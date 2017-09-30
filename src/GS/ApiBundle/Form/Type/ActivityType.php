<?php

namespace GS\ApiBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use GS\ApiBundle\Entity\Activity;
use GS\ApiBundle\Entity\Registration;

class ActivityType extends AbstractType
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
                ->add('membersOnly', CheckboxType::class, array(
                    'label' => "Reservé aux membres de l'association",
                    'required' => false,
                ))
                ->add('membershipTopic', EntityType::class, array(
                    'label' => "Adhésion (obligatoire) associée a l'activité",
                    'class' => 'GSApiBundle:Topic',
                    'choice_label' => 'title',
                    'placeholder' => "Choissisez l'adhésion obligatoire",
                    'required' => false,
                    'attr' => array(
                        'class' => 'js-select-single',
                    ),
                ))
                ->add('membership', ChoiceType::class, array(
                    'label' => 'Ensemble des adhésions possibles',
                    'choices' => array(
                        "Oui" => true,
                        "Non" => false
                    )
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
                ->add('triggeredEmails', ChoiceType::class, array(
                    'label' => 'Liste des emails à envoyer',
                    'choices' => array(
                        "Soumission" => Registration::CREATE,
                        "Mise en liste d'attente" => Registration::WAIT,
                        "Validation" => Registration::VALIDATE,
                        "Annulation" => Registration::CANCEL,
                    ),
                    'multiple' => true,
                    'expanded' => true,
                ))
                ->add('submit', SubmitType::class)
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Activity::class,
        ));
    }

}
