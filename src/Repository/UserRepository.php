<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param string $usernameOrEmail
     * @return UserInterface|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadUserByUsername($usernameOrEmail)
    {
        $result = $this->createQueryBuilder('u')
            ->where('u.username = :query')
            ->andWhere('u.enabled = :enabled')
            ->setParameter('query', $usernameOrEmail)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder) : void
    {
        $queryBuilder
            ->from(User::class, 'u')
        ;

        $queryBuilder
            ->groupBy('u.id');
    }

    /**
     * @return mixed
     */
    public function list()
    {
        $qb = $this
            ->createQueryBuilder('u')
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
            ->createQueryBuilder('u')
            ->where('u.id = :id')
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
            ->createQueryBuilder('u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
    }
}
