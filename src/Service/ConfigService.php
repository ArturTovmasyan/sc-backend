<?php

namespace App\Service;

use App\Entity\Config;
use App\Exception\ConfigNotFoundException;
use App\Repository\ConfigRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ConfigService
 */
class ConfigService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        /** @var ConfigRepository $repo */
        $repo = $this->em->getRepository(Config::class);

        $repo->search($queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var ConfigRepository $repo */
        $repo = $this->em->getRepository(Config::class);

        return $repo->list();
    }

    /**
     * @param $id
     * @return Config|null|object
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getById($id)
    {
        /** @var ConfigRepository $repo */
        $repo = $this->em->getRepository(Config::class);

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

            $entity = new Config();
            $entity->setName($params['name']);
            $entity->setValue($params['value']);

            $this->validate($entity, null, ['api_config_add']);

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

            /** @var ConfigRepository $repo */
            $repo = $this->em->getRepository(Config::class);

            /** @var Config $entity */
            $entity = $repo->getOne($id);

            if ($entity === null) {
                throw new ConfigNotFoundException();
            }

            $entity->setName($params['name']);
            $entity->setValue($params['value']);

            $this->validate($entity, null, ['api_config_edit']);

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

            /** @var ConfigRepository $repo */
            $repo = $this->em->getRepository(Config::class);

            /** @var Config $entity */
            $entity = $repo->getOne($id);

            if ($entity === null) {
                throw new ConfigNotFoundException();
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
                throw new ConfigNotFoundException();
            }

            /** @var ConfigRepository $repo */
            $repo = $this->em->getRepository(Config::class);

            $entities = $repo->findByIds($ids);

            if (empty($entities)) {
                throw new ConfigNotFoundException();
            }

            /**
             * @var Config $entity
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

    public function assoc()
    {
        $configs = $this->list([]);
        $response = [];

        /** @var Config $config */
        foreach ($configs as $config) {
            $response[$config->getName()] = $config->getValue();
        }

        return $response;
    }
}
