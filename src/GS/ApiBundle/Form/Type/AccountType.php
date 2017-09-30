<?php

namespace GS\ApiBundle\Form\Type;

use GS\ApiBundle\Entity\Account;
use GS\ApiBundle\Form\Type\AddressType;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class AccountType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('email', EmailType::class, array(
                    'label' => 'Adresse email',
                    'position' => 'first',
                ))
                ->add('firstName', TextType::class, array(
                    'label' => 'Prenom',
                ))
                ->add('lastName', TextType::class, array(
                    'label' => 'Nom',
                ))
                ->add('birthDate', BirthdayType::class, array(
                    'label' => 'Date de naissance',
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-datepicker'],
                ))
                ->add('phoneNumber', PhoneNumberType::class, array(
                    'label' => 'Numero de telephone',
                    'default_region' => 'FR',
                    'format' => PhoneNumberFormat::NATIONAL
                ))
                ->add('address', AddressType::class, array(
                    'label' => 'Adresse',
                ))
                ->add('student', CheckboxType::class, array(
                    'label'    => 'Etudiant',
                    'required' => false,
                ))
                ->add('unemployed', CheckboxType::class, array(
                    'label'    => 'Chômeur',
                    'required' => false,
                ))
//                ->add('imageFile', VichImageType::class, [
//                    'label' => 'Photo du compte',
//                    'required' => false,
//                ])
                ->add('submit', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $account = $event->getData();
            $form = $event->getForm();

            if (null !== $account && null !== $account->getEmail()) {
                $this->disableField($form->get('email'));
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
            'data_class' => Account::class,
        ));
    }

}
