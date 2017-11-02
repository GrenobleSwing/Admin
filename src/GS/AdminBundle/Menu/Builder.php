<?php

namespace GS\AdminBundle\Menu;

use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;

class Builder
{
    private $entityManager;
    private $factory;

    public function __construct(FactoryInterface $factory, EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->factory = $factory;
    }

    public function mainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('Profil')->setAttribute('dropdown', true);
        $menu['Profil']->addChild('Profil', array(
            'route' => 'gsadmin_my_account',
        ));
        $menu['Profil']->addChild('Changer mot de passe', array(
            'route' => 'fos_user_change_password',
        ));

        return $menu;
    }

    public function organizerMenu(array $options)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('Orga')->setAttribute('dropdown', true);
        $menu['Orga']->addChild('Liste des années', array(
            'route' => 'gsadmin_index_year',
        ));

        return $menu;
    }

    public function treasurerMenu(array $options)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('Trésorier')->setAttribute('dropdown', true);
        $menu['Trésorier']->addChild('Liste des paiements', array(
            'route' => 'gsadmin_index_payment',
            'routeParameters' => array('state' => 'PAID')
        ));
        $menu['Trésorier']->addChild('Ajouter un paiement', array(
            'route' => 'gsadmin_add_payment',
        ));
        $menu['Trésorier']
                ->addChild('Liste des justificatifs', array(
                    'route' => 'gsadmin_index_certificate',
                ))
                ->setAttribute('divider_prepend', true);
        $menu['Trésorier']->addChild('Ajouter un justificatif', array(
            'route' => 'gsadmin_add_certificate',
        ));

        return $menu;
    }

    public function secretaryMenu(array $options)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $year = $this->entityManager
            ->getRepository('GSStructureBundle:Year')
            ->findCurrentYear()
            ;

        $menu->addChild('Secrétaire')->setAttribute('dropdown', true);
        if (null === $year) {
            $menu['Secrétaire']->addChild("Aucun membres pour l'année en cours", array(
                'route' => 'homepage',
            ));

        } else {
            $menu['Secrétaire']->addChild('Liste des membres', array(
                'route' => 'gsadmin_index_member',
                'routeParameters' => array(
                    'id' => $year->getId(),
                )
            ));
            $menu['Secrétaire']->addChild("Liste des membres (incluant ceux n'ayant pas encore payé)", array(
                'route' => 'gsadmin_index_member',
                'routeParameters' => array(
                    'id' => $year->getId(),
                    'all' => true,
                )
            ));
            $menu['Secrétaire']->addChild('Liste des inscriptions', array(
                'route' => 'gsadmin_index_registration',
            ));
        }

        return $menu;
    }

    public function adminMenu(array $options)
    {
        $societies = $this->entityManager
            ->getRepository('GSStructureBundle:Society')
            ->findAll()
            ;

        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('Admin')->setAttribute('dropdown', true);
        if (count($societies) > 0) {
            $society = $societies[0];
            $menu['Admin']->addChild('Société', array(
                'route' => 'gsadmin_view_society',
            ));
            $menu['Admin']->addChild('Ajouter une année', array(
                'route' => 'gsadmin_add_year',
                'routeParameters' => array('id' => $society->getId())
            ));
        } else {
            $menu['Admin']->addChild('Créer société', array(
                'route' => 'gsadmin_add_society',
            ));
        }
        $menu['Admin']->addChild('Liste des utilisateurs', array(
            'route' => 'gsadmin_index_user',
        ));
        $menu['Admin']->addChild('Liste des paiements', array(
            'route' => 'gsadmin_index_payment',
        ));
        $menu['Admin']
            ->addChild('Email list', array(
                'route' => 'lexik_mailer.email_list',
            ))
            ->setAttribute('divider_prepend', true);
        $menu['Admin']->addChild('Layout list', array(
            'route' => 'lexik_mailer.layout_list',
        ));

        return $menu;
    }

}
