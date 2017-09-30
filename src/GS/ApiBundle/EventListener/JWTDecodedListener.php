<?php

namespace GS\ApiBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Doctrine\ORM\EntityRepository;

class JWTDecodedListener
{

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @param EntityRepository $repository
     */
    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        $payload = $event->getPayload();
        $user = $this->repository->findOneBy(array('email' => $payload['username']));
        if ($user === null) {
            $event->markAsInvalid();
            return;
        }
        $hash = $user->getHash();
        
        if (!isset($payload['hash']) || $payload['hash'] != $hash) {
            $event->markAsInvalid();
        }
    }

}
