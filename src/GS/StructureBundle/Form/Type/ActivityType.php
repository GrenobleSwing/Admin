<?php

namespace GS\StructureBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use GS\StructureBundle\Entity\Activity;
use GS\StructureBundle\Entity\Registration;

class ActivityType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('year', EntityType::class, array(
                    'class' => 'GSStructureBundle:Year',
                    'choice_label' => 'title',
                    'position' => 'first',
                ))
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
                ->add('allowSemester', CheckboxType::class, array(
                    'label' => "Autoriser l'inscription au semestre",
                    'required' => false,
                ))
                # Filter the list of topics using PRE_SET_DATA
                # Not perfect since Year can be changed
                # TODO: improve it
                ->add('membershipTopic', EntityType::class, array(
                    'class' => 'GSStructureBundle:Topic',
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
                    'class' => 'GSStructureBundle:User',
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $activity = $event->getData();
            $form = $event->getForm();

            if (null !== $activity && null !== $activity->getYear()) {
                $year = $activity->getYear();
                $form->remove('membershipTopic');
                $form->add('membershipTopic', EntityType::class, array(
                    'label' => "Adhésion (obligatoire) associée a l'activité",
                    'class' => 'GSStructureBundle:Topic',
                    'choice_label' => 'title',
                    'placeholder' => "Choissisez l'adhésion obligatoire",
                    'required' => false,
                    'attr' => array(
                        'class' => 'js-select-single',
                    ),
                    'position' => array('after' => 'allowSemester'),
                    'choices' => $year->getMembershipTopics(),
                ));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Activity::class,
        ));
    }

}
