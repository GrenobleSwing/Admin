<?php

namespace GS\ApiBundle\Form\Type;

use GS\ApiBundle\Entity\Discount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiscountType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name', TextType::class, array(
                    'label' => 'Nom de la reduction',
                ))
                ->add('type', ChoiceType::class, array(
                    'label' => 'Type de reduction',
                    'choices' => array(
                        'Pourcentage' => 'percent',
                        'Somme' => 'amount',
                    ),
                ))
                ->add('value', NumberType::class, array(
                    'label' => 'Valeur',
                    'scale' => 2,
                ))
                ->add('condition', ChoiceType::class, array(
                    'label' => 'Condition d\'application',
                    'choices' => array(
                        'Membre' => 'member',
                        'Etudiant' => 'student',
                        'Chômeur' => 'unemployed',
                        '2e cours' => '2nd',
                        '3e cours' => '3rd',
                        '4e cours' => '4th',
                        '5e cours' => '5th',
                    ),
                ))
                ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Discount::class,
        ));
    }

}
