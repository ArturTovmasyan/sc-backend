<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Job;
use App\Exception\CustomerNotFoundException;
use App\Exception\JobNotFoundException;
use App\Model\JobStatus;
use App\Repository\CustomerRepository;
use App\Repository\JobRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class JobService
 */
class JobService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        /** @var JobRepository $repo */
        $repo = $this->em->getRepository(Job::class);

        $repo->search($queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var JobRepository $repo */
        $repo = $this->em->getRepository(Job::class);

        return $repo->list();
    }

    /**
     * @param $id
     * @return Job|null|object
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getById($id)
    {
        /** @var JobRepository $repo */
        $repo = $this->em->getRepository(Job::class);

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

            $customerId = $params['customer_id'] ?: 0;

            /** @var CustomerRepository $customerRepo */
            $customerRepo = $this->em->getRepository(Customer::class);

            /** @var Customer $customer */
            $customer = $customerRepo->getOne($customerId);

            if ($customer === null) {
                throw new CustomerNotFoundException();
            }

            $type = $params['type'] ? (int)$params['type'] : 0;
            $log = $params['log'] ?: '';

            $entity = new Job();
            $entity->setCustomer($customer);
            $entity->setType($type);
            $entity->setStatus(JobStatus::TYPE_NOT_STARTED);
            $entity->setLog($log);

            $this->validate($entity, null, ['api_job_add']);

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

            /** @var JobRepository $repo */
            $repo = $this->em->getRepository(Job::class);

            /** @var Job $entity */
            $entity = $repo->getOne($id);

            if ($entity === null) {
                throw new JobNotFoundException();
            }

            $customerId = $params['customer_id'] ?: 0;

            /** @var CustomerRepository $customerRepo */
            $customerRepo = $this->em->getRepository(Customer::class);

            /** @var Customer $customer */
            $customer = $customerRepo->getOne($customerId);

            if ($customer === null) {
                throw new CustomerNotFoundException();
            }

            $type = $params['type'] ? (int)$params['type'] : 0;
            $log = $params['log'] ?: '';

            $entity->setCustomer($customer);
            $entity->setType($type);
            $entity->setLog($log);

            $this->validate($entity, null, ['api_job_edit']);

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

            /** @var JobRepository $repo */
            $repo = $this->em->getRepository(Job::class);

            /** @var Job $entity */
            $entity = $repo->getOne($id);

            if ($entity === null) {
                throw new JobNotFoundException();
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
                throw new JobNotFoundException();
            }

            /** @var JobRepository $repo */
            $repo = $this->em->getRepository(Job::class);

            $entities = $repo->findByIds($ids);

            if (empty($entities)) {
                throw new JobNotFoundException();
            }

            /**
             * @var Job $entity
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
