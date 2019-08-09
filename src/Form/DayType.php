<?php

namespace App\Form;

use App\Entity\Day;
use App\Form\PlannedMealType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class DayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        $builder
            ->add('plannedMeal', CollectionType::class, [
                'entry_type' => PlannedMealType::class,
                'entry_options' => ['attr' => ['class' => 'form-group', 'label' => false], 'user' => $this->user],
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr'         => [
                    'class' => "meal-collection",
                    'required' => false,
                ],
                'prototype'    => true,
                'prototype_name' => '__planned_meal__',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Day::class,
            'user' => null
        ]);
    }
    public function getBlockPrefix()
    {
        return 'day';
    }
}
