<?php

namespace GS\ApiBundle\EventListener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use GS\ApiBundle\Entity\Account;
use GS\ApiBundle\Entity\Address;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(EntityManager $entityManager, UrlGeneratorInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onChangePasswordSuccess',
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

    public function onChangePasswordSuccess(FormEvent $event)
    {
        $url = $this->router->generate('homepage');
        $event->setResponse(new RedirectResponse($url));
    }
}
