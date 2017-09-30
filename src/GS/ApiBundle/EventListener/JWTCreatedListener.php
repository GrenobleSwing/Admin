<?php

namespace GS\ApiBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $hash = $event->getUser()->getHash();

        $payload = $event->getData();
        $payload['hash'] = $hash;

        $event->setData($payload);
    }

}
