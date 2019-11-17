<?php

namespace App\Controller;

use App\Entity\Memo;
use App\Entity\Recipe;
use App\Form\MemoType;
use App\Form\RecipeType;
use App\Helpers\ImageHelper;
use App\Helpers\RecipeHelper;
use App\Repository\MealRepository;
use App\Repository\MemoRepository;
use App\Repository\RecipeRepository;
use App\Repository\IngredientRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RecipeIngredientRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MemoRecipeController extends AbstractController
{
    /**
     * @Route("/moncompte", name="dashboard")
     */
    public function showDashboard()
    {
        $user = $this->getUser();

        return $this->render('main/dashboard.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/moncompte/memo", name="memo")
     */
    public function showMemo(Request $request, ObjectManager $manager)
    {
        $user = $this->getUser();

        $memo = new Memo();
        $form = $this->createForm(MemoType::class, $memo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $memo->setUser($user);
            $memo->setItem(htmlspecialchars($memo->getItem()));
            $manager->persist($memo);
            $manager->flush();

            return $this->json([
                'message' => "L'élément a été ajouté",
                'item' => htmlspecialchars($memo->getItem()),
                'id' => $memo->getId()
            ], 200);
        } else {
            return $this->render('main/memo/memo.twig', [
                'user' => $user,
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
        $memoList = $memoRepo->findBy(['user' => $user->getId()]);

        foreach ($memoList as $memo) {
            $manager->remove($memo);
        }
        $manager->flush();

        return $this->json(['message' => 'Le mémo a été vidé'], 200);
    }

    /**
     * @Route("/moncompte/supprimer-element-memo/{id}", name="delete_memo_item")
     */
    public function deleteMemoItem($id, MemoRepository $memoRepo, ObjectManager $manager)
    {
        if (!$memoRepo->findOneBy(['id' => $id]) || $memoRepo->findOneBy(['id' => $id])->getUser() != $this->getUser()) {
            throw $this->createNotFoundException("L'élément demandé n'existe pas.");
        } else {
            $memo = $memoRepo->findOneBy(['id' => $id]);
        }
        $manager->remove($memo);
        $manager->flush();

        return $this->json(['message' => "L'élément a été supprimé"], 200);
    }

    /**
     * @Route("/moncompte/mes-recettes", name="recipes")
     */
    public function showMyRecipes(MealRepository $mealRepo)
    {
        $user = $this->getUser();
        $meals = $mealRepo->findAll();
        return $this->render('main/recipes/my-recipes.twig', [
            'user' => $user,
            'myMeals' => $meals
        ]);
    }

    /**
     * @Route("/moncompte/creation-recette", name="create-recipe")
     * @Route("/moncompte/modifier-recette/{id}", name="edit-recipe")
     */
    public function editARecipe($id = null, Request $request, ObjectManager $manager, RecipeRepository $recipeRepo, IngredientRepository $ingredientRepo, RecipeIngredientRepository $recipeIngredientRepo)
    {
        $user = $this->getUser();

        if (!$id) {
            $recipe = new Recipe();
        } else {
            if ($recipeRepo->findOneBy(['id' => $id]) != null && $recipeRepo->findOneBy(['id' => $id])->getUser() == $user) {
                $recipe = $recipeRepo->findOneBy(['id' => $id]);
            } else {
                throw $this->createNotFoundException("La recette demandée n'existe pas.");
            }
        }
        $originalImage = $recipe->getImage();


        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        $RecipeHelper = new RecipeHelper($manager, $ingredientRepo, $recipeIngredientRepo);

        if ($form->isSubmitted() && $form->isValid()) {
            // enregistrement de la recette et des ingrédients
            $ingredientsRecipe = $recipe->getRecipeIngredients();
            $RecipeHelper->saveIngredientsCollectionAndRecipe($ingredientsRecipe, $recipe, $user);
            $RecipeHelper->removeUnusefulIngredients($user);

            //enregistrement de l'image
            $image = $form['image']->getData();
            $ImageHelper = new ImageHelper($this->getParameter('images_directory'), $this->getParameter('thumbnails_directory'));

            $ImageHelper->replaceRecipeImage($image, $originalImage, $recipe);
            $manager->flush();

            return $this->redirectToRoute('show-recipe', ['id' => $recipe->getId()]);
        }

        //récupération des ingrédients + ingrédients utilisateurs pour l'autocomplétion
        $encodedList = $RecipeHelper->getNewIngredientsList($user);

        return $this->render('main/recipes/create-recipe.twig', [
            'recipeType' => $form->createView(),
            'editMode' => $recipe->getId() !== null,
            'image' => $recipe->getImage(),
            'listIngredients' => $encodedList
        ]);
    }

    /**
     * @Route("/moncompte/ma-recette/{id}", name="show-recipe")
     */
    public function showOneRecipe($id, RecipeRepository $recipeRepo)
    {
        if (!$recipeRepo->findOneBy(['id' => $id]) || $recipeRepo->findOneBy(['id' => $id])->getUser() != $this->getUser()) {
            throw $this->createNotFoundException("La recette demandée n'existe pas.");
        } else {
            $recipe = $recipeRepo->findOneBy(['id' => $id]);
        }
        return $this->render('main/recipes/one-recipe.twig', [
            'recipe' => $recipe,
        ]);
    }

    /**
     * @Route("/moncompte/supprimer-ma-recette/{id}", name="delete-recipe")
     */
    public function deleteRecipe($id, RecipeRepository $recipeRepo, ObjectManager $manager, IngredientRepository $ingredientRepo, RecipeIngredientRepository $recipeIngredientRepo)
    {
        $RecipeHelper = new RecipeHelper($manager, $ingredientRepo, $recipeIngredientRepo);
        //la recette que l'on souhaite supprimer est-elle utilisée dans les plannings ??
        $recipeUsedInPlannings = $RecipeHelper->checkThisRecipeInPlannings($this->getUser()->getPlannings(), $recipeRepo->findOneBy(['id' => $id]));

        if (!$recipeRepo->findOneBy(['id' => $id]) || $recipeRepo->findOneBy(['id' => $id])->getUser() != $this->getUser()) {
            throw $this->createNotFoundException("La recette demandée n'existe pas.");
        } elseif ($recipeUsedInPlannings) {
            throw $this->createNotFoundException("La recette demandée ne peut être supprimée car elle est utilisée dans vos plannings");
        } else {
            $recipe = $recipeRepo->findOneBy(['id' => $id]);
        }

        $image = $recipe->getImage();
        if ($image != null) {
            $ImageHelper = new ImageHelper($this->getParameter('images_directory'), $this->getParameter('thumbnails_directory'));
            $ImageHelper->removeImage($image);
        }
        $manager->remove($recipe);
        //supprime les ingredients du user qui ne sont utilisés dans aucune de ses recettes
        $user = $this->getUser();

        $RecipeHelper->removeUnusefulIngredients($user);

        $manager->flush();

        $filesystem = new Filesystem();
        $filesystem->remove([$image]);

        return $this->redirectToRoute('recipes');
    }
}
