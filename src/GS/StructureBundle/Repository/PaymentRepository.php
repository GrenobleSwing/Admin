<?php

namespace GS\StructureBundle\Repository;

use GS\StructureBundle\Entity\Account;

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

    public function findPaymentsForAccount(Account $account)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
                ->where('p.account = :account')
                ->andWhere('p.state != :state')
                ->setParameter('state', 'DRAFT')
                ->setParameter('account', $account)
                ;

        return $qb->getQuery()->getResult();
    }

}
