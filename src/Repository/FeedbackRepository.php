<?php

namespace App\Repository;

use App\Entity\Feedback;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FeedbackRepository
 * @package App\Repository
 */
class FeedbackRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Feedback::class, 'f')
        ;

        $queryBuilder
            ->groupBy('f.id');
    }

    /**
     * @return mixed
     */
    public function list()
    {
        $qb = $this
            ->createQueryBuilder('f')
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
            ->createQueryBuilder('f')
            ->where('f.id = :id')
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
            ->createQueryBuilder('f')
            ->where('f.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->groupBy('f.id')
            ->getQuery()
            ->getResult();
    }
}
