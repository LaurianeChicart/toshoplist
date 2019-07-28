<?php

namespace App\Controller;

use App\Entity\Memo;
use App\Entity\Recipe;
use App\Form\MemoType;
use App\Form\RecipeType;
use App\Entity\Ingredient;
use App\Repository\MealRepository;
use App\Repository\MemoRepository;
use App\Repository\UserRepository;
use App\Repository\IngredientRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RecipeIngredientRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\Entity\RecipeIngredient;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MainController extends AbstractController
{
    /**
     * @Route("/moncompte", name="dashboard")
     */
    public function showDashboard(UserRepository $userRepo)
    {
        $user = $userRepo->findOneBy(['id' => $this->getUser()->getId()]);

        return $this->render('main/dashboard.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/moncompte/memo", name="memo")
     */
    public function showMemo(UserRepository $userRepo, Request $request, ObjectManager $manager)
    {
        $user = $userRepo->findOneBy(['id' => $this->getUser()->getId()]);

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
                'item' => $memo->getItem(),
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

    /**
     * @Route("/moncompte/mes-recettes", name="recipes")
     */
    public function showMyRecipes(UserRepository $userRepo, MealRepository $mealRepo)
    {
        $user = $userRepo->findOneBy(['id' => $this->getUser()->getId()]);
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
    public function editARecipe(Recipe $recipe = null, Request $request, ObjectManager $manager, UserRepository $userRepo, IngredientRepository $ingredientRepo, RecipeIngredientRepository $recipeIngredientRepo)
    {
        $user = $userRepo->findOneBy(['id' => $this->getUser()->getId()]);

        if (!$recipe) {
            $recipe = new Recipe();
        }
        $originalImage = $recipe->getImage();


        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            dump($request);
            // création de recette : on défint le user associé
            if (!$recipe->getId()) {
                $recipe->setUser($user);
            }

            // on passe en revue les recipeIngredients reçus
            $ingredientsRecipe = $recipe->getRecipeIngredients();
            foreach ($ingredientsRecipe as $ingredientRecipe) {

                $recipe->setName(htmlspecialchars($recipe->getName()));
                $instructions = $recipe->getInstructions();
                if ($instructions != null) {
                    $recipe->setInstructions(htmlspecialchars($instructions));
                }
                $link = $recipe->getLink();
                if ($link != null) {
                    $recipe->setLink(htmlspecialchars($link));
                }

                $ingredientId = $ingredientRecipe->getIngredient()->getId();
                // si l'ingrédient associé n'existe pas dans la liste de la bdd, le créer
                if ($ingredientId == null) {
                    $ingredient = $ingredientRecipe->getIngredient();
                    $ingredient->setUser($user);
                    $ingredient->setName(htmlspecialchars($ingredientRecipe->getIngredient()->getName()));
                    $ingredient->setDepartment($ingredientRecipe->getIngredient()->getDepartment());
                    $manager->persist($ingredient);
                }
                // sinon, le récupérer
                else {
                    $ingredient = $ingredientRepo->findOneBy(['id' => $ingredientId]);
                    $ingredient->setUser(null);
                }
                //si le recipeIngredient n'existe pas, le créer
                if (!$ingredientRecipe->getId()) {

                    $ingredientRecipe->setRecipe($recipe);
                    $ingredientRecipe->setIngredient($ingredient);
                    $manager->persist($ingredientRecipe);
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

            //$manager->flush();

            //return $this->redirectToRoute('show-recipe', ['id' => $recipe->getId()]);
            dump($recipe);
        }

        $query = $manager->createQuery(
            'SELECT i
            FROM App:Ingredient i
            WHERE i.user = :user
            OR i.user IS NULL
            ORDER BY i.name'
        )->setParameter('user', $user);
        $listIngredients = $query->getResult();
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $encodedList = $serializer->serialize($listIngredients, 'json', ['attributes' => ['id', 'name', 'department' => ['id']]]);

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
    public function showOneRecipe(Recipe $recipe)
    {
        return $this->render('main/recipes/one-recipe.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    /**
     * @Route("/moncompte/supprimer-ma-recette/{id}", name="delete-recipe")
     */
    public function deleteRecipe(Recipe $recipe, ObjectManager $manager, IngredientRepository $ingredientRepo, RecipeIngredientRepository $recipeIngredientRepo, UserRepository $userRepo)
    {
        $image = $recipe->getImage();
        if ($image != null) {
            unlink($this->getParameter('images_directory') . '/' . $image);
            unlink($this->getParameter('thumbnails_directory') . '/' . $image);
        }
        $manager->remove($recipe);
        //supprime les ingredients du user qui ne sont utilisés dans aucune de ses recettes
        $user = $userRepo->findOneBy(['id' => $this->getUser()->getId()]);
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
