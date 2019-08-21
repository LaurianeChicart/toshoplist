<?php

namespace App\Controller;

use App\Entity\User;

use App\Form\UserDatasType;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/", name="security_registration")
     */

    public function registration(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        } else {

            $user = new User();

            $form = $this->createForm(RegistrationType::class, $user);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $hash = $encoder->encodePassword($user, htmlspecialchars($user->getPassword()));
                $user->setPassword($hash);
                $user->setEmail(htmlspecialchars($user->getEmail()));
                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute('security_login');
            }

            return $this->render('security/home.twig', [
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/connexion", name="security_login")
     */

    public function login(AuthenticationUtils $authenticationUtils)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        } else {
            $error = $authenticationUtils->getLastAuthenticationError();
            $lastUsername = $authenticationUtils->getLastUsername();
            return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername,
                'error' => $error
            ]);
        }
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */

    public function logout()
    { }

    /**
     * @Route("/moncompte/mesdonnees", name="security_datas")
     */
    public function changeUserDatas(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserDatasType::class, $user);
        $errorMessage = null;
        $originalEmail = $user->getEmail();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($encoder->isPasswordValid($user, $user->getCurrentPassword())) {
                if ($user->getNewPassword() != null) {
                    $hash = $encoder->encodePassword($user, htmlspecialchars($user->getNewPassword()));
                    $user->setPassword($hash);
                }
                if ($user->getEmail() != $originalEmail) {
                    $user->setEmail(htmlspecialchars($user->getEmail()));
                }
                $manager->persist($user);
                $manager->flush();

                return $this->json([
                    'message' => "Vos données de connection ont été mises à jour !",
                ], 200);

                return $this->redirectToRoute('dashboard');
            } else {
                return $this->json([
                    'message' => "Mot de passe incorrect",
                ], 403);
            }
        } else {
            $errorMessage = null;
        }

        return $this->render('security/user_datas.twig', [
            'form' => $form->createView(),
            'errorMessage' => $errorMessage
        ]);
    }

    /**
     * @Route("/mentions-legales", name="legal-mentions")
     */

    public function showLegalMentions()
    {
        return $this->render('security/legal-mentions.html.twig');
    }

    /**
     * @Route("/contact", name="contact")
     */

    public function sendMail(Request $request, \Swift_Mailer $mailer)
    {
        if ($this->getUser()) {
            $emailAddress = $this->getUser()->getEmail();
        } else {
            $emailAddress = null;
        }

        $form = $this
            ->createFormBuilder()
            ->setAction($this->generateUrl('contact'))
            ->add('email', EmailType::class, [
                'label'        => "Mon adresse e-mail",
                'required'     => true,
                'attr'         => [
                    'class' => 'form-control',
                    'value' => $emailAddress,
                ],
                'trim' => true,
            ])
            ->add('subject', TextType::class, [
                'label'        => "Objet",
                'required'     => true,
                'attr'         => [
                    'class' => 'form-control',
                ],
                'trim' => true,
            ])
            ->add('message', TextareaType::class, [
                'label'        => "Message",
                'required'     => true,
                'attr'         => [
                    'class' => 'form-control',
                    'rows' => '6'
                ],
                'trim' => true,
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary float-right text-white shadow mb-4 mt-3'],
                'label' => 'Envoyer'
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = trim(htmlspecialchars(($request->request->get('form'))["email"]));
            $subject = trim(htmlspecialchars(($request->request->get('form'))["subject"]));
            $message = trim(htmlspecialchars(($request->request->get('form'))["message"]));

            if (!empty($email) && !empty($subject) && !empty($message)) {
                $mail = (new \Swift_Message('ToShopList : ' . $subject))
                    ->setFrom($email)
                    ->setTo('contact@mesmodulesdevalidationoc.fr')
                    ->setBody(
                        $this->renderView(
                            'emails/contact.html.twig',
                            [
                                'email' => $email,
                                'subject' => $subject,
                                'message' => $message
                            ]
                        ),
                        'text/html'
                    );

                $mailer->send($mail);

                return $this->json([
                    'message' => "E-mail envoyé !",
                ], 200);
            } else {
                return $this->json([
                    'message' => "Envoi impossible",
                ], 403);
            }
        }

        return $this->render('security/contact.twig', [
            'authentified' => $this->getUser() !== null,
            'form' => $form->createView()
        ]);
    }
}
