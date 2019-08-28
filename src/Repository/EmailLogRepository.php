<?php

namespace App\Repository;

use App\Entity\EmailLog;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class EmailLogRepository
 * @package App\Repository
 */
class EmailLogRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(EmailLog::class, 'el')
        ;

        $queryBuilder
            ->groupBy('el.id');
    }

    /**
     * @return mixed
     */
    public function list()
    {
        $qb = $this
            ->createQueryBuilder('el')
        ;

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOne($id)
    {
        $qb = $this
            ->createQueryBuilder('el')
            ->where('el.id = :id')
            ->setParameter('id', $id);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this
            ->createQueryBuilder('el')
            ->where('el.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->groupBy('el.id')
            ->getQuery()
            ->getResult();
    }
}
