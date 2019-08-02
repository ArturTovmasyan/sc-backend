<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class UserService
 */
class UserService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var UserRepository $repo */
        $repo = $this->em->getRepository(User::class);

        $repo->search($queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var UserRepository $repo */
        $repo = $this->em->getRepository(User::class);

        return $repo->list();
    }

    /**
     * @param $id
     * @return User|null|object
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getById($id)
    {
        /** @var UserRepository $repo */
        $repo = $this->em->getRepository(User::class);

        return $repo->getOne($id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $entity = new User();
            $entity->setUsername($params['username']);
            $entity->setFullName($params['full_name']);
            $entity->setEnabled($params['enabled']);
            $entity->setRoles([$params['role']]);

            $entity->setPassword($params['password']);

            $this->validate($entity, null, ['api_user_add']);

            $encoded = $this->encoder->encodePassword($entity, $params['password']);
            $entity->setPassword($encoded);

            $this->em->persist($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $entity->getId();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Throwable
     */
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var UserRepository $repo */
            $repo = $this->em->getRepository(User::class);

            /** @var User $entity */
            $entity = $repo->getOne($id);

            if ($entity === null) {
                throw new UserNotFoundException();
            }

            $entity->setUsername($params['username']);
            $entity->setFullName($params['full_name']);
            $entity->setEnabled($params['enabled']);
            $entity->setRoles([$params['role']]);

            if (!empty($params['password'])) {
                $entity->setPassword($params['password']);
            }

            $this->validate($entity, null, ['api_user_edit']);

            if (!empty($params['password'])) {
                $encoded = $this->encoder->encodePassword($entity, $params['password']);
                $entity->setPassword($encoded);
            }

            $this->em->persist($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var UserRepository $repo */
            $repo = $this->em->getRepository(User::class);

            /** @var User $entity */
            $entity = $repo->getOne($id);

            if ($entity === null) {
                throw new UserNotFoundException();
            }

            $this->em->remove($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @throws \Throwable
     */
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new UserNotFoundException();
            }

            /** @var UserRepository $repo */
            $repo = $this->em->getRepository(User::class);

            $entities = $repo->findByIds($ids);

            if (empty($entities)) {
                throw new UserNotFoundException();
            }

            /**
             * @var User $entity
             */
            foreach ($entities as $entity) {
                $this->em->remove($entity);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
