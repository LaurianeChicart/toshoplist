<?php

namespace App\Helpers;

use App\Entity\User;
use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\RecipeIngredient;
use App\Repository\IngredientRepository;
use App\Repository\RecipeIngredientRepository;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class RecipeHelper
{
    private $manager;
    private $ingredientRepo;
    private $recipeIngredientRepo;

    public function __construct(ObjectManager $manager, IngredientRepository $ingredientRepo, RecipeIngredientRepository $recipeIngredientRepo)
    {
        $this->manager = $manager;
        $this->ingredientRepo = $ingredientRepo;
        $this->recipeIngredientRepo = $recipeIngredientRepo;
    }

    public function saveIngredientsCollectionAndRecipe($ingredientsRecipe, Recipe $recipe, User $user): void
    {
        if (!$recipe->getId()) {
            $recipe->setUser($user);
        }
        $recipe->setName(htmlspecialchars($recipe->getName()));
        $recipe->setInstructions(htmlspecialchars($recipe->getInstructions()));
        $recipe->setLink(htmlspecialchars($recipe->getLink()));
        foreach ($ingredientsRecipe as $ingredientRecipe) {
            $ingredientId = $ingredientRecipe->getIngredient()->getId();

            //enregistrement de chaque ingrédient
            $ingredient = $this->registerIngredient($ingredientId, $ingredientRecipe, $user);
            //enregistrement de chaque recipeIngredient
            $this->registerIngredientRecipe($ingredientRecipe, $ingredient, $recipe);

            $this->manager->persist($ingredientRecipe);
        }
        $this->manager->persist($recipe);
    }

    private function registerIngredient(int $ingredientId, RecipeIngredient $ingredientRecipe, User $user): Ingredient
    {
        // si l'ingrédient existe on le récupère
        if ($ingredientId != null) {
            $ingredient = $this->ingredientRepo->findOneBy(['id' => $ingredientId]);
            //si c'est un ingrédient de l'utilisateur, accepter les mises à jour de celui-ci
            if ($ingredient->getUser() != null) {
                $ingredient->setName(htmlspecialchars($ingredientRecipe->getIngredient()->getName()));
                $ingredient->setDepartment($ingredientRecipe->getIngredient()->getDepartment());
                $this->manager->persist($ingredient);
            }
        }
        // si l'ingrédient associé n'existe pas dans la liste de la bdd, le créer
        else {
            $ingredient = $ingredientRecipe->getIngredient();
            $ingredient->setUser($user);
            $ingredient->setName(htmlspecialchars($ingredientRecipe->getIngredient()->getName()));
            $ingredient->setDepartment($ingredientRecipe->getIngredient()->getDepartment());
            $this->manager->persist($ingredient);
        }
        return $ingredient;
    }
    private function registerIngredientRecipe(RecipeIngredient $ingredientRecipe, Ingredient $ingredient, Recipe $recipe): void
    {
        //si le recipeIngredient n'existe pas, le créer
        if (!$ingredientRecipe->getId()) {
            $ingredientRecipe->setRecipe($recipe);
            $ingredientRecipe->setIngredient($ingredient);
        }
        // sinon le récupérer et le mettre à jour
        else {
            $ingredientRecipe = $this->recipeIngredientRepo->findOneBy(['id' => $ingredientRecipe->getId()]);
            $ingredientRecipe->setIngredient($ingredient);
        }
    }

    //supprime les ingredients du user qui ne sont utilisés dans aucune de ses recettes
    public function removeUnusefulIngredients(User $user): void
    {
        $ingredientsUser = $this->ingredientRepo->findBy(["user" => $user]);
        foreach ($ingredientsUser as $ingredientUser) {
            if ($this->recipeIngredientRepo->findBy(["ingredient" => $ingredientUser]) == null) {
                $this->manager->remove($ingredientUser);
            }
        }
    }

    public function getNewIngredientsList(User $user)
    {
        $listIngredients = $this->ingredientRepo->findByUserIngredients($user);

        foreach ($listIngredients as $ingredientFromList) {
            if ($ingredientFromList->getUser() == null) {
                $ingredientFromList->setUserIsNull(true);
            } else {
                $ingredientFromList->setUserIsNull(false);
            }
        }
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($listIngredients, 'json', ['attributes' => ['id', 'name', 'department' => ['id'], 'userIsNull']]);
    }

    //la recette que l'on souhaite supprimer est-elle utilisée dans les plannings ??
    public function checkThisRecipeInPlanning(Recipe $recipe, array $plannings): bool
    {
        $counter = 0;
        foreach ($plannings as $planning) {
            $days = $planning->getDay();
            foreach ($days as $day) {
                $plannedMeals = $day->getPlannedMeal();
                foreach ($plannedMeals as $plannedMeal) {
                    if ($plannedMeal->getRecipe() == $recipe) {
                        $counter++;
                    }
                }
            }
        }
        return ($counter > 0);
    }
}
