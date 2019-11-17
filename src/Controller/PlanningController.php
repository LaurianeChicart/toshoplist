<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Form\DayType;
use App\Helpers\PlanningHelper;
use App\Form\ListDepartmentType;
use App\Helpers\ListHelper;
use App\Helpers\ListItemsHelper;
use App\Repository\DayRepository;
use App\Repository\ItemRepository;
use App\Repository\PlanningRepository;
use App\Repository\ListItemsRepository;
use App\Repository\DepartmentRepository;
use App\Repository\ListDepartmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlanningController extends AbstractController
{
    /**
     * @Route("/moncompte/mes-plannings", name="plannings")
     */
    public function showPlannings()
    {
        $user = $this->getUser();

        return $this->render('main/shoplist/my-plannings.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/moncompte/creer-un-planning", name="create-planning")
     */
    public function createPlanning(ObjectManager $manager, Request $request, PlanningRepository $planningRepo, DayRepository $dayRepo)
    {
        $user = $this->getUser();
        if ($request->request->count() > 0) {
            $startDate = date_create(htmlspecialchars($request->request->get('startDate')));
            $stopDate = date_create(htmlspecialchars($request->request->get('stopDate')));
            $diff = (date_diff($startDate, $stopDate))->days;
            if ($diff > 0) {
                $planning = PlanningHelper::registerPlanningAndDays($startDate, $stopDate, $diff, $manager, $user);
                return $this->redirectToRoute('fill-planning', ['id' => $planning->getId()]);
            }
        }
        return $this->render('main/shoplist/create-planning.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/moncompte/remplir-mon-planning/{id}", name="fill-planning")
     */
    public function fillPlanning($id, PlanningRepository $planningRepo, ObjectManager $manager, Request $request, DayRepository $dayRepo,  DepartmentRepository $departmentRepo)
    {
        if (!$planningRepo->findOneBy(['id' => $id]) || $planningRepo->findOneBy(['id' => $id])->getUser() != $this->getUser()) {
            throw $this->createNotFoundException("Le planning demandé n'existe pas.");
        } else {
            $planning = $planningRepo->findOneBy(['id' => $id]);
        }
        $user = $this->getUser();
        $days = $dayRepo->findBy(['planning' => $planning]);
        $departments = $departmentRepo->findAll();

        $list = PlanningHelper::getPlanningList($planning);
        $labelSubmit = PlanningHelper::getLabelSubmitForPlanning($planning);
        $data = ['collection' => $days];
        $dates = PlanningHelper::getPlanningDatesInJSON($data, $days);


        $form = $this
            ->createFormBuilder($data)
            ->add('collection', CollectionType::class, [
                'entry_type'   => DayType::class,
                'entry_options' => ['user' => $user],
                'label'        => false,
                'allow_add'    => false,
                'allow_delete' => false,
                'prototype'    => true,
                'required'     => false,
                'attr'         => [
                    'class' => 'day-collection',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary float-right text-white shadow mb-4 mt-3'],
                'label' => $labelSubmit
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $listItemsHelper = new ListItemsHelper($manager, $list, $planning, $departments, $user, $days);
            $listItemsHelper->registerPlanningList();

            return $this->redirectToRoute('modify-shoplist', ['id' => $list->getId()]);
        }

        return $this->render('main/shoplist/fill-planning.twig', [
            'planning' => $planning,
            'user' => $user,
            'form' => $form->createView(),
            'dates' => $dates,
            'editMode' => $list->getId() !== null,
        ]);
    }

    // /**
    //  * @Route("/moncompte/mon-planning-pdf/{id}", name="planning-pdf-1")
    //  */
    // public function showPlanningPDF($id, PlanningRepository $planningRepo)
    // {
    //     if (!$planningRepo->findOneBy(['id' => $id]) || $planningRepo->findOneBy(['id' => $id])->getUser() != $this->getUser()) {
    //         throw $this->createNotFoundException("Le planning demandé n'existe pas.");
    //     } else {
    //         $planning = $planningRepo->findOneBy(['id' => $id]);
    //     }
    //     // Configuration Dompdf 
    //     $pdfOptions = new Options();
    //     $pdfOptions->set('defaultFont', 'Arial');
    //     $dompdf = new Dompdf($pdfOptions);

    //     // on génère le fichier twig
    //     $html = $this->renderView('main/shoplist/planning-pdf.twig', [
    //         'planning' => $planning,
    //     ]);
    //     // on le charge dans Dompdf
    //     $dompdf->loadHtml($html);

    //     $dompdf->setPaper('A4', 'landscape');

    //     // génération du pdf
    //     $dompdf->render();

    //     // et affichage
    //     $dompdf->stream("toshoplist.pdf", [
    //         "Attachment" => false,
    //     ]);
    // }
    /**
     * @Route("/moncompte/mon-planning/{id}", name="planning")
     */
    public function showPlanning($id, PlanningRepository $planningRepo)
    {
        if (!$planningRepo->findOneBy(['id' => $id]) || $planningRepo->findOneBy(['id' => $id])->getUser() != $this->getUser()) {
            throw $this->createNotFoundException("Le planning demandé n'existe pas.");
        } else {
            $planning = $planningRepo->findOneBy(['id' => $id]);
        }
        return $this->render('main/shoplist/one-planning.twig', [
            'planning' => $planning,
        ]);
    }

    /**
     * @Route("/moncompte/modifier-ma-toshoplist/{id}", name="modify-shoplist")
     */
    public function modifyShopList($id, ListItemsRepository $listRepo, Request $request, ListDepartmentRepository $listDepartmentRepo, ObjectManager $manager)
    {
        if (!$listRepo->findOneBy(['id' => $id]) || $listRepo->findOneBy(['id' => $id])->getPlanning()->getUser() != $this->getUser()) {
            throw $this->createNotFoundException("La liste demandée n'existe pas.");
        } else {
            $list = $listRepo->findOneBy(['id' => $id]);
        }
        $departments = $listDepartmentRepo->findBy(['list_items' => $list]);
        $departmentNames = ListHelper::getPlanningDepartmentsInJSON($departments);

        $data = ['listDepartment' => $departments];

        $form = $this
            ->createFormBuilder($data)
            ->add('listDepartment', CollectionType::class, [
                'entry_type'   => ListDepartmentType::class,
                'entry_options' => [
                    'attr' => ['class' => 'col-md'],
                ],
                'label'        => false,
                'allow_add'    => false,
                'allow_delete' => false,
                'prototype'    => true,
                'required'     => false,
                'attr'         => [
                    'class' => 'department-collection',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary float-right text-white shadow mb-4 mt-3'],
                'label' => 'Valider la liste'
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            ListHelper::registerModificationsOnItems($departments, $manager);
            return $this->redirectToRoute('shoplist', ['id' => $list->getId()]);
        }

        return $this->render('main/shoplist/modify-list.twig', [
            'list' => $list,
            'form' => $form->createView(),
            'departmentNames' => $departmentNames
        ]);
    }

    /**
     * @Route("/moncompte/ma-toshoplist/{id}", name="shoplist")
     */
    public function showShopList($id, ListItemsRepository $listRepo, ListDepartmentRepository $listDepartmentRepo, ItemRepository $itemRepo)
    {
        if (!$listRepo->findOneBy(['id' => $id]) || $listRepo->findOneBy(['id' => $id])->getPlanning()->getUser() != $this->getUser()) {
            throw $this->createNotFoundException("La liste demandée n'existe pas.");
        } else {
            $list = $listRepo->findOneBy(['id' => $id]);
        }
        $departments = $listDepartmentRepo->findBy(['list_items' => $list], ['position' => 'ASC']);
        $orderedList = ListHelper::getOrderedList($departments, $itemRepo);


        return $this->render('main/shoplist/show-list.twig', [
            'listItems' => $list,
            'list' => $orderedList
        ]);
    }

    /**
     * @Route("/moncompte/check-element-liste/{id}", name="check_memo_list")
     */
    public function deleteMemoItem($id, ItemRepository $itemRepo, ObjectManager $manager)
    {
        if (!$itemRepo->findOneBy(['id' => $id]) || $itemRepo->findOneBy(['id' => $id])->getListDepartment()->getListItems()->getPlanning()->getUser() != $this->getUser()) {
            throw $this->createNotFoundException("L'élement demandé n'existe pas.");
        } else {
            $item = $itemRepo->findOneBy(['id' => $id]);
        }
        ListHelper::toggleCheckItem($item, $manager);
        return $this->json(['message' => 'Check validé'], 200);
    }

    /**
     * @Route("/moncompte/supprimer-toshoplist/{id}", name="remove-shoplist")
     */
    public function removeShopList($id, PlanningRepository $planningRepo, ObjectManager $manager)
    {
        if (!$planningRepo->findOneBy(['id' => $id]) || $planningRepo->findOneBy(['id' => $id])->getUser() != $this->getUser()) {
            throw $this->createNotFoundException("Le planning demandé n'existe pas.");
        } else {
            $planning = $planningRepo->findOneBy(['id' => $id]);
        }
        ListHelper::removeMealDaysPlanning($planning, $manager);

        return $this->redirectToRoute('plannings');
    }
}
