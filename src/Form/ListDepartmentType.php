<?php

namespace App\Form;

use App\Form\ItemType;
use App\Entity\ListDepartment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ListDepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', CollectionType::class, [
                'entry_type' => ItemType::class,
                'entry_options' => ['attr' => ['label' => false]],
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr'         => [
                    'class' => "item-collection",
                    'required' => false,
                ],
                'prototype'    => true,
                'prototype_name' => '__item__',
            ])
            ->add('position', HiddenType::class, [
                'attr' => [
                    'class' => 'my-position-department',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ListDepartment::class,
        ]);
    }
    public function getBlockPrefix()
    {
        return 'listDepartment';
    }
}
