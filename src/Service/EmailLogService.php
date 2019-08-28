<?php

namespace App\Service;

use App\Entity\EmailLog;
use App\Repository\EmailLogRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class EmailLogService
 */
class EmailLogService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        /** @var EmailLogRepository $repo */
        $repo = $this->em->getRepository(EmailLog::class);

        $repo->search($queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var EmailLogRepository $repo */
        $repo = $this->em->getRepository(EmailLog::class);

        return $repo->list();
    }

    /**
     * @param $id
     * @return EmailLog|null|object
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getById($id)
    {
        /** @var EmailLogRepository $repo */
        $repo = $this->em->getRepository(EmailLog::class);

        return $repo->getOne($id);
    }
}
