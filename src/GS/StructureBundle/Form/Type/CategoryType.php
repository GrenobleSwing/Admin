<?php

namespace GS\StructureBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use GS\StructureBundle\Entity\Category;

class CategoryType extends AbstractType
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
                    'label' => 'Nom de la categorie',
                ))
                ->add('price', MoneyType::class, array(
                    'label' => 'Prix',
                    'scale' => 2,
                ))
                ->add('canBeFreeTopicForTeachers', CheckboxType::class, array(
                    'label' => "Peut être un cours gratuit pour les profs",
                    'required' => false,
                ))
                ->add('discounts')
                ->add('submit', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $category = $event->getData();
            $form = $event->getForm();

            if (null !== $category && null !== $category->getActivity()) {
                $this->disableField($form->get('activity'));
                $form->remove('discounts');
                $form->add('discounts', EntityType::class, array(
                    'label' => 'Reductions applicables',
                    'class' => 'GSStructureBundle:Discount',
                    'choice_label' => 'name',
                    'multiple' => true,
                    'position' => array('after' => 'price'),
                    'choices' => $category->getActivity()->getDiscounts(),
                    'required' => false,
                    'attr' => array(
                        'class' => 'js-select-multiple',
                    ),
                ));
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
            'data_class' => Category::class,
        ));
    }

}
