<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserDatasType;

use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
                $hash = $encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($hash);

                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute('security_login');
            }

            return $this->render('security/home.html.twig', [
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/connexion", name="security_login")
     */

    public function login()
    {
        return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */

    public function logout()
    { }

    /**
     * @Route("/mesdonnees", name="security_datas")
     */
    public function changeUserDatas(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserDatasType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getNewPassword());
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('security/user_datas.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
