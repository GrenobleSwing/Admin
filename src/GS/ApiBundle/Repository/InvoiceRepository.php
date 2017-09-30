<?php

namespace GS\ApiBundle\Repository;

/**
 * InvoiceRepository
 */
class InvoiceRepository extends \Doctrine\ORM\EntityRepository
{

    public function countByNumber($prefix = '')
    {
        $qb = $this->createQueryBuilder('i');
        $qb
                ->select('count(i.id)')
                ->where($qb->expr()->like('i.number', ':prefix'))
                ->setParameter('prefix', $prefix . '%')
                ;
        return $qb->getQuery()->getSingleScalarResult();
    }

}
