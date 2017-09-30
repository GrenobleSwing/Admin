<?php

namespace GS\ApiBundle\Profiler;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class AdminMatcher implements RequestMatcherInterface
{
    protected $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function matches(Request $request)
    {
        return ($this->authorizationChecker->isGranted('ROLE_ADMIN') ||
                $this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN'));
    }
}