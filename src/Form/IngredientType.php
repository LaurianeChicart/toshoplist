<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\Ingredient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class IngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['placeholder' => 'Nom', 'class' => 'form-control ui-widget'],
                'label' => 'Nom*',
                'required'   => false
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'name',
                'attr' => ['class' => 'form-control ui-widget-department'],
                'label' => 'Rayon*',
                'required'   => false,
                'placeholder' => 'Sélectionner...',
            ])
            ->add('id', HiddenType::class, [
                'required'   => false,
                'attr' => ['class' => 'ui-widget-id', 'disabled' => 'disabled'],

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ingredient::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ingredientType';
    }
}
