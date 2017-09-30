<?php

namespace GS\StructureBundle\Repository;

use GS\StructureBundle\Entity\Account;

/**
 * CertificateRepository
 */
class CertificateRepository extends \Doctrine\ORM\EntityRepository
{

    public function getValidCertificate(Account $account, $type)
    {
        $now = new \DateTime();

        $qb = $this->createQueryBuilder('c');
        $qb
                ->where($qb->expr()->between(':date', 'c.startDate', 'c.endDate'))
                ->andWhere('c.account = :account')
                ->andWhere('c.type = :type')
                ->setParameter('date', $now, \Doctrine\DBAL\Types\Type::DATETIME)
                ->setParameter('type', $type)
                ->setParameter('account', $account)
                ;
        return $qb->getQuery()->getOneOrNullResult();
    }

}
