<?php

namespace App\Repository;

use App\Entity\HelpCategory;
use App\Entity\HelpObject;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class HelpObjectRepository
 * @package App\Repository
 */
class HelpObjectRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(HelpObject::class, 'ho')
            ->innerJoin(
                HelpCategory::class,
                'hc',
                Join::WITH,
                'hc = ho.category'
            );

        $queryBuilder
            ->groupBy('ho.id');
    }

    /**
     * @return mixed
     */
    public function list()
    {
        $qb = $this
            ->createQueryBuilder('ho');

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
            ->createQueryBuilder('ho')
            ->where('ho.id = :id')
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
            ->createQueryBuilder('ho')
            ->where('ho.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->groupBy('ho.id')
            ->getQuery()
            ->getResult();
    }
}
