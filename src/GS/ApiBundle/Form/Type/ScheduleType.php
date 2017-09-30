<?php

namespace GS\ApiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use GS\ApiBundle\Entity\Schedule;

class ScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('startDate', DateType::class, array(
                    'label' => 'Date debut',
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-datepicker'],
                ))
                ->add('startTime', TimeType::class, array(
                    'input' => 'datetime',
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-timepicker'],
                ))
                ->add('endTime', TimeType::class, array(
                    'input' => 'datetime',
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-timepicker'],
                ))
                ->add('endDate', DateType::class, array(
                    'label' => 'Date fin',
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-datepicker'],
                ))
                ->add('frequency', ChoiceType::class, array(
                    'label' => 'Frequence',
                    'choices' => array(
                        'Ponctuel' => 'once',
                        'Hebdomadaire' => 'weekly',
                    )
                ))
                ->add('venue', EntityType::class, array(
                    'label' => 'Salle',
                    'class' => 'GSApiBundle:Venue',
                    'choice_label' => 'name',
                ))
                ->add('teachers', TextType::class, array(
                    'label' => 'Profs',
                    'required' => false,
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Schedule::class,
        ));
    }

}
