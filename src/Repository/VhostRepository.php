<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\Vhost;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class VhostRepository
 * @package App\Repository
 */
class VhostRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Vhost::class, 'v')
            ->innerJoin(
                Customer::class,
                'c',
                Join::WITH,
                'c = v.customer'
            )
        ;

        $queryBuilder
            ->groupBy('v.id');
    }

    /**
     * @return mixed
     */
    public function list()
    {
        $qb = $this
            ->createQueryBuilder('v')
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
            ->createQueryBuilder('v')
            ->where('v.id = :id')
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
            ->createQueryBuilder('v')
            ->where('v.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->groupBy('v.id')
            ->getQuery()
            ->getResult();
    }
}
