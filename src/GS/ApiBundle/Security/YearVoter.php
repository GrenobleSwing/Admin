<?php

namespace GS\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

use GS\ApiBundle\Entity\Year;
use GS\ApiBundle\Entity\User;

class YearVoter extends Voter
{
    // these strings are just invented: you can use anything
    const CREATE = 'create';
    const VIEW = 'view';
    const EDIT = 'edit';
    const OPEN = 'open';
    const CLOSE = 'close';
    const ADD_ACTIVITY = 'add_activity';
    const DELETE = 'delete';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::CREATE, self::VIEW, self::EDIT,
            self::OPEN, self::CLOSE, self::DELETE, self::ADD_ACTIVITY))) {
            return false;
        }

        // only vote on Year objects inside this voter
        if (!$subject instanceof Year) {
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

        // you know $subject is a Year object, thanks to supports
        $year = $subject;

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($token);
            case self::VIEW:
                return $this->canView($token);
            case self::EDIT:
                return $this->canEdit($year, $user, $token);
            case self::OPEN:
                return $this->canOpen($year, $user, $token);
            case self::CLOSE:
                return $this->canClose($year, $user, $token);
            case self::ADD_ACTIVITY:
                return $this->canAddActivity($year, $user, $token);
            case self::DELETE:
                return $this->canDelete($year, $user, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreate(TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
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

    private function isOwner(Year $year, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        foreach ( $year->getOwners() as $owner) {
            if ($user === $owner) {
                return true;
            }
        }
        return false;
    }

    private function canEdit(Year $year, User $user, TokenInterface $token)
    {
        return $this->isOwner($year, $user, $token);
    }

    private function canAddActivity(Year $year, User $user, TokenInterface $token)
    {
        if ('CLOSE' == $year->getState()) {
            return false;
        }
        if ($this->decisionManager->decide($token, array('ROLE_ORGANIZER'))) {
            return true;
        }
        return $this->isOwner($year, $user, $token);
    }

    private function canOpen(Year $year, User $user, TokenInterface $token)
    {
        if ('DRAFT' != $year->getState()) {
            return false;
        }
        return $this->isOwner($year, $user, $token);
    }

    private function canClose(Year $year, User $user, TokenInterface $token)
    {
        if ('OPEN' != $year->getState()) {
            return false;
        }
        return $this->isOwner($year, $user, $token);
    }

    private function canDelete(Year $year, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN')) &&
                $year->getState() == 'DRAFT') {
            return true;
        }
        if ('DRAFT' === $year->getState()) {
            return $this->isOwner($year, $user, $token);
        }
        return false;
    }

}