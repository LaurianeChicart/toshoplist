<?php

namespace App\Form;

use App\Form\IngredientType;
use App\Entity\RecipeIngredient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


class RecipeIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', TextType::class, [
                'trim' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 1, 0.5...', 'required' => true],
                'label' => 'Quantité*',
            ])
            ->add('measure', ChoiceType::class, [
                'choices'  => [
                    'unité' => 'unité',
                    'g' => 'g',
                    'kg' => 'kg',
                    'mL' => 'mL',
                    'cL' => 'cL',
                    'L' => 'L',
                    'c à C' => 'c à C',
                    'c à S' => 'c à S',
                    'tranche' => 'tranche',
                    'filet' => 'filet'
                ],
                'label' => 'Mesure*',
                'attr' => ['class' => 'form-control', 'required' => true]
            ])
            ->add('ingredient', IngredientType::class, [
                'label' => false,
                'attr' => ['class' => 'form-row']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecipeIngredient::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'recipeIngredientType';
    }
}
