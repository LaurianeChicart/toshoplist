<?php

namespace App\Controller;

use App\Entity\Day;
use App\Entity\Item;
use App\Form\DayType;
use App\Entity\Planning;
use App\Entity\ListItems;
use App\Entity\ListDepartment;
use App\Form\ListDepartmentType;
use App\Repository\DayRepository;
use App\Repository\PlanningRepository;
use App\Repository\DepartmentRepository;
use App\Repository\ListDepartmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ItemRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\ListItemsRepository;

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
                $intervalDate = [date_format($startDate, 'Y-m-d')];
                for ($i = 0; $i < $diff; $i++) {
                    $newDate = date_add($startDate, date_interval_create_from_date_string('1 days'));
                    array_push($intervalDate, date_format($newDate, 'Y-m-d'));
                }
                $planning = new Planning;
                $planning->setUser($user);
                $planning->setCreatedAt(new \DateTime());

                $manager->persist($planning);
                foreach ($intervalDate as $date) {
                    $day = new Day();
                    $day->setDate(\DateTime::createFromFormat('Y-m-d', $date));
                    $day->setPlanning($planning);
                    $manager->persist($day);
                }
                $manager->flush();
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

        if (!$planning->getListItems()) {
            $list = new ListItems();
            $list->setPlanning($planning);
            $labelSubmit = "Créer ma liste";
        } else {
            $list = $planning->getListItems();
            $labelSubmit = "Enregistrer les modifications";
        }

        $data = ['collection' => $days];

        //tableau des timestamps de la période sélectionnée à transmettre à javascript pour assurer l'affichage des dates (ex: mercredi 31 juillet)
        $allDates = [];
        foreach ($days as $day) {
            setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
            $dayDate = $day->getDate();
            $date = $dayDate->getTimestamp();
            array_push($allDates, $date);
        }
        $dates = json_encode($allDates);

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
            foreach ($days as $day) {
                foreach ($day->getPlannedMeal() as $plannedMeal) {
                    $plannedMeal->setDescription(htmlspecialchars($plannedMeal->getDescription()));
                    $manager->persist($plannedMeal);
                }
            }
            $listDepartmentList = [];
            //si on modifie le planning, il faut réinitialiser complètement la liste
            if ($planning->getListItems()) {
                foreach ($list->getListDepartments() as $listDepartment) {
                    array_push($listDepartmentList, $listDepartment);
                    foreach ($listDepartment->getItems() as $items) {
                        $manager->remove($items);
                    }
                }
            } else {
                $manager->persist($list);
                foreach ($departments as $department) {
                    $listDepartment = new ListDepartment();
                    $listDepartment->setDepartment($department);
                    $listDepartment->setListItems($list);
                    $manager->persist($listDepartment);
                    array_push($listDepartmentList, $listDepartment);
                }
            }

            foreach ($user->getMemos() as $memoItem) {
                $item = new Item();
                $diversDepartment = $listDepartmentList[count($listDepartmentList) - 1];
                $item->setListDepartment($diversDepartment);
                $item->setName($memoItem->getItem());
                $manager->persist($item);
            }

            //Etape 1 : on récupère tous les RecipeIngredients appelés sur ce planning, et la quantité qu'on doit leur appliquer
            $listIngredientAndQuantity = [];

            foreach ($days as $day) {
                foreach ($day->getPlannedMeal() as $plannedMeal) {
                    $portionWanted = $plannedMeal->getPortion();
                    $recipe = $plannedMeal->getRecipe();
                    $originalPortion = $recipe->getPortionsNb();
                    $ingredientsRecipe = $recipe->getRecipeIngredients();
                    foreach ($ingredientsRecipe as $ingredientRecipe) {
                        $quantityNeeded = $ingredientRecipe->getQuantity() * $portionWanted / $originalPortion;
                        $ingredientAndQuantity = [$ingredientRecipe,  round($quantityNeeded, 2)];
                        array_push($listIngredientAndQuantity, $ingredientAndQuantity);
                    }
                }
            }

            //Etape 2 : on rassemble tous les items de même rayon dans des tableaux placés dans un tableau
            $ingredientsGroupedByDepartment = [];
            //pour rappel $departments est un tableau qui rassemble tous les objets Department de la bdd
            // $listDepartmentList a été crée à partir de $departments, les departments correspondants sont donc dans le même ordre dans les 2 tableaux
            foreach ($departments as $departmentIngredient) {
                $ingredientsOfOneDepartment = [];
                foreach ($listIngredientAndQuantity as $ingredient) {
                    if ($ingredient[0]->getIngredient()->getDepartment() == $departmentIngredient) {
                        array_push($ingredientsOfOneDepartment, $ingredient);
                    }
                }
                array_push($ingredientsGroupedByDepartment, $ingredientsOfOneDepartment);
            }

            for ($i = 0; $i < count($departments); $i++) {
                //Etape 3 : on récupère la liste des id uniques des ingrédients requis dans le groupe d'ingrédient du rayon
                $allIdIngredientsNeeded = [];
                foreach ($ingredientsGroupedByDepartment[$i] as $ingredientRecipe) {
                    $idIngredient = $ingredientRecipe[0]->getIngredient()->getId();
                    array_push($allIdIngredientsNeeded, $idIngredient);
                }
                $idIngredientsNeeded = array_unique($allIdIngredientsNeeded);
                //Etape 4 :  on rassemble tous les RecipeIngredient impliquant le même idIngredient dans un tableau
                $ingredientsGroupedById = [];
                foreach ($idIngredientsNeeded as $idIngredient) {
                    $arrayRecipeIngredientId = [];
                    foreach ($listIngredientAndQuantity as $ingredient) {
                        if ($ingredient[0]->getIngredient()->getId() == $idIngredient) {
                            array_push($arrayRecipeIngredientId, $ingredient);
                        }
                    }
                    array_push($ingredientsGroupedById, $arrayRecipeIngredientId);
                }
                //Etape 5 : chacun de ces tableaux crées correspond à un item de la liste, on va récupérer les infos des RecipeIngredients qu'il contient pour définir ses différents attributs
                foreach ($ingredientsGroupedById as $idIngredient) {
                    $newItem = new Item();
                    $newItem->setName(lcfirst($idIngredient[0][0]->getIngredient()->getName()));
                    $newItem->setListDepartment($listDepartmentList[$i]);

                    // on trie les différentes unités de mesures pour un même ingrédient
                    $allMeasureUnits = [];
                    foreach ($idIngredient as $recipeIngredient) {
                        $measure = $recipeIngredient[0]->getMeasure();
                        array_push($allMeasureUnits, $measure);
                    }
                    $measureUnits = array_unique($allMeasureUnits);
                    $quantitiesGroupedByMeasure = [];
                    foreach ($measureUnits as $measureUnit) {
                        $quantitiesByMeasure = [];
                        foreach ($idIngredient as $recipeIngredient) {
                            if ($recipeIngredient[0]->getMeasure() == $measureUnit) {
                                array_push($quantitiesByMeasure, $recipeIngredient[1]);
                            }
                        }
                        array_push($quantitiesGroupedByMeasure, $quantitiesByMeasure);
                    }

                    $initialQuantities = [];
                    // à chaque unité de mesure de l'ingrédient, on associe le tableau des quanitités concernées par cette unité
                    for ($j = 0; $j < count($measureUnits); $j++) {
                        if ($measureUnits[$j] == "unité") {
                            $measureUnit = null;
                        } else if ($measureUnits[$j] == "c à C" || $measureUnits[$j] == "c à S" || $measureUnits[$j] == "tranche" || $measureUnits[$j] == "filet") {
                            $measureUnit = " " .  $measureUnits[$j];
                        } else {
                            $measureUnit = $measureUnits[$j];
                        }
                        array_push($initialQuantities, [$quantitiesGroupedByMeasure[$j], $measureUnit]);
                    }

                    //pour $newItem->setInitialQuantities(), on crée une chaîne sur le modèle "200g + 100g + 2 tranches" 
                    $initialQuantitiesStringified = [];
                    foreach ($initialQuantities as $quantityIngredient) {
                        foreach ($quantityIngredient[0] as $oneQuantity) {
                            if (is_null($quantityIngredient[1])) {
                                $quantityToAdd = $oneQuantity;
                            } else if ($oneQuantity >= 2 && $quantityIngredient[1] == " tranche") {
                                $quantityToAdd = $oneQuantity . " tranches";
                            } else if ($oneQuantity >= 2 && $quantityIngredient[1] == " filet") {
                                $quantityToAdd = $oneQuantity . " filets";
                            } else {
                                $quantityToAdd = $oneQuantity . $quantityIngredient[1];
                            }
                            array_push($initialQuantitiesStringified, $quantityToAdd);
                        }
                    }
                    $stringifyInitialQuantities =  implode(" + ",  $initialQuantitiesStringified);
                    $newItem->setInitialQuantities($stringifyInitialQuantities);

                    //pour $newItem->setInitialQuantities(), on crée une chaîne sur le modèle "300g + 2 tranches" 
                    $measureSumsStringified = [];
                    foreach ($initialQuantities as $quantityIngredient) {
                        $totalQuantity = 0;
                        foreach ($quantityIngredient[0] as $oneQuantity) {
                            $totalQuantity = $totalQuantity + $oneQuantity;
                        }
                        if (is_null($quantityIngredient[1])) {
                            $quantityToAdd = $totalQuantity;
                        } else if ($totalQuantity >= 2 && $quantityIngredient[1] == " tranche") {
                            $quantityToAdd = $totalQuantity . " tranches";
                        } else if ($oneQuantity >= 2 && $quantityIngredient[1] == " filet") {
                            $quantityToAdd = $totalQuantity . " filets";
                        } else {
                            $quantityToAdd = $totalQuantity . $quantityIngredient[1];
                        }
                        array_push($measureSumsStringified, $quantityToAdd);
                    }
                    $allSumsStringified = implode(" + ",  $measureSumsStringified);
                    $newItem->setQuantities($allSumsStringified);
                    $manager->persist($newItem);
                }
            }

            $manager->flush();
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
        $departmentNames = [];
        foreach ($departments as $department) {
            $name = $department->getDepartment()->getName();
            array_push($departmentNames, $name);
        }
        $departmentNames = json_encode($departmentNames);

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
            foreach ($departments as $department) {
                foreach ($department->getItems() as $item) {
                    $item->setName(htmlspecialchars($item->getName()));
                    $item->setQuantities(htmlspecialchars($item->getQuantities()));
                    $item->setInitialQuantities(htmlspecialchars($item->getInitialQuantities()));
                    $manager->persist($item);
                }
            }
            $manager->flush();
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
        $orderedList = [];
        foreach ($departments as $department) {
            $items = $itemRepo->findBy(['list_department' => $department], ['position' => 'ASC']);
            $orderedDepartment = [$department->getDepartment()->getName(), $items];
            array_push($orderedList, $orderedDepartment);
        }

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
        $check = $item->getChecked();
        if ($check == false) {
            $item->setChecked(true);
        } elseif ($check == true) {
            $item->setChecked(false);
        }
        $manager->persist($item);
        $manager->flush();
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
        foreach ($planning->getDay() as $day) {
            foreach ($day->getPlannedMeal() as $meal) {
                $manager->remove($meal);
            }
            $manager->remove($day);
        }

        $manager->remove($planning);
        $manager->flush();

        return $this->redirectToRoute('plannings');
    }
}
