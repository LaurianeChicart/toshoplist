<?php

namespace App\Form;

use App\Entity\Item;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'trim' => true,
                'label' => false,
                'attr' => ['class' => 'form-control item-name', 'placeholder' => 'Nom de du produit', 'required' => true],

            ])
            ->add('quantities', TextType::class, [
                'trim' => true,
                'label' => false,
                'attr' => ['class' => 'form-control item-quantities', 'placeholder' => 'Quantité (facultatif)', 'required' => false],
            ])
            ->add('position', HiddenType::class, [
                'attr' => [
                    'class' => 'my-position-item'
                ]
            ])
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [$this, 'onPreSetData']
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }
    public function getBlockPrefix()
    {
        return 'item';
    }
    public function onPreSetData(FormEvent $event)
    {
        $item = $event->getData();
        $form = $event->getForm();

        if (!$item) {
            return;
        }

        if ($item->getInitialQuantities() != null) {
            dump($item);
            $form->add('initial_quantities', TextType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control', 'disabled' => true, 'formnovalidate' => true],
                'required' => false,
            ]);
        } else {
            return;
        }
    }
}
