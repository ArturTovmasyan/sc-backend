<?php

namespace App\Repository;

use App\Entity\Config;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ConfigRepository
 * @package App\Repository
 */
class ConfigRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Config::class, 'c')
        ;

        $queryBuilder
            ->groupBy('c.id');
    }

    /**
     * @return mixed
     */
    public function list()
    {
        $qb = $this
            ->createQueryBuilder('c')
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
            ->createQueryBuilder('c')
            ->where('c.id = :id')
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
            ->createQueryBuilder('c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }
}
