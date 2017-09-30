<?php

namespace GS\ApiBundle\Repository;

use GS\ApiBundle\Entity\Account;
use GS\ApiBundle\Entity\Activity;
use GS\ApiBundle\Entity\Topic;
use GS\ApiBundle\Entity\Year;

/**
 * RegistrationRepository
 */
class RegistrationRepository extends \Doctrine\ORM\EntityRepository
{

    public function getValidatedRegistrationsForAccount(Account $account)
    {
        // The first request is to get all the Category for which the Account
        // has at least one validated Registration.
        // With this special usage, parameters are share between the 2 queries
        // so it is useless to define them in the first query.
        $qbAcc = $this->createQueryBuilder('reg1');
        $qbAcc
                ->leftJoin('reg1.topic', 'top1')
                ->leftJoin('top1.category', 'cat1')
                ->select('cat1.id')
                ->where('reg1.account = :acc')
                ->andWhere('reg1.state = :statev');

        // The second request is to get all the validated or paid Registrations
        // for the Account using the Categories of first request to avoid all
        // the ones that only have paid Registrations.
        $qb = $this->createQueryBuilder('reg');
        $qb
                ->leftJoin('reg.topic', 'top')
                ->addSelect('top')
                ->leftJoin('top.category', 'cat')
                ->addSelect('cat')
                ->leftJoin('top.activity', 'act')
                ->addSelect('act')
                ->orderBy('act.title', 'ASC')
                ->addOrderBy('cat.name', 'ASC')
                ->addOrderBy('cat.price', 'DESC')
                ->where('reg.account = :acc')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('reg.state', ':statev'),
                    $qb->expr()->eq('reg.state', ':statep')
                ))
                ->andWhere($qb->expr()->in('cat.id', $qbAcc->getDQL()))
                ->setParameter('acc', $account)
                ->setParameter('statev', 'VALIDATED')
                ->setParameter('statep', 'PAID');

        return $qb->getQuery()->getResult();
    }

    public function getRegistrationsPaidOrValidatedForAccountAndActivity(Account $account, Activity $activity)
    {
        $qb = $this->createQueryBuilder('reg');
        $qb
                ->leftJoin('reg.topic', 'top')
                ->addSelect('top')
                ->leftJoin('top.category', 'cat')
                ->addSelect('cat')
                ->orderBy('cat.name', 'ASC')
                ->addOrderBy('cat.price', 'DESC')
                ->where('reg.account = :acc')
                ->andWhere('top.activity = :act')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('reg.state', ':statev'),
                    $qb->expr()->eq('reg.state', ':statep')
                ))
                ->setParameter('acc', $account)
                ->setParameter('act', $activity)
                ->setParameter('statev', 'VALIDATED')
                ->setParameter('statep', 'PAID');

        return $qb->getQuery()->getResult();
    }

    public function getRegistrationsNotCancelledForAccountAndActivity(Account $account, Activity $activity)
    {
        $qb = $this->createQueryBuilder('reg');
        $qb
                ->leftJoin('reg.topic', 'top')
                ->addSelect('top')
                ->where('reg.account = :acc')
                ->andWhere('top.activity = :act')
                ->andWhere($qb->expr()->neq('reg.state', ':state'))
                ->setParameter('acc', $account)
                ->setParameter('state', 'CANCELLED')
                ->setParameter('act', $activity);

        return $qb->getQuery()->getResult();
    }

    public function getRegistrationsForAccountAndYear(Account $account, Year $year)
    {
        $qb = $this->createQueryBuilder('reg');
        $qb
                ->leftJoin('reg.topic', 'top')
                ->addSelect('top')
                ->leftJoin('top.category', 'cat')
                ->addSelect('cat')
                ->leftJoin('top.activity', 'act')
                ->addSelect('act')
                ->orderBy('cat.name', 'ASC')
                ->where('reg.account = :acc')
                ->andWhere('act.year = :y')
                ->setParameter('acc', $account)
                ->setParameter('y', $year);

        return $qb->getQuery()->getResult();
    }

    public function getMembershipRegistrationsForAccountAndYear(Account $account, Year $year)
    {
        $qb = $this->createQueryBuilder('reg');
        $qb
                ->leftJoin('reg.topic', 'top')
                ->addSelect('top')
                ->leftJoin('top.category', 'cat')
                ->addSelect('cat')
                ->leftJoin('top.activity', 'act')
                ->addSelect('act')
                ->where('reg.account = :acc')
                ->andWhere('act.year = :y')
                ->andWhere('act.membership = :true')
                ->setParameter('acc', $account)
                ->setParameter('true', true)
                ->setParameter('y', $year);

        return $qb->getQuery()->getResult();
    }

    public function getMembershipRegistrationsForYear(Year $year)
    {
        $qb = $this->createQueryBuilder('reg');
        $qb
                ->leftJoin('reg.topic', 'top')
                ->addSelect('top')
                ->leftJoin('top.category', 'cat')
                ->addSelect('cat')
                ->leftJoin('top.activity', 'act')
                ->addSelect('act')
                ->where('act.year = :y')
                ->andWhere('act.membership = :true')
                ->setParameter('true', true)
                ->setParameter('y', $year);

        return $qb->getQuery()->getResult();
    }

    public function checkUniqueness(array $criteria) {
        $qb = $this->createQueryBuilder('reg');
        $qb
                ->where('reg.account = :account')
                ->andWhere('reg.topic = :topic')
                ->andWhere('reg.state != :cancel')
                ->setParameter('cancel', 'CANCELLED')
                ->setParameter('topic', $criteria['topic'])
                ->setParameter('account', $criteria['account']);

        return $qb->getQuery()->getResult();
    }

    public function getRegistrationsForAccountAndTopic(Account $account, Topic $topic)
    {
        $qb = $this->createQueryBuilder('reg');
        $qb
                ->where('reg.account = :acc')
                ->andWhere('reg.topic = :topic')
                ->andWhere($qb->expr()->notLike('reg.state', ':cancel'))
                ->setParameter('acc', $account)
                ->setParameter('topic', $topic)
                ->setParameter('cancel', '%CANCELLED');

        return $qb->getQuery()->getResult();
    }

}
