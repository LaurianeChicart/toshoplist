<?php

namespace App\DataFixtures;

use App\Entity\Meal;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class MealFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $meals = ["Déjeuner/Dîner", "Petit-déjeuner", "Entrée", "Dessert", "Apéro", "Collation"];
        foreach ($meals as $meal_name) {
            $meal = new Meal();
            $meal->setType($meal_name);
            $manager->persist($meal);
        }

        $manager->flush();
    }
}
