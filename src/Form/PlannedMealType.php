<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\PlannedMeal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class PlannedMealType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        $builder
            ->add('description', TextType::class, [
                'trim' => true,
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: déjeuner, apéro...'],
                'required' => false
            ])
            ->add('recipe', EntityType::class, [
                'class' => Recipe::class,
                'attr' => ['class' => 'form-control planned-meal-recipe', 'required' => true],
                'choices' => $this->user->getRecipes(),
                'group_by' => 'meal.type',
                'choice_label' => 'name',
                'label' => 'Recette*',
                'placeholder' => "Sélectionner..."
            ])
            ->add('portion', IntegerType::class, [
                'trim' => true,
                'label' => 'Nombre de parts*',
                'attr' => ['class' => 'form-control planned-meal-portion', 'min' => 1, 'max' => 15, 'required' => true,],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PlannedMeal::class,
            'user' => null
        ]);
    }
    public function getBlockPrefix()
    {
        return 'plannedMeal';
    }
}
