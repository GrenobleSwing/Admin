<?php

namespace GS\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

use GS\ApiBundle\Entity\Account;
use GS\ApiBundle\Entity\User;

class AccountVoter extends Voter
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

        // only vote on Account objects inside this voter
        if (!$subject instanceof Account) {
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

        // you know $subject is a Account object, thanks to supports
        $account = $subject;

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate();
            case self::VIEW:
                return $this->canView($account, $user, $token);
            case self::EDIT:
                return $this->canEdit($account, $user, $token);
            case self::DELETE:
                return $this->canDelete($account, $user, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreate()
    {
        return true;
    }

     private function canView(Account $account, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        if ($user === $account->getUser()) {
            return true;
        }
        return false;
    }

    private function canEdit(Account $account, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        if ($user === $account->getUser()) {
            return true;
        }
        return false;
    }

    private function canDelete(Account $account, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        if ($user === $account->getUser()) {
            return true;
        }
        return false;
    }

}