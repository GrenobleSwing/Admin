<?php

namespace GS\StructureBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use GS\StructureBundle\Entity\Discount;

class DiscountType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('activity', EntityType::class, array(
                    'class' => 'GSStructureBundle:Activity',
                    'choice_label' => 'title',
                    'position' => 'first',
                ))
                ->add('name', TextType::class, array(
                    'label' => 'Nom de la reduction',
                ))
                ->add('type', ChoiceType::class, array(
                    'label' => 'Type de reduction',
                    'choices' => array(
                        'Pourcentage' => 'percent',
                        'Somme' => 'amount',
                    ),
                ))
                ->add('value', NumberType::class, array(
                    'label' => 'Valeur',
                    'scale' => 2,
                ))
                ->add('condition', ChoiceType::class, array(
                    'label' => 'Condition d\'application',
                    'choices' => array(
                        'Membre' => 'member',
                        'Etudiant' => 'student',
                        'Chômeur' => 'unemployed',
                        '2e cours' => '2nd',
                        '3e cours' => '3rd',
                        '4e cours' => '4th',
                        '5e cours' => '5th',
                    ),
                ))
                ->add('submit', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $discount = $event->getData();
            $form = $event->getForm();

            if (null !== $discount && null !== $discount->getActivity()) {
                $this->disableField($form->get('activity'));
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
            'data_class' => Discount::class,
        ));
    }

}
