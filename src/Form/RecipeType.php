<?php

namespace App\Form;

use App\Entity\Meal;
use App\Entity\Recipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;



class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'trim' => true,
                'attr' => ['placeholder' => 'Nom de la recette', 'class' => 'form-control', 'required' => true],
                'label' => 'Nom*'
            ])
            ->add('meal', EntityType::class, [
                'class' => Meal::class,
                'attr' => ['class' => 'form-control', 'required' => true],
                'choice_label' => 'type',
                'label' => 'Type de repas*'
            ])
            ->add('portions_nb', IntegerType::class, [
                'trim' => true,
                'attr' => ['placeholder' => 'Ex: 4', 'class' => 'form-control', 'min' => 1, 'max' => 15, 'required' => true],
                'label' => 'Nombre de parts*',
            ])
            ->add('recipeIngredients', CollectionType::class, [
                'entry_type' => RecipeIngredientType::class,
                'entry_options' => ['attr' => ['class' => 'form-row']],
                'required' => true,
                'label' => 'Ingrédients*',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr'         => [
                    'class' => "ingredient-collection",
                ],
                'prototype'    => true
            ])
            ->add('instructions', TextareaType::class, [
                'trim' => true,
                'attr' => ['placeholder' => 'Les étapes de la recette', 'class' => 'form-control', 'rows' => "5"],
                'label' => 'Instructions',
                'required' => false
            ])
            ->add('link', TextType::class, [
                'trim' => true,
                'attr' => ['placeholder' => 'Ex: http://recette-originale.fr ou Livre de recette p23', 'class' => 'form-control', 'required' => false,],
                'label' => 'Lien vers la recette originale',
                'required' => false
            ])
            ->add('image', FileType::class, [
                'trim' => true,
                'label' => 'Image',
                'attr' => ['placeholder' => 'Choisir une image', 'class' => 'form-control', 'required' => false,],
                'help' => 'Taille maximale : 1 Mo ',
                'required' => false,
                'data_class' => null,
                'constraints' => [
                    new File([
                        'maxSize' => '1M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'L\'image doit être au format JPEG ou PNG',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
    public function getBlockPrefix()
    {
        return 'recipe';
    }
}
