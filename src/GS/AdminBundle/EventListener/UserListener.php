<?php

namespace GS\AdminBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onChangePasswordSuccess',
        );
    }

    public function onChangePasswordSuccess(FormEvent $event)
    {
        $url = $this->router->generate('homepage');
        $event->setResponse(new RedirectResponse($url));
    }
}
