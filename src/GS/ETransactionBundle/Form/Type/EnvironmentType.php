<?php

namespace GS\ETransactionBundle\Form\Type;

use GS\ETransactionBundle\Entity\Environment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnvironmentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name', TextType::class, array(
                    'label' => 'Nom',
                ))
                ->add('hmacKey', TextType::class, array(
                    'label' => 'Clé HMAC',
                ))
                ->add('urlClassique', UrlType::class, array(
                    'label' => 'URL classique',
                ))
                ->add('urlLight', UrlType::class, array(
                    'label' => 'URL light (iFrame)',
                ))
                ->add('urlMobile', UrlType::class, array(
                    'label' => 'URL mobile',
                ))
                ->add('validIps', CollectionType::class, array(
                    'label' => 'IP valides',
                    'entry_type' => TextType::class,
                    'by_reference' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'attr' => array(
                        'class' => 'child-collection',
                    ),
                ))
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Environment::class,
        ));
    }

}
