<?php

namespace GS\ApiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use GS\ApiBundle\Entity\Payment;
use GS\ApiBundle\Form\Type\PaymentItemType;

class PaymentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('state', HiddenType::class, array(
                    'data' => 'PAID',
                ))
                ->add('type', ChoiceType::class, array(
                    'label' => 'Type de cours',
                    'choices' => array(
                        'Virement' => 'TRANSFER',
                        'Liquide' => 'CASH',
                        'Cheque' => 'CHECK',
                        'CB' => 'CARD',
                    ),
                ))
                ->add('date', DateType::class, array(
                    'label' => 'Date du paiement',
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-datepicker'],
                ))
                ->add('comment', TextareaType::class, array(
                    'label' => 'Commentaire',
                    'required' => false,
                ))
                ->add('items', CollectionType::class, array(
                    'label' => 'Liste des inscriptions',
                    'entry_type' => PaymentItemType::class,
                    'by_reference' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'position' => array('before' => 'submit'),
                    'attr' => array(
                        'class' => 'js-collection',
                    ),
                ))
                ->add('submit', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $payment = $event->getData();
            $form = $event->getForm();

            if (null !== $payment && 'PAID' == $payment->getState()) {
                $this->disableField($form->get('items'));
            }
        });
    }

    private function disableField(FormInterface $field)
    {
        $parent = $field->getParent();
        $options = $field->getConfig()->getOptions();
        $name = $field->getName();
        $type = get_class($field->getConfig()->getType()->getInnerType());
        $parent->remove($name);
        $parent->add($name, $type, array_merge($options, ['disabled' => true]));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Payment::class,
        ));
    }

}
