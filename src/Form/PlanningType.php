<?php

namespace App\Form;

use App\Form\DayType;
use App\Entity\Planning;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PlanningType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('day', CollectionType::class, [
                'entry_type' => DayType::class,
                'entry_options' => ['attr' => ['class' => 'form-control']],
                'label_format' => 'form.day.%name%',
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
                'attr'         => [
                    'class' => "day-collection",
                    'required' => true,
                ],
                'prototype'    => true,
                'prototype_name' => '__day__',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Planning::class,
        ]);
    }
    public function getBlockPrefix()
    {
        return 'planning';
    }
}
