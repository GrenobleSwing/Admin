<?php

namespace GS\StructureBundle\EventSubscriber;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use GS\StructureBundle\Entity\Account;
use GS\StructureBundle\Entity\Address;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    private $router;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        );
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        $address = new Address();
        $account = new Account();
        $account->setUser($user);
        $account->setEmail($user->getEmail());
        $account->setAddress($address);
        $this->entityManager->persist($account);
        $this->entityManager->flush();
    }

}
