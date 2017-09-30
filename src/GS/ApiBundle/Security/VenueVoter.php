<?php

namespace GS\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

use GS\ApiBundle\Entity\Venue;
use GS\ApiBundle\Entity\User;

class VenueVoter extends Voter
{
    // these strings are just invented: you can use anything
    const CREATE = 'create';
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }
    
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::CREATE, self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        // only vote on Venue objects inside this voter
        if (!$subject instanceof Venue) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Venue object, thanks to supports
        $venue = $subject;

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($token);
            case self::VIEW:
                return $this->canView($token);
            case self::EDIT:
                return $this->canEdit($token);
            case self::DELETE:
                return $this->canDelete($token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreate(TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ORGANIZER'))) {
            return true;
        }
        return false;
    }

    private function canView(TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_USER'))) {
            return true;
        }
        return false;
    }

    private function canEdit(TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ORGANIZER'))) {
            return true;
        }
        return false;
    }

    private function canDelete(TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        return false;
    }

}