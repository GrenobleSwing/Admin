<?php

namespace GS\StructureBundle\Repository;

use GS\StructureBundle\Entity\Registration;
use GS\StructureBundle\Entity\Year;

/**
 * PaymentItemRepository
 */
class PaymentItemRepository extends \Doctrine\ORM\EntityRepository
{

    public function findPaidByRegistration(Registration $registration)
    {
        $qb = $this->createQueryBuilder('item');
        $qb
                ->leftJoin('item.payment', 'p')
                ->where('item.registration = :registration')
                ->andWhere('p.state = :state')
                ->setParameter('state', 'PAID')
                ->setParameter('registration', $registration)
                ;

        return $qb->getQuery()->getResult();
    }

}
