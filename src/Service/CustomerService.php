<?php

namespace App\Service;

use App\Entity\Customer;
use App\Exception\CustomerNotFoundException;
use App\Repository\CustomerRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CustomerService
 */
class CustomerService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        /** @var CustomerRepository $repo */
        $repo = $this->em->getRepository(Customer::class);

        $repo->search($queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var CustomerRepository $repo */
        $repo = $this->em->getRepository(Customer::class);

        return $repo->list();
    }

    /**
     * @param $id
     * @return Customer|null|object
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getById($id)
    {
        /** @var CustomerRepository $repo */
        $repo = $this->em->getRepository(Customer::class);

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

            $entity = new Customer();
            $entity->setDomain($params['domain']);
            $entity->setFirstName($params['first_name']);
            $entity->setLastName($params['last_name']);
            $entity->setPhone($params['phone']);
            $entity->setAddress($params['address']);
            $entity->setCsz($params['csz']);
            $entity->setEmail($params['email']);
            $entity->setOrganization($params['organization']);
            $entity->setEnableLedgerCommands((bool)$params['enable_ledger_commands']);

            $this->validate($entity, null, ['api_customer_add']);

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

            /** @var CustomerRepository $repo */
            $repo = $this->em->getRepository(Customer::class);

            /** @var Customer $entity */
            $entity = $repo->getOne($id);

            if ($entity === null) {
                throw new CustomerNotFoundException();
            }

            $entity->setDomain($params['domain']);
            $entity->setFirstName($params['first_name']);
            $entity->setLastName($params['last_name']);
            $entity->setPhone($params['phone']);
            $entity->setAddress($params['address']);
            $entity->setCsz($params['csz']);
            $entity->setEmail($params['email']);
            $entity->setOrganization($params['organization']);
            $entity->setEnableLedgerCommands((bool)$params['enable_ledger_commands']);

            $this->validate($entity, null, ['api_customer_edit']);

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

            /** @var CustomerRepository $repo */
            $repo = $this->em->getRepository(Customer::class);

            /** @var Customer $entity */
            $entity = $repo->getOne($id);

            if ($entity === null) {
                throw new CustomerNotFoundException();
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
                throw new CustomerNotFoundException();
            }

            /** @var CustomerRepository $repo */
            $repo = $this->em->getRepository(Customer::class);

            $entities = $repo->findByIds($ids);

            if (empty($entities)) {
                throw new CustomerNotFoundException();
            }

            /**
             * @var Customer $entity
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
