<?php

namespace App\DataFixtures;

use App\Entity\Department;
use App\Entity\Ingredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class DepartmentFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $fruits_et_legumes = ["Abricot", "Ananas", "Avocat", "Banane", "Cerise", "Citron", "Citron vert", "Clémentine", "Mandarine", "Figue", "Fraise", "Frambois", "Goyave", "Grenade", "Groseille", "Kiwi", "Litchi", "Mangue", "Melon", "Mirabelle", "Mûre", "Myrtille", "Noix", "Noix de coco", "Orange", "Papaye", "Pastèque", "Pêche", "Nectarine", "Poire", "Pomme", "Prune", "Raisin", "Rhubarbe", "Ail", "Artichaut", "Asperge", "Aubergine", "Betterave", "Brocoli", "Carotte", "Céleri-branche", "Céleri-rave", "Champignon de Paris", "Champignons", "Chou kale", "Chou-fleur", "Choux de Bruxelles", "Concombre", "Courge", "Potiron", "Courgette", "Échalote", "Endive", "Épinard", "Fenouil", "Haricot vert", "Laitue", "Mâche", "Navet", "Oignon", "Panais", "Patate douce", "Petits pois", "Poireau", "Pois gourmand", "Poivron rouge", "Poivron vert", "Poivron jaune", "Pomme de terre", "Potiron", "Radis", "Rutabaga", "Salade", "Tomate", "Topinambour"];

        $epicerie = ["Céréales", "Corn flakes", "Muesli", "Flocons d'avoine", "Thé", "Café", "Infusion", "Biscottes", "Pains grillés", "Pain", "Pain de mie", "Brioche", "Chocolat", "Confiture", "Confiture de fraise", "Confiture d'abricot", "Pâte à tartiner", "Miel", "Caramel", "Beurre de cacahuètes", "Sucre", "Sucre roux", "Farine", "Sel", "Poivre", "Gingembre", "Curry", "Curcuma", "Piment", "Paprika", "Herbes de provence", "Basilic", "Cumin", "Huile d'olive", "Huile de coco", "Huile de colza", "Huile de tournesol", "Vinaigre blanc", "Vinaigre balsamique", "Chips", "Cacahuètes", "Olives", "Sauce soja", "Mayonnaise", "Ketchup", "Moutarde", "Pâtes", "Riz", "Lentilles", "Quinoa", "Croûtons", "Lait", "Lait de soja", "Lait d'amandes"];

        $viandes_et_poissons = ["Poulet", "Blanc de poulet", "Escalope de dinde", "Steak", "Steak hâché", "Rôti de porc", "Saucisse", "Chipolata", "Merguez", "Jambon", "Blanc de dinde", "Jambon cru", "Saumon", "Saumon fûmé", "Truite", "Truite fumée", "Thon", "Maquereau", "Sardines", "Colin", "Lieu", "Moules", "Crevettes", "Langoustines", "Huîtres", "Merlan "];

        $frais = [" Oeuf ", "Yaourt", "Fromage blanc", "Beurre", "Pâte brisée", "Pâte feuilletée", "Pâte sablée", "Tofu", "Steak de soja", "Fromage", "Emmental ", "Emmental râpé", "Fromage  de chèvre", "Parmesan", "Mozarella", "Crème fraîche"];

        $user = null;

        $department1 = new Department();
        $department1->setName("Épicerie");
        $manager->persist($department1);
        foreach ($epicerie as $epicerie_item) {
            $ingredient = new Ingredient();
            $ingredient->setName($epicerie_item);
            $ingredient->setDepartment($department1);
            $ingredient->setUser($user);
            $manager->persist($ingredient);
        }


        $department2 = new Department();
        $department2->setName("Fruits et légumes");
        $manager->persist($department2);
        foreach ($fruits_et_legumes as $fruits_et_legumes_item) {
            $ingredient = new Ingredient();
            $ingredient->setName($fruits_et_legumes_item);
            $ingredient->setDepartment($department2);
            $ingredient->setUser($user);
            $manager->persist($ingredient);
        }


        $department3 = new Department();
        $department3->setName("Viandes et poissons");
        $manager->persist($department3);
        foreach ($viandes_et_poissons as  $viandes_et_poissons_item) {
            $ingredient = new Ingredient();
            $ingredient->setName($viandes_et_poissons_item);
            $ingredient->setDepartment($department3);
            $ingredient->setUser($user);
            $manager->persist($ingredient);
        }

        $department4 = new Department();
        $department4->setName("Produits frais");
        $manager->persist($department4);
        foreach ($frais as $frais_item) {
            $ingredient = new Ingredient();
            $ingredient->setName($frais_item);
            $ingredient->setDepartment($department4);
            $ingredient->setUser($user);
            $manager->persist($ingredient);
        }

        $department5 = new Department();
        $department5->setName("Divers");
        $manager->persist($department5);

        $manager->flush();
    }
}
