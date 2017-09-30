<?php

namespace GS\ApiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use GS\ApiBundle\Entity\Address;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('street', TextType::class, array(
                    'label' => 'Rue',
                ))
                ->add('zipCode', TextType::class, array(
                    'label' => 'Code postal',
                ))
                ->add('city', TextType::class, array(
                    'label' => 'Ville',
                ))
                ->add('county', TextType::class, array(
                    'label' => 'Departement',
                    'required' => false,
                ))
                ->add('state', TextType::class, array(
                    'label' => 'Region',
                    'required' => false,
                ))
                ->add('country', CountryType::class, array(
                    'label' => 'Pays',
                    'data' => 'FR',
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Address::class,
        ));
    }

}
