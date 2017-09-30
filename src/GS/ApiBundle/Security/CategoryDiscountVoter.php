<?php

namespace GS\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

use GS\ApiBundle\Entity\Category;
use GS\ApiBundle\Entity\Discount;
use GS\ApiBundle\Entity\User;

class CategoryDiscountVoter extends Voter
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

        // only vote on Category or Discount objects inside this voter
        if (!$subject instanceof Category && !$subject instanceof Discount) {
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

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($subject, $user, $token);
            case self::VIEW:
                return $this->canView($subject, $user, $token);
            case self::EDIT:
                return $this->canEdit($subject, $user, $token);
            case self::DELETE:
                return $this->canDelete($subject, $user, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreate($subject, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        return false;
    }

    private function canView($subject, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        foreach ( $subject->getCategory()->getOwners() as $owner) {
            if ($user === $owner) {
                return true;
            }
        }
        return false;
    }

    private function canEdit($subject, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        foreach ( $subject->getCategory()->getOwners() as $owner) {
            if ($user === $owner) {
                return true;
            }
        }
        return false;
    }

    private function canDelete($subject, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        foreach ( $subject->getCategory()->getOwners() as $owner) {
            if ($user === $owner) {
                return true;
            }
        }
        return false;
    }

}