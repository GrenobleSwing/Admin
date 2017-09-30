<?php

namespace GS\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

use GS\ApiBundle\Entity\Topic;
use GS\ApiBundle\Entity\User;

class TopicVoter extends Voter
{
    // these strings are just invented: you can use anything
    const CREATE = 'create';
    const VIEW = 'view';
    const EDIT = 'edit';
    const MODERATE = 'moderate';
    const OPEN = 'open';
    const CLOSE = 'close';
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
            self::MODERATE, self::OPEN, self::CLOSE, self::DELETE))) {
            return false;
        }

        // only vote on Topic objects inside this voter
        if (!$subject instanceof Topic) {
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

        // you know $subject is a Topic object, thanks to supports
        $topic = $subject;

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($topic, $user, $token);
            case self::VIEW:
                return $this->canView($topic, $user, $token);
            case self::EDIT:
                return $this->canEdit($topic, $user, $token);
            case self::MODERATE:
                return $this->canModerate($topic, $user, $token);
            case self::OPEN:
                return $this->canOpen($topic, $user, $token);
            case self::CLOSE:
                return $this->canClose($topic, $user, $token);
            case self::DELETE:
                return $this->canDelete($topic, $user, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreate(Topic $topic, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        foreach ($topic->getActivity->getOwners() as $owner) {
            if ($user === $owner) {
                return true;
            }
        }
        return false;
    }

    private function canView(Topic $topic, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_USER'))) {
            return true;
        }
        return false;
    }

    private function isOwner(Topic $topic, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        foreach ($topic->getOwners() as $owner) {
            if ($user === $owner) {
                return true;
            }
        }
        return false;
    }

    private function isModerator(Topic $topic, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        foreach ($topic->getModerators() as $moderator) {
            if ($user === $moderator) {
                return true;
            }
        }
        return false;
    }

    private function canEdit(Topic $topic, User $user, TokenInterface $token)
    {
        return $this->isOwner($topic, $user, $token);
    }

    private function canModerate(Topic $topic, User $user, TokenInterface $token)
    {
        return $this->isOwner($topic, $user, $token) ||
                $this->isModerator($topic, $user, $token);
    }

    private function canOpen(Topic $topic, User $user, TokenInterface $token)
    {
        if ('DRAFT' != $topic->getState()) {
            return false;
        }
        return $this->isOwner($topic, $user, $token);
    }

    private function canClose(Topic $topic, User $user, TokenInterface $token)
    {
        if ('OPEN' != $topic->getState()) {
            return false;
        }
        return $this->isOwner($topic, $user, $token);
    }

    private function canDelete(Topic $topic, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        if (count($topic->getRegistrations()) == 0) {
            return $this->isOwner($topic, $user, $token);
        }
        return false;
    }

}