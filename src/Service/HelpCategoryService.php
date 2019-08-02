<?php

namespace App\Service;

use App\Entity\HelpCategory;
use App\Entity\HelpObject;
use App\Exception\HelpCategoryNotFoundException;
use App\Repository\HelpCategoryRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * Class HelpCategoryService
 */
class HelpCategoryService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        /** @var HelpCategoryRepository $repo */
        $repo = $this->em->getRepository(HelpCategory::class);

        $repo->search($queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var HelpCategoryRepository $repo */
        $repo = $this->em->getRepository(HelpCategory::class);

        return $repo->list();
    }

    /**
     * @param $id
     * @return HelpCategory|null|object
     * @throws NonUniqueResultException
     */
    public function getById($id)
    {
        /** @var HelpCategoryRepository $repo */
        $repo = $this->em->getRepository(HelpCategory::class);

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

            $entity = new HelpCategory();
            $entity->setTitle($params['title']);
            $entity->setGrantInherit($params['grant_inherit']);
            if(!$entity->isGrantInherit()) {
                $entity->setGrants($params['grants']);
            }

            if($params['parent_id']) {
                /** @var HelpCategoryRepository $repo */
                $repo = $this->em->getRepository(HelpCategory::class);
                /** @var HelpCategory $category */
                $category = $repo->getOne($params['parent_id']);
                if ($category === null) {
                    throw new HelpCategoryNotFoundException();
                }
                $entity->setParent($category);
            } else {
                $entity->setParent(null);
            }

            $this->validate($entity, null, ['api_help_category_add']);

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

            /** @var HelpCategoryRepository $repo */
            $repo = $this->em->getRepository(HelpCategory::class);
            /** @var HelpCategory $entity */
            $entity = $repo->getOne($id);
            if ($entity === null) {
                throw new HelpCategoryNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setGrantInherit($params['grant_inherit']);
            if(!$entity->isGrantInherit()) {
                $entity->setGrants($params['grants']);
            }

            if($params['parent_id']) {
                /** @var HelpCategory $category */
                $category = $repo->getOne($params['parent_id']);
                if ($category === null) {
                    throw new HelpCategoryNotFoundException();
                }
                $entity->setParent($category);
            } else {
                $entity->setParent(null);
            }

            $this->validate($entity, null, ['api_help_category_edit']);

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

            /** @var HelpCategoryRepository $repo */
            $repo = $this->em->getRepository(HelpCategory::class);

            /** @var HelpCategory $entity */
            $entity = $repo->getOne($id);

            if ($entity === null) {
                throw new HelpCategoryNotFoundException();
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
                throw new HelpCategoryNotFoundException();
            }

            /** @var HelpCategoryRepository $repo */
            $repo = $this->em->getRepository(HelpCategory::class);

            $entities = $repo->findByIds($ids);

            if (empty($entities)) {
                throw new HelpCategoryNotFoundException();
            }

            /**
             * @var HelpCategory $entity
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

    /**
     * @param $params
     * @return mixed
     */
    public function all($params)
    {
        /** @var HelpCategoryRepository $repo */
        $repo = $this->em->getRepository(HelpCategory::class);

        return $this->build_help_tree($repo->all(), $params['permissions']);
    }

    /**
     * @param HelpCategory[] $arr
     * @param array $grants
     * @return array
     */
    private function build_help_tree(array $arr, array $grants) {
        $help_tree = [];

        foreach ($arr as $category) {
            if($category->isGrantInherit() === false && count(array_diff($category->getGrants(), $grants)) !== 0) {
               continue;
            }

            $help_item = [];
            $help_item['key'] = $category->getId();
            $help_item['title'] = $category->getTitle();

            if($category->getChildren()->count() > 0) {
                $help_item['selectable'] = false;
            }

            $children = [];
            $children_tree = $this->build_help_tree($category->getChildren()->toArray(), $grants);

            if(count($children_tree) > 0) {
                array_push($children, ...$children_tree);
            }

            /** @var HelpObject $object */
            foreach ($category->getObjects() as $object) {
                if($object->isGrantInherit() === false && count(array_diff($object->getGrants(), $grants)) !== 0) {
                    continue;
                }

                $cmd = $this->s3client->getCommand('GetObject', [
                    'Bucket' => getenv('AWS_BUCKET_HELP'),
                    'Key'    => $object->getHash(),
                ]);
                $request = $this->s3client->createPresignedRequest($cmd, '+20 minutes');

                $children[] = [
                    'key' => $object->getId(),
                    'type' => $object->getType(),
                    'title' => $object->getTitle(),
                    'description' => $object->getDescription(),
                    'url' => (string)$request->getUri(),
                    'isLeaf' => true
                ];
            }

            if(count($children) > 0) {
                $help_item['children'] = $children;
                $help_tree[] = $help_item;
            }
        }

        return $help_tree;
    }
}
