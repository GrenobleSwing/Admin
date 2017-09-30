<?php

namespace GS\ApiBundle\Security;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

use GS\ApiBundle\Entity\Registration;
use GS\ApiBundle\Entity\User;

class RegistrationVoter extends Voter
{
    // these strings are just invented: you can use anything
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
        if (!in_array($attribute, array(Registration::CREATE, self::VIEW,
            self::EDIT, self::DELETE, Registration::WAIT, Registration::VALIDATE,
            Registration::CANCEL, Registration::PAY))) {
            return false;
        }

        // only vote on Registration objects inside this voter
        if (!$subject instanceof Registration) {
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

        // you know $subject is a Registration object, thanks to supports
        $registration = $subject;

        switch ($attribute) {
            case Registration::CREATE:
                return $this->canCreate($registration, $user, $token);
            case self::VIEW:
                return $this->canView($registration, $user, $token);
            case self::EDIT:
                return $this->canEdit($registration, $user, $token);
            case self::DELETE:
                return $this->canDelete($registration, $user, $token);
            case Registration::WAIT:
                return $this->canWait($registration, $user, $token);
            case Registration::VALIDATE:
                return $this->canValidate($registration, $user, $token);
            case Registration::CANCEL:
                return $this->canCancel($registration, $user, $token);
            case Registration::PAY:
                return $this->canPay($registration, $user, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreate(Registration $registration, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_USER'))) {
            return true;
        }
        return false;
    }

    private function canView(Registration $registration, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        if ($user === $registration->getAccount()->getUser()) {
            return true;
        }
        $editors = new ArrayCollection(
                array_merge($registration->getTopic()->getOwners()->toArray(),
                        $registration->getTopic()->getModerators()->toArray(),
                        $registration->getTopic()->getActivity()->getOwners()->toArray())
        );
        foreach ($editors as $editor) {
            if ($user === $editor) {
                return true;
            }
        }
        return false;
    }

    private function isEditor(Registration $registration, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }

        $editors = new ArrayCollection(
                array_merge($registration->getTopic()->getOwners()->toArray(),
                        $registration->getTopic()->getModerators()->toArray(),
                        $registration->getTopic()->getActivity()->getOwners()->toArray())
        );
        foreach ($editors as $editor) {
            if ($user === $editor) {
                return true;
            }
        }
        return false;
    }

    private function canEdit(Registration $registration, User $user, TokenInterface $token)
    {
        if ('PAID' != $registration->getState() &&
                $this->isEditor($registration, $user, $token)) {
            // Organizer should be able to add a partner or change information if needed
            return true;
        } elseif ('SUBMITTED' != $registration->getState()) {
            return false;
        }

        if ($user === $registration->getAccount()->getUser()) {
            return true;
        }

        return false;
    }

    private function canDelete(Registration $registration, User $user, TokenInterface $token)
    {
        if ('PAID' == $registration->getState()) {
            return false;
        }

        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }
        return false;
    }

    private function canWait(Registration $registration, User $user, TokenInterface $token)
    {
        if ('SUBMITTED' != $registration->getState()) {
            return false;
        }

        return $this->isEditor($registration, $user, $token);
    }

    private function canValidate(Registration $registration, User $user, TokenInterface $token)
    {
        if ('SUBMITTED' != $registration->getState() &&
                'WAITING' != $registration->getState()) {
            return false;
        }

        return $this->isEditor($registration, $user, $token);
    }

    private function canCancel(Registration $registration, User $user, TokenInterface $token)
    {
        if ('PAID' != $registration->getState() &&
                $user === $registration->getAccount()->getUser()) {
            return true;
        }

        return $this->isEditor($registration, $user, $token);
    }

    private function canPay(Registration $registration, User $user, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_TREASURER')) &&
                'VALIDATED' == $registration->getState()) {
            return true;
        }
        return false;
    }

}