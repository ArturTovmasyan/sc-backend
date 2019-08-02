<?php

namespace App\Repository;

use App\Entity\HelpCategory;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class HelpCategoryRepository
 * @package App\Repository
 */
class HelpCategoryRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(HelpCategory::class, 'hc')
            ->leftJoin(
                HelpCategory::class,
                'hcp',
                Join::WITH,
                'hcp = hc.parent'
            );

        $queryBuilder
            ->groupBy('hc.id');
    }

    /**
     * @return mixed
     */
    public function list()
    {
        $qb = $this
            ->createQueryBuilder('hc')
            ->where('hc.parent IS NULL');
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
            ->createQueryBuilder('hc')
            ->where('hc.id = :id')
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
            ->createQueryBuilder('hc')
            ->where('hc.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->groupBy('hc.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function all()
    {
        $qb = $this
            ->createQueryBuilder('hc')
            ->where('hc.parent IS NULL');
        ;

        return $qb
            ->getQuery()
            ->getResult();
    }
}
