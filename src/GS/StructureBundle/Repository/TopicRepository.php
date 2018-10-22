<?php

namespace GS\StructureBundle\Repository;

use GS\StructureBundle\Entity\Activity;
use GS\StructureBundle\Entity\User;
use GS\StructureBundle\Entity\Year;

/**
 * TopicRepository
 */
class TopicRepository extends \Doctrine\ORM\EntityRepository
{
    public function getMembershipTopicsForActivity(Activity $activity)
    {
        $qbY = $this->createQueryBuilder('t1');
        $qbY
                ->leftJoin('t1.activity', 'act1')
                ->select('act1')
                ->leftJoin('act1.year', 'y1')
                ->select('y1.id')
                ->where('act1 = :act');

        $qb = $this->createQueryBuilder('t');
        $qb
                ->leftJoin('t.activity', 'act')
                ->addSelect('act')
                ->leftJoin('act.year', 'y')
                ->addSelect('y')
                ->where('act.membership = :true')
                ->andWhere($qb->expr()->in('y.id', $qbY->getDQL()))
                ->setParameter('act', $activity)
                ->setParameter('true', true);

        return $qb->getQuery()->getResult();
    }

    public function getMembershipTopicsForYear(Year $year)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
                ->leftJoin('t.activity', 'act')
                ->addSelect('act')
                ->where('act.membership = :true')
                ->andWhere('act.year = :year')
                ->setParameter('year', $year)
                ->setParameter('true', true);

        return $qb->getQuery()->getResult();
    }

    public function getOpenTopics()
    {
        $qb = $this->createQueryBuilder('t');
        $qb
                ->where('t.state = :open')
                ->setParameter('open', 'OPEN');

        return $qb->getQuery()->getResult();
    }

    public function getTopicsForUsers(User $user)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
                ->leftJoin('t.moderators', 'm')
                ->leftJoin('t.owners', 'o')
                ->where('o.id = :user')
                ->orWhere('m.id = :user')
                ->setParameter('user', $user->getId())
                ;

        return $qb->getQuery()->getResult();
    }

    public function getOpenTopicsNotAdhesion(Year $year = null)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
                ->where('t.type <> :type')
                ->andWhere('t.state = :open')
                ->setParameter('open', 'OPEN')
                ->setParameter('type', 'adhesion');

        if (null !== $year) {
            $qb
                    ->leftJoin('t.activity', 'act')
                    ->addSelect('act')
                    ->andWhere('act.year = :year')
                    ->setParameter('year', $year);
        }

        return $qb->getQuery()->getResult();
    }

}
