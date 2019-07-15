<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RoleRepository
 * @package App\Repository
 */
class RoleRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Role::class, 'r');

        $queryBuilder
            ->groupBy('r.id');
    }

    /**
     * @return mixed
     */
    public function list()
    {
        $qb = $this
            ->createQueryBuilder('r');

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
            ->createQueryBuilder('r')
            ->where('r.id = :id')
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
            ->createQueryBuilder('r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }
}
