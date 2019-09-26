<?php

namespace App\Service;

use App\Entity\Feedback;
use App\Repository\FeedbackRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FeedbackService
 */
class FeedbackService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        /** @var FeedbackRepository $repo */
        $repo = $this->em->getRepository(Feedback::class);

        $repo->search($queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var FeedbackRepository $repo */
        $repo = $this->em->getRepository(Feedback::class);

        return $repo->list();
    }

    /**
     * @param $id
     * @return Feedback|null|object
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getById($id)
    {
        /** @var FeedbackRepository $repo */
        $repo = $this->em->getRepository(Feedback::class);

        return $repo->getOne($id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $entity = new Feedback();
            $entity->setDomain($params['domain']);
            $entity->setUsername($params['username']);
            $entity->setEmail($params['email']);
            $entity->setFullName($params['full_name']);
            $entity->setSubject($params['subject']);
            $entity->setMessage($params['message']);
            $entity->setDate($params['date']);

            $this->validate($entity, null, ['api_feedback_add']);

            $this->em->persist($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $entity->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }
}
