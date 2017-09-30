<?php

namespace GS\ApiBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use GS\ApiBundle\Entity\Year;

class YearType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('title', TextType::class, array(
                    'label' => 'Titre',
                ))
                ->add('description', TextareaType::class, array(
                    'label' => 'Description',
                ))
                ->add('startDate', DateType::class, array(
                    'label' => 'Date debut',
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-datepicker'],
                ))
                ->add('endDate', DateType::class, array(
                    'label' => 'Date fin',
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-datepicker'],
                ))
                ->add('teachers', EntityType::class, array(
                    'label' => 'Profs',
                    'class' => 'GSApiBundle:User',
                    'choice_label' => 'email',
                    'multiple' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                                ->orderBy('u.username', 'ASC');
                    },
                    'attr' => array(
                        'class' => 'js-select-multiple',
                    ),
                ))
                ->add('owners', EntityType::class, array(
                    'label' => 'Admins',
                    'class' => 'GSApiBundle:User',
                    'choice_label' => 'email',
                    'multiple' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                                ->orderBy('u.username', 'ASC');
                    },
                    'attr' => array(
                        'class' => 'js-select-multiple',
                    ),
                ))
                ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Year::class,
        ));
    }

}
