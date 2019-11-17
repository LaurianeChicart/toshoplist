<?php

namespace App\Helpers;

use App\Entity\Item;
use App\Entity\User;
use App\Entity\Planning;
use App\Entity\ListItems;
use App\Entity\ListDepartment;
use Doctrine\Common\Persistence\ObjectManager;

class ListItemsHelper
{
    private $manager;
    private $list;
    private $planning;
    private $departments;
    private $user;
    private $days;


    public function __construct(ObjectManager $manager, ListItems $list, Planning $planning, array $departments, User $user, array $days)
    {
        $this->manager = $manager;
        $this->list = $list;
        $this->planning = $planning;
        $this->departments = $departments;
        $this->user = $user;
        $this->days = $days;
    }

    /**
     * pour chaque jour du planning, on enregistre les repas planifiés
     * puis on remplit la liste avec les items (memo + recette) par department
     *
     */
    public function registerPlanningList()
    {
        foreach ($this->days as $day) {
            foreach ($day->getPlannedMeal() as $plannedMeal) {
                $plannedMeal->setDescription(htmlspecialchars($plannedMeal->getDescription()));
                $this->manager->persist($plannedMeal);
            }
        }
        $listDepartmentList = $this->getListDepartments();
        $this->pushMemoItemsInList();
        $listIngredientAndQuantity = $this->getAllQuantifiedRecipeIngredients();
        $ingredientsGroupedByDepartment = $this->sortIngredientsByDepartments($listIngredientAndQuantity);
        $this->fillListDepartments($ingredientsGroupedByDepartment, $listIngredientAndQuantity, $listDepartmentList);
        $this->manager->flush();
    }

    /**
     * On obtient la liste des departments pour la liste
     */
    private function getListDepartments(): array
    {
        $listDepartmentList = [];
        //si on modifie le planning, il faut réinitialiser complètement la liste
        if ($this->planning->getListItems()) {
            foreach ($this->list->getListDepartments() as $listDepartment) {
                $listDepartmentList[] = $listDepartment;
                foreach ($listDepartment->getItems() as $items) {
                    $this->manager->remove($items);
                }
            }
        }
        //si on crée le planning, on crée les departments qui vont accueillir les items
        else {
            $this->manager->persist($this->list);
            foreach ($this->departments as $department) {
                $listDepartment = new ListDepartment();
                $listDepartment->setDepartment($department);
                $listDepartment->setListItems($this->list);
                $this->manager->persist($listDepartment);
                $listDepartmentList[] = $listDepartment;
            }
        }
        return $listDepartmentList;
    }

    /**
     * On inclut les éléments du mémo dans le rayon "divers" de la liste
     */
    private function pushMemoItemsInList()
    {
        foreach ($this->user->getMemos() as $memoItem) {
            $item = new Item();
            $diversDepartment = $this->listDepartmentList[count($this->listDepartmentList) - 1];
            $item->setListDepartment($diversDepartment);
            $item->setName($memoItem->getItem());
            $this->manager->persist($item);
        }
    }

    /**
     * on récupère tous les RecipeIngredients appelés sur ce planning, et la quantité qu'on doit leur appliquer
     *
     */
    private function getAllQuantifiedRecipeIngredients(): array
    {
        $listIngredientAndQuantity = [];

        foreach ($this->days as $day) {
            foreach ($day->getPlannedMeal() as $plannedMeal) {
                $portionWanted = $plannedMeal->getPortion();
                $recipe = $plannedMeal->getRecipe();
                $originalPortion = $recipe->getPortionsNb();
                $ingredientsRecipe = $recipe->getRecipeIngredients();
                foreach ($ingredientsRecipe as $ingredientRecipe) {
                    $quantityNeeded = $ingredientRecipe->getQuantity() * $portionWanted / $originalPortion;
                    $ingredientAndQuantity = [$ingredientRecipe,  round($quantityNeeded, 2)];
                    $listIngredientAndQuantity[] = $ingredientAndQuantity;
                }
            }
        }
        return $listIngredientAndQuantity;
    }

    /**
     * on rassemble tous les items de même rayon dans des tableaux placés dans un tableau
     */
    private function sortIngredientsByDepartments(array $listIngredientAndQuantity): array
    {
        $ingredientsGroupedByDepartment = [];
        //pour rappel $departments est un tableau qui rassemble tous les objets Department de la bdd
        // $listDepartmentList a été crée à partir de $departments, les departments correspondants sont donc dans le même ordre dans les 2 tableaux
        foreach ($this->departments as $departmentIngredient) {
            $ingredientsOfOneDepartment = [];
            foreach ($listIngredientAndQuantity as $ingredient) {
                if ($ingredient[0]->getIngredient()->getDepartment() == $departmentIngredient) {
                    $ingredientsOfOneDepartment[] = $ingredient;
                }
            }
            $ingredientsGroupedByDepartment[] = $ingredientsOfOneDepartment;
        }
        return $ingredientsGroupedByDepartment;
    }

    /**
     * Les éléments de la liste sont créés et répartis selon les rayons
     *
     */
    private function fillListDepartments(array $ingredientsGroupedByDepartment, array $listIngredientAndQuantity, array $listDepartmentList)
    {
        for ($i = 0; $i < count($this->departments); $i++) {
            $idIngredientsNeeded = $this->getAllIngredientsNeeded($i, $ingredientsGroupedByDepartment);
            $ingredientsGroupedById = $this->sortIngredientsById($idIngredientsNeeded, $listIngredientAndQuantity);
            $this->createItems($ingredientsGroupedById, $listDepartmentList, $i);
        }
    }
    /**
     *on récupère la liste des id uniques des ingrédients requis dans le groupe d'ingrédient du rayon
     */
    private function getAllIngredientsNeeded(int $i, array $ingredientsGroupedByDepartment): array
    {
        $allIdIngredientsNeeded = [];
        foreach ($ingredientsGroupedByDepartment[$i] as $ingredientRecipe) {
            $idIngredient = $ingredientRecipe[0]->getIngredient()->getId();
            $allIdIngredientsNeeded[] = $idIngredient;
        }
        $idIngredientsNeeded = array_unique($allIdIngredientsNeeded);
        return $idIngredientsNeeded;
    }

    /**
     * on rassemble tous les RecipeIngredient impliquant le même idIngredient dans un tableau
     *
     */
    private function sortIngredientsById(array $idIngredientsNeeded, array $listIngredientAndQuantity): array
    {
        $ingredientsGroupedById = [];
        foreach ($idIngredientsNeeded as $idIngredient) {
            $arrayRecipeIngredientId = [];
            foreach ($listIngredientAndQuantity as $ingredient) {
                if ($ingredient[0]->getIngredient()->getId() == $idIngredient) {
                    $arrayRecipeIngredientId[] = $ingredient;
                }
            }
            $ingredientsGroupedById[] = $arrayRecipeIngredientId;
        }
        return $ingredientsGroupedById;
    }

    /**
     * chacun de ces tableaux crées correspond à un item de la liste, 
     * on va récupérer les infos des RecipeIngredients qu'il contient pour définir ses différents attributs
     */
    private function createItems(array $ingredientsGroupedById, array $listDepartmentList, int $i)
    {
        foreach ($ingredientsGroupedById as $idIngredient) {
            $newItem = new Item();
            $newItem->setName($idIngredient[0][0]->getIngredient()->getName());
            $newItem->setListDepartment($listDepartmentList[$i]);

            // on trie les différentes unités de mesures pour un même ingrédient
            $allMeasureUnits = [];
            foreach ($idIngredient as $recipeIngredient) {
                $measure = $recipeIngredient[0]->getMeasure();
                $allMeasureUnits[] = $measure;
            }
            $measureUnits = array_unique($allMeasureUnits);

            $quantitiesGroupedByMeasure = [];
            foreach ($measureUnits as $measureUnit) {
                $quantitiesByMeasure = [];
                foreach ($idIngredient as $recipeIngredient) {
                    if ($recipeIngredient[0]->getMeasure() == $measureUnit) {
                        $quantitiesByMeasure[] = $recipeIngredient[1];
                    }
                }
                $quantitiesGroupedByMeasure[]  = $quantitiesByMeasure;
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
                $initialQuantities[] = [$quantitiesGroupedByMeasure[$j], $measureUnit];
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
                    $initialQuantitiesStringified[] = $quantityToAdd;
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
                $measureSumsStringified[] = $quantityToAdd;
            }
            $allSumsStringified = implode(" + ",  $measureSumsStringified);
            $newItem->setQuantities($allSumsStringified);
            $this->manager->persist($newItem);
        }
    }
}
