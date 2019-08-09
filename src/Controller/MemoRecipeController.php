<?php

namespace App\Controller;

use App\Entity\Memo;
use App\Entity\Recipe;
use App\Form\MemoType;
use App\Form\RecipeType;
use App\Repository\MealRepository;
use App\Repository\MemoRepository;
use App\Repository\UserRepository;
use App\Repository\RecipeRepository;
use App\Repository\IngredientRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RecipeIngredientRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class MemoRecipeController extends AbstractController
{
    /**
     * @Route("/moncompte", name="dashboard")
     */
    public function showDashboard()
    {
        $user = $this->getUser();

        return $this->render('main/dashboard.html.twig', [
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
            return $this->render('main/memo/memo.html.twig', [
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
        return $this->render('main/recipes/my-recipes.html.twig', [
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

        if ($form->isSubmitted() && $form->isValid()) {

            // création de recette : on défint le user associé
            if (!$recipe->getId()) {
                $recipe->setUser($user);
            }
            $recipe->setName(htmlspecialchars($recipe->getName()));

            $instructions = $recipe->getInstructions();
            $recipe->setInstructions(htmlspecialchars($instructions));

            $link = $recipe->getLink();
            $recipe->setLink(htmlspecialchars($link));

            // on passe en revue les recipeIngredients reçus
            $ingredientsRecipe = $recipe->getRecipeIngredients();
            foreach ($ingredientsRecipe as $ingredientRecipe) {
                $ingredientId = $ingredientRecipe->getIngredient()->getId();
                // sinon, l'ingrédient existe on le récupère
                if ($ingredientId != null) {
                    $ingredient = $ingredientRepo->findOneBy(['id' => $ingredientId]);
                    //si c'est un ingrédient de l'utilisateur, accepter les mises à jour de celui-ci
                    if ($ingredient->getUser() != null) {
                        $ingredient->setName(htmlspecialchars($ingredientRecipe->getIngredient()->getName()));
                        $ingredient->setDepartment($ingredientRecipe->getIngredient()->getDepartment());
                        $manager->persist($ingredient);
                    }
                }
                // si l'ingrédient associé n'existe pas dans la liste de la bdd, le créer
                else {
                    $ingredient = $ingredientRecipe->getIngredient();
                    $ingredient->setUser($user);
                    $ingredient->setName(htmlspecialchars($ingredientRecipe->getIngredient()->getName()));
                    $ingredient->setDepartment($ingredientRecipe->getIngredient()->getDepartment());
                    $manager->persist($ingredient);
                }

                //si le recipeIngredient n'existe pas, le créer
                if (!$ingredientRecipe->getId()) {
                    $ingredientRecipe->setRecipe($recipe);
                    $ingredientRecipe->setIngredient($ingredient);
                }
                // sinon le récupérer et le mettre à jour
                else {
                    $ingredientRecipe = $recipeIngredientRepo->findOneBy(['id' => $ingredientRecipe->getId()]);
                    $ingredientRecipe->setIngredient($ingredient);
                }
                $manager->persist($ingredientRecipe);
            }
            $image = $form['image']->getData();
            if ($image) {
                if (!is_null($originalImage)) {
                    unlink($this->getParameter('images_directory') . '/' . $originalImage);
                    unlink($this->getParameter('thumbnails_directory') . '/' . $originalImage);
                }

                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newImageName = $safeFilename . '-' . uniqid() . '.' . $image->guessClientExtension();
                try {
                    $image->move($this->getParameter('images_directory'), $newImageName);
                    $recipe->setImage($newImageName);
                } catch (FileException $e) { }
            } else {
                $recipe->setImage($originalImage);
            }

            //supprime les ingredients du user qui ne sont utilisés dans aucune de ses recettes
            $ingredientsUser = $ingredientRepo->findBy(["user" => $user]);
            foreach ($ingredientsUser as $ingredientUser) {
                if ($recipeIngredientRepo->findBy(["ingredient" => $ingredientUser]) == null) {
                    $manager->remove($ingredientUser);
                }
            }
            $manager->persist($recipe);
            dump($recipe);
            $manager->flush();

            return $this->redirectToRoute('show-recipe', ['id' => $recipe->getId()]);
        }

        $listIngredients = $ingredientRepo->findByUserIngredients($user);

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

        $encodedList = $serializer->serialize($listIngredients, 'json', ['attributes' => ['id', 'name', 'department' => ['id'], 'userIsNull']]);

        return $this->render('main/recipes/create-recipe.html.twig', [
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
        return $this->render('main/recipes/one-recipe.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    /**
     * @Route("/moncompte/supprimer-ma-recette/{id}", name="delete-recipe")
     */
    public function deleteRecipe($id, RecipeRepository $recipeRepo, ObjectManager $manager, IngredientRepository $ingredientRepo, RecipeIngredientRepository $recipeIngredientRepo, UserRepository $userRepo)
    {

        $counter = 0;
        //la recette est-elle utilisée dans les plannings ??
        $plannings = $this->getUser()->getPlannings();
        foreach ($plannings as $planning) {
            $days = $planning->getDay();
            foreach ($days as $day) {
                $plannedMeals = $day->getPlannedMeal();
                foreach ($plannedMeals as $plannedMeal) {
                    if ($plannedMeal->getRecipe() == $recipeRepo->findOneBy(['id' => $id])) {
                        $counter++;
                    }
                }
            }
        }
        if (!$recipeRepo->findOneBy(['id' => $id]) || $recipeRepo->findOneBy(['id' => $id])->getUser() != $this->getUser()) {
            throw $this->createNotFoundException("La recette demandée n'existe pas.");
        } elseif ($counter > 0) {
            throw $this->createNotFoundException("La recette demandée ne peut être supprimée car elle est utilisée dans vos plannings");
        } else {
            $recipe = $recipeRepo->findOneBy(['id' => $id]);
        }

        $image = $recipe->getImage();
        if ($image != null) {
            unlink($this->getParameter('images_directory') . '/' . $image);
            unlink($this->getParameter('thumbnails_directory') . '/' . $image);
        }
        $manager->remove($recipe);
        //supprime les ingredients du user qui ne sont utilisés dans aucune de ses recettes
        $user = $this->getUser();
        $ingredientsUser = $ingredientRepo->findBy(["user" => $user]);
        foreach ($ingredientsUser as $ingredientUser) {
            if ($recipeIngredientRepo->findBy(["ingredient" => $ingredientUser]) == null) {
                $manager->remove($ingredientUser);
            }
        }
        $manager->flush();
        $filesystem = new Filesystem();
        $filesystem->remove([$image]);
        return $this->redirectToRoute('recipes');
    }
}
