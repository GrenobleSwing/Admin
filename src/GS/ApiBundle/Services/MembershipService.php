<?php

namespace GS\ApiBundle\Services;

use Doctrine\ORM\EntityManager;

use GS\ApiBundle\Entity\Account;
use GS\ApiBundle\Entity\Year;

class MembershipService
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function isAlmostMember(Account $account, Year $year)
    {
        $registrations = $this->entityManager
            ->getRepository('GSApiBundle:Registration')
            ->getMembershipRegistrationsForAccountAndYear($account, $year);

        foreach ($registrations as $registration) {
            if ($registration->getState() == 'VALIDATED') {
                return true;
            }
        }
        return false;
    }

    public function isTeacher(Account $account, Year $year)
    {
        $user = $account->getUser();
        foreach ($year->getTeachers() as $teacher) {
            if ($user === $teacher) {
                return true;
            }
        }
        return false;
    }

    public function isMember(Account $account, Year $year)
    {
        if ( $this->isTeacher($account, $year) ) {
            return true;
        }

        $registrations = $this->entityManager
            ->getRepository('GSApiBundle:Registration')
            ->getMembershipRegistrationsForAccountAndYear($account, $year);

        foreach ($registrations as $registration) {
            if ($registration->getState() == 'PAID') {
                return true;
            }
        }
        return false;
    }

    public function getMembers(Year $year, $onlyPaid = true)
    {
        $registrations = $this->entityManager
            ->getRepository('GSApiBundle:Registration')
            ->getMembershipRegistrationsForYear($year);

        $accounts = [];
        foreach ($registrations as $registration) {
            if ($registration->getState() == 'PAID') {
                $accounts[] = $registration->getAccount();
            } elseif (!$onlyPaid && $registration->getState() == 'VALIDATED') {
                $accounts[] = $registration->getAccount();
            }
        }

        foreach ($year->getTeachers() as $teacher) {
            $account = $this->entityManager
                    ->getRepository('GSApiBundle:Account')
                    ->findOneByUser($teacher);
            $accounts[] = $account;
        }

        return $accounts;
    }

}