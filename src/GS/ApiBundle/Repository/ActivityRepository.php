<?php

namespace GS\ApiBundle\Repository;

use GS\ApiBundle\Entity\User;

/**
 * ActivityRepository
 */
class ActivityRepository extends \Doctrine\ORM\EntityRepository
{

    public function getActivitiesForUsers(User $user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
                ->leftJoin('a.owners', 'o')
                ->where('o.id = :user')
                ->setParameter('user', $user->getId())
                ;

        return $qb->getQuery()->getResult();
    }

}
