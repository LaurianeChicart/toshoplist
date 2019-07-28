<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserDatasType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                ['label' => 'Email'],
                ['trim' => true]
            )
            ->add(
                'current_password',
                PasswordType::class,
                ['label' => 'Mot de passe actuel'],
                ['trim' => true]
            )
            ->add(
                'new_password',
                PasswordType::class,
                [
                    'label' => 'Nouveau mot de passe',
                    'trim' => true,
                    'required' => false
                ]
            )
            ->add(
                'confirm_new_password',
                PasswordType::class,
                [
                    'label' => 'Confirmation du nouveau mot de passe',
                    'trim' => true,
                    'required' => false
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
