<?php

namespace GS\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TopicEmailType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('to', ChoiceType::class, array(
                    'label' => 'Destinataires',
                    'choices' => array(
                        'Tous' => 'all',
                        'Inscriptions payées' => 'paid',
                        'Inscriptions validées' => 'validated',
                        "Inscriptions en liste d'attente" => 'waiting',
                        "Inscriptions non traitées" => 'submitted',
                    ),
                    'required' => true,
//                    'expanded' => true,
//                    'multiple' => true,
                ))
                ->add('subject', TextType::class, array(
                    'label' => 'Sujet',
                    'required' => true,
                    'constraints' => array(new Assert\Length(array('min' => 3)))
                ))
                ->add('content', TextareaType::class, array(
                    'label' => 'Message',
                    'required' => true,
                    'constraints' => array(new Assert\Length(array('min' => 5)))
                ))
                ->add('submit', SubmitType::class, array(
                    'label' => "Envoyer"
                ))
        ;

    }

}
