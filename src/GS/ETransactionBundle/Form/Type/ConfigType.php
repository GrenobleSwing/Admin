<?php

namespace GS\ETransactionBundle\Form\Type;

use GS\ETransactionBundle\Entity\Config;
use GS\ETransactionBundle\Form\Type\EnvironmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name', TextType::class, array(
                    'label' => 'Nom',
                ))
                ->add('site', TextType::class, array(
                    'label' => 'Site',
                ))
                ->add('rang', TextType::class, array(
                    'label' => 'Rang',
                ))
                ->add('identifiant', TextType::class, array(
                    'label' => 'Identifiant',
                ))
                ->add('environments', CollectionType::class, array(
                    'label' => 'Environment',
                    'entry_type' => EnvironmentType::class,
                    'by_reference' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'attr' => array(
                        'class' => 'parent-collection',
                    ),
                ))
                ->add('submit', SubmitType::class)

        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Config::class,
        ));
    }

}
