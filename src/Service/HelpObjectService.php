<?php

namespace App\Service;

use App\Entity\HelpCategory;
use App\Entity\HelpObject;
use App\Exception\HelpCategoryNotFoundException;
use App\Exception\HelpObjectNotFoundException;
use App\Repository\HelpCategoryRepository;
use App\Repository\HelpObjectRepository;
use DataURI\Exception\InvalidArgumentException;
use DataURI\Exception\InvalidDataException;
use DataURI\Parser;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * Class HelpObjectService
 */
class HelpObjectService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        /** @var HelpObjectRepository $repo */
        $repo = $this->em->getRepository(HelpObject::class);

        $repo->search($queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var HelpObjectRepository $repo */
        $repo = $this->em->getRepository(HelpObject::class);

        return $repo->list();
    }

    /**
     * @param $id
     * @return HelpObject|null|object
     * @throws NonUniqueResultException
     */
    public function getById($id)
    {
        /** @var HelpObjectRepository $repo */
        $repo = $this->em->getRepository(HelpObject::class);

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

            $categoryId = $params['category_id'] ?: 0;
            /** @var HelpCategoryRepository $categoryRepo */
            $categoryRepo = $this->em->getRepository(HelpCategory::class);
            /** @var HelpCategory $category */
            $category = $categoryRepo->getOne($categoryId);
            if ($category === null) {
                throw new HelpCategoryNotFoundException();
            }

            $entity = new HelpObject();
            $entity->setTitle($params['title']);
            $entity->setType($params['type']);
            $entity->setDescription($params['description']);

            $entity->setVimeoUrl($params['vimeo_url']);
            $entity->setYoutubeUrl($params['youtube_url']);

            $entity->setGrantInherit($params['grant_inherit']);
            if (!$entity->isGrantInherit()) {
                $entity->setGrants($params['grants']);
            }
            $entity->setCategory($category);

            if (!empty($params['file'])) {
                $hash = md5($entity->getId() . '_' . (new \DateTime())->format('Ymd_His'));
                $this->saveToS3($hash, $params['file']);
                $entity->setHash($hash);
            } else {
                $entity->setHash('');
            }

            $this->validate($entity, null, ['api_help_object_add']);

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

            $categoryId = $params['category_id'] ?: 0;
            /** @var HelpCategoryRepository $categoryRepo */
            $categoryRepo = $this->em->getRepository(HelpCategory::class);
            /** @var HelpCategory $category */
            $category = $categoryRepo->getOne($categoryId);
            if ($category === null) {
                throw new HelpCategoryNotFoundException();
            }

            /** @var HelpObjectRepository $repo */
            $repo = $this->em->getRepository(HelpObject::class);
            /** @var HelpObject $entity */
            $entity = $repo->getOne($id);
            if ($entity === null) {
                throw new HelpObjectNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setType($params['type']);
            $entity->setDescription($params['description']);

            $entity->setVimeoUrl($params['vimeo_url']);
            $entity->setYoutubeUrl($params['youtube_url']);

            $entity->setGrantInherit($params['grant_inherit']);
            if (!$entity->isGrantInherit()) {
                $entity->setGrants($params['grants']);
            }
            $entity->setCategory($category);

            if (!empty($params['file'])) {
                if (!empty($entity->getHash())) {
                    $this->deleteFromS3($entity->getHash());
                }
                $hash = md5($entity->getId() . '_' . (new \DateTime())->format('Ymd_His'));
                $entity->setHash($hash);
                $this->saveToS3($hash, $params['file']);
            } else {
                if (!empty($entity->getHash())) {
                    $this->deleteFromS3($entity->getHash());
                }
                $entity->setHash('');
            }

            $this->validate($entity, null, ['api_help_object_edit']);

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

            /** @var HelpObjectRepository $repo */
            $repo = $this->em->getRepository(HelpObject::class);

            /** @var HelpObject $entity */
            $entity = $repo->getOne($id);

            if ($entity === null) {
                throw new HelpObjectNotFoundException();
            }

            if (!empty($entity->getHash())) {
                $this->deleteFromS3($entity->getHash());
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
                throw new HelpObjectNotFoundException();
            }

            /** @var HelpObjectRepository $repo */
            $repo = $this->em->getRepository(HelpObject::class);

            $entities = $repo->findByIds($ids);

            if (empty($entities)) {
                throw new HelpObjectNotFoundException();
            }

            /**
             * @var HelpObject $entity
             */
            foreach ($entities as $entity) {
                if (!empty($entity->getHash())) {
                    $this->deleteFromS3($entity->getHash());
                }

                $this->em->remove($entity);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @throws InvalidDataException
     * @throws InvalidArgumentException
     */
    private function saveToS3($hash, $base64)
    {
        $parseFile = Parser::parse($base64);

        $this->s3client->putObject([
            'Bucket' => getenv('AWS_BUCKET_HELP'),
            'Key' => $hash,
            'Body' => $parseFile->getData(),
            'ContentType' => $parseFile->getMimeType()
        ]);
    }

    private function deleteFromS3($hash)
    {
        $this->s3client->deleteObject([
            'Bucket' => getenv('AWS_BUCKET_HELP'),
            'Key' => $hash
        ]);
    }
}
