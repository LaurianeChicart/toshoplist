<?php

namespace App\Helpers;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserHelper
{

    public static function updateUserDatas(User $user, string $originalEmail, UserPasswordEncoderInterface $encoder, ObjectManager $manager)
    {

        if ($user->getNewPassword() != null) {
            $hash = $encoder->encodePassword($user, htmlspecialchars($user->getNewPassword()));
            $user->setPassword($hash);
        }
        if ($user->getEmail() != $originalEmail) {
            $user->setEmail(htmlspecialchars($user->getEmail()));
        }
        $manager->persist($user);
        $manager->flush();
    }
}
