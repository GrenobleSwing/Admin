<?php

namespace GS\ApiBundle\Repository;

use GS\ApiBundle\Entity\Account;

/**
 * CertificateRepository
 */
class CertificateRepository extends \Doctrine\ORM\EntityRepository
{

    public function getValidCertificate(Account $account, string $type)
    {
        $now = new \DateTime();

        $qb = $this->createQueryBuilder('c');
        $qb
                ->where($qb->expr()->between(':date', 'c.startDate', 'c.endDate'))
                ->andWhere('account = :account')
                ->andWhere('type = :type')
                ->setParameter('date', $now, \Doctrine\DBAL\Types\Type::DATETIME)
                ->setParameter('type', $type)
                ->setParameter('account', $account)
                ;
        return $qb->getQuery()->getOneOrNullResult();
    }

}
