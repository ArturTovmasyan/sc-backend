<?php

namespace App\Service;

use App\Entity\Vhost;
use App\Exception\VhostNotFoundException;
use App\Repository\VhostRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class VhostService
 */
class VhostService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        /** @var VhostRepository $repo */
        $repo = $this->em->getRepository(Vhost::class);

        $repo->search($queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var VhostRepository $repo */
        $repo = $this->em->getRepository(Vhost::class);

        return $repo->list();
    }

    /**
     * @param $id
     * @return Vhost|null|object
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getById($id)
    {
        /** @var VhostRepository $repo */
        $repo = $this->em->getRepository(Vhost::class);

        return $repo->getOne($id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params)
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $entity = new Vhost();
            $entity->setName($params['name']);
            $entity->setWwwRoot($params['www_root']);
            $entity->setDbHost($params['db_host']);
            $entity->setDbName($params['db_name']);
            $entity->setDbUser($params['db_user']);
            $entity->setDbPassword($params['db_password']);
            $entity->setMailerHost($params['mailer_host']);
            $entity->setMailerProto($params['mailer_proto']);
            $entity->setMailerUser($params['mailer_user']);
            $entity->setMailerPassword($params['mailer_password']);

            $this->validate($entity, null, ['api_vhost_add']);

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
    public function edit($id, array $params)
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var VhostRepository $repo */
            $repo = $this->em->getRepository(Vhost::class);

            /** @var Vhost $entity */
            $entity = $repo->getOne($id);

            if ($entity === null) {
                throw new VhostNotFoundException();
            }

            $entity->setName($params['name']);
            $entity->setWwwRoot($params['www_root']);
            $entity->setDbHost($params['db_host']);
            $entity->setDbName($params['db_name']);
            $entity->setDbUser($params['db_user']);
            $entity->setDbPassword($params['db_password']);
            $entity->setMailerHost($params['mailer_host']);
            $entity->setMailerProto($params['mailer_proto']);
            $entity->setMailerUser($params['mailer_user']);
            $entity->setMailerPassword($params['mailer_password']);

            $this->validate($entity, null, ['api_vhost_edit']);

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

            /** @var VhostRepository $repo */
            $repo = $this->em->getRepository(Vhost::class);

            /** @var Vhost $entity */
            $entity = $repo->getOne($id);

            if ($entity === null) {
                throw new VhostNotFoundException();
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
                throw new VhostNotFoundException();
            }

            /** @var VhostRepository $repo */
            $repo = $this->em->getRepository(Vhost::class);

            $entities = $repo->findByIds($ids);

            if (empty($entities)) {
                throw new VhostNotFoundException();
            }

            /**
             * @var Vhost $entity
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
