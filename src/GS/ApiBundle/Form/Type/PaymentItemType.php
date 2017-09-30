<?php

namespace GS\ApiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

use GS\ApiBundle\Entity\PaymentItem;

class PaymentItemType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('registration', EntityType::class, array(
                    'label' => 'Inscription',
                    'class' => 'GSApiBundle:Registration',
                    'choice_label' => 'displayName',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                ->where('r.state = :state')
                                ->orderBy('r.id', 'ASC')
                                ->setParameter('state', 'VALIDATED');
                    },
                    'attr' => array(
                        'class' => 'js-select-single',
                        'style' => 'width: 100%;',
                    ),
                ))
                ->add('discount', EntityType::class, array(
                    'label' => 'Reduction a appliquer (optionnel)',
                    'class' => 'GSApiBundle:Discount',
                    'choice_label' => 'name',
                    'placeholder' => 'Choisir une reduction',
                    'empty_data'  => null,
                    'group_by' => function($discount, $key, $index) {
                        return $discount->getActivity()->getTitle();
                    },
                    'required' => false,
                    'attr' => array(
                        'class' => 'js-select-single',
                        'style' => 'width: 100%;',
                    ),
                ))
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PaymentItem::class,
        ));
    }

}
