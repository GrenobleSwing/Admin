<?php

namespace GS\ApiBundle\Repository;

use GS\ApiBundle\Entity\User;

/**
 * PaymentRepository
 */
class PaymentRepository extends \Doctrine\ORM\EntityRepository
{

    public function findDraftPaymentToPrune()
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P1D'));

        $qb = $this->createQueryBuilder('p');
        $qb
                ->where($qb->expr()->gt(':date', 'p.date'))
                ->andWhere('p.state = :state')
                ->setParameter('state', 'DRAFT')
                ->setParameter('date', $date, \Doctrine\DBAL\Types\Type::DATETIME)
                ;

        return $qb->getQuery()->getResult();
    }

}
