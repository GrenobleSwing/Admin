<?php

namespace GS\ApiBundle\Form\Type;

use Shapecode\Bundle\HiddenEntityTypeBundle\Form\Type\HiddenEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
//use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use GS\ApiBundle\Entity\Registration;

class RegistrationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('role', ChoiceType::class, array(
                    'label' => 'Role',
                    'choices' => array(
                        'Leader' => 'leader',
                        'Follower' => 'follower',
                    ),
                ))
                ->add('withPartner', CheckboxType::class, array(
                    'label' => 'Inscription avec un partenaire',
                    'required' => false,
                ))
                ->add('partnerFirstName', TextType::class, array(
                    'label' => 'Prenom du partenaire',
                    'required' => false,
                ))
                ->add('partnerLastName', TextType::class, array(
                    'label' => 'Nom du partenaire',
                    'required' => false,
                ))
                ->add('partnerEmail', EmailType::class, array(
                    'label' => 'Adresse email du partenaire',
                    'required' => false,
                ))
//                ->add('partnerRegistration', EntityType::class, array(
//                    'class' => 'GSApiBundle:Registration',
//                    'choice_label' => 'account.displayName',
//                ))
                ->add('acceptRules', CheckboxType::class, array(
                    'label' => "Je m'engage à respecter l'objet associatif, les " .
                        "statuts (http://www.grenobleswing.com/pour-les-membres/statuts/)" .
                        " et le " .
                        "règlement intérieur (http://www.grenobleswing.com/pour-les-membres/reglement-interieur/)" .
                        ", et je déclare vouloir adhérer à l'association Grenoble Swing.",
                    'required' => true,
                ))
                ->add('submit', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $registration = $event->getData();
            $form = $event->getForm();

            if (null !== $registration && null !== $registration->getTopic()) {
                if ('couple' != $registration->getTopic()->getType()) {
                    $form->remove('role');
                    $form->remove('withPartner');
                    $form->remove('partnerFirstName');
                    $form->remove('partnerLastName');
                    $form->remove('partnerEmail');
                }
                if (!$registration->getTopic()->getActivity()->getMembersOnly() &&
                        !$registration->getTopic()->getActivity()->isMembership()) {
                    $form->remove('acceptRules');
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Registration::class,
        ));
    }

}
