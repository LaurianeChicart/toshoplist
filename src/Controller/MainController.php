<?php

namespace App\Controller;

use App\Entity\Memo;

use App\Entity\User;
use App\Form\MemoType;
use App\Repository\MemoRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    /**
     * @Route("/moncompte", name="dashboard")
     */
    public function showDashboard(UserRepository $userRepo, MemoRepository $memoRepo)
    {
        $user = new User;
        $user = $userRepo->findOneBy(['id' => $this->getUser()->getId()]);

        $memos = $memoRepo->findBy(['id_user' => $this->getUser()->getId()]);

        return $this->render('main/dashboard.html.twig', [
            'memos' => $memos
        ]);
    }

    /**
     * @Route("/moncompte/memo", name="memo")
     */
    public function showMemo(MemoRepository $memoRepo, Request $request, ObjectManager $manager)
    {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = $this->getUser();
        $id = $user->getId();
        $userMemo = $repo->find($id);

        $memo = new Memo();
        $form = $this->createForm(MemoType::class, $memo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $memo->setIdUser($userMemo);

            $manager->persist($memo);
            $manager->flush();
            dump($request);
            return $this->json([
                'message' => "L'élément a été ajouté",
                'item' => $memo->getItem(),
                'id' => $memo->getId()
            ], 200);
        } else {
            $memoList = $memoRepo->findBy(['id_user' => $id]);

            return $this->render('main/memo.html.twig', [
                'memoList' => $memoList,
                'form' => $form->createView(),

            ]);
        }
    }

    /**
     * @Route("/moncompte/reset", name="reset_memo")
     */
    public function resetMemo(MemoRepository $memoRepo, ObjectManager $manager)
    {
        $user = $this->getUser();
        $memoList = $memoRepo->findBy(['id_user' => $user->getId()]);

        foreach ($memoList as $memo) {
            $manager->remove($memo);
        }
        $manager->flush();

        return $this->json(['message' => 'L\'élément a été supprimé'], 200);
    }

    /**
     * @Route("/moncompte/supprimer-element-memo/{id}", name="delete_memo_item")
     */
    public function deleteMemoItem(Memo $memo, ObjectManager $manager)
    {
        $manager->remove($memo);
        $manager->flush();

        return $this->json(['message' => 'L\'élément a été supprimé'], 200);
    }
}
