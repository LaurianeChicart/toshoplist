<?php

namespace App\Helpers;

use DateTime;
use App\Entity\Day;
use App\Entity\User;
use App\Entity\Planning;
use App\Entity\ListItems;
use Doctrine\Common\Persistence\ObjectManager;

class PlanningHelper
{
    public static function registerPlanningAndDays(DateTime $startDate, DateTime $stopDate, int $diff, ObjectManager $manager, User $user)
    {
        $intervalDate = [date_format($startDate, 'Y-m-d')];
        for ($i = 0; $i < $diff; $i++) {
            $newDate = date_add($startDate, date_interval_create_from_date_string('1 days'));
            array_push($intervalDate, date_format($newDate, 'Y-m-d'));
        }
        $planning = new Planning;
        $planning->setUser($user);
        $planning->setCreatedAt(new DateTime());

        $manager->persist($planning);
        foreach ($intervalDate as $date) {
            $day = new Day();
            $day->setDate(DateTime::createFromFormat('Y-m-d', $date));
            $day->setPlanning($planning);
            $manager->persist($day);
        }
        $manager->flush();
        return $planning;
    }

    public static function getPlanningList(Planning $planning)
    {
        if (!$planning->getListItems()) {
            $list = new ListItems();
            $list->setPlanning($planning);
        } else {
            $list = $planning->getListItems();
        }
        return $list;
    }
    public static function getLabelSubmitForPlanning(Planning $planning): string
    {
        if (!$planning->getListItems()) {
            return "Créer ma liste";
        } else {
            return "Enregistrer les modifications";
        }
    }

    public static function getPlanningDatesInJSON(array $data, array $days): string
    {
        //tableau des timestamps de la période sélectionnée à transmettre à javascript pour assurer l'affichage des dates (ex: mercredi 31 juillet)
        $allDates = [];
        foreach ($days as $day) {
            setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
            $dayDate = $day->getDate();
            $date = $dayDate->getTimestamp();
            $allDates[] = $date;
        }
        return json_encode($allDates);
    }
}
