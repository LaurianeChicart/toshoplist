<?php

namespace App\Helpers;

use App\Entity\Item;
use App\Entity\Planning;
use App\Repository\ItemRepository;
use Doctrine\Common\Persistence\ObjectManager;

class ListHelper
{
    public static function getPlanningDepartmentsInJSON(array $departments): string
    {
        $departmentNames = [];
        foreach ($departments as $department) {
            $name = $department->getDepartment()->getName();
            $departmentNames[] = $name;
        }
        return json_encode($departmentNames);
    }

    public static function registerModificationsOnItems(array $departments, ObjectManager $manager)
    {
        foreach ($departments as $department) {
            foreach ($department->getItems() as $item) {
                $item->setName(htmlspecialchars($item->getName()));
                $item->setQuantities(htmlspecialchars($item->getQuantities()));
                $item->setInitialQuantities(htmlspecialchars($item->getInitialQuantities()));
                $manager->persist($item);
            }
        }
        $manager->flush();
    }

    public static function getOrderedList(array $departments, ItemRepository $itemRepo): array
    {
        $orderedList = [];
        foreach ($departments as $department) {
            $items = $itemRepo->findBy(['list_department' => $department], ['position' => 'ASC']);
            $orderedDepartment = [$department->getDepartment()->getName(), $items];
            $orderedList[] = $orderedDepartment;
        }
        return $orderedList;
    }

    public static function toggleCheckItem(Item $item, ObjectManager $manager)
    {
        $check = $item->getChecked();
        if ($check == false) {
            $item->setChecked(true);
        } elseif ($check == true) {
            $item->setChecked(false);
        }
        $manager->persist($item);
        $manager->flush();
    }

    public static function removeMealDaysPlanning(Planning $planning, ObjectManager $manager)
    {
        foreach ($planning->getDay() as $day) {
            foreach ($day->getPlannedMeal() as $meal) {
                $manager->remove($meal);
            }
            $manager->remove($day);
        }

        $manager->remove($planning);
        $manager->flush();
    }
}
