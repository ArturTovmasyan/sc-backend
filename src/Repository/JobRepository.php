<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\Job;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class JobRepository
 * @package App\Repository
 */
class JobRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Job::class, 'j')
            ->innerJoin(
                Customer::class,
                'c',
                Join::WITH,
                'c = j.customer'
            )
        ;

        $queryBuilder
            ->groupBy('j.id');
    }

    /**
     * @return mixed
     */
    public function list()
    {
        $qb = $this
            ->createQueryBuilder('j')
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
            ->createQueryBuilder('j')
            ->where('j.id = :id')
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
            ->createQueryBuilder('j')
            ->where('j.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->groupBy('j.id')
            ->getQuery()
            ->getResult();
    }
}
