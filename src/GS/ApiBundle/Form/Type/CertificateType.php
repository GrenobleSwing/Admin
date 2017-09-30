<?php

namespace GS\ApiBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use GS\ApiBundle\Entity\Certificate;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class CertificateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('type', ChoiceType::class, array(
                    'label' => 'Type de justificatif',
                    'choices' => array(
                        'Etudiant' => 'student',
                        'Chômeur' => 'unemployed',
                    ),
                ))
                ->add('file', VichFileType::class, [
                    'label' => 'Fichier',
                    'required' => true,
                ])
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
                ->add('account', EntityType::class, array(
                    'label' => 'Utilisateur',
                    'class' => 'GSApiBundle:Account',
                    'choice_label' => 'displayName',
                    'attr' => array(
                        'class' => 'js-select-single',
                    ),
                ))
                ->add('year', EntityType::class, array(
                    'label' => 'Année',
                    'class' => 'GSApiBundle:Year',
                    'choice_label' => 'title',
                    'attr' => array(
                        'class' => 'js-select-single',
                    ),
                ))
                ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Certificate::class,
        ));
    }

}
