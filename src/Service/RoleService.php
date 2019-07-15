<?php

namespace App\Service;

use App\Entity\Role;
use App\Entity\Vhost;
use App\Exception\RoleNotFoundException;
use App\Exception\RoleSyncException;
use App\Repository\RoleRepository;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RoleService
 */
class RoleService extends BaseService implements IGridService
{
    private static $QUERY = [
        'SELECT' => "SELECT * FROM `%1\$s`.`tbl_role`",
        'INSERT' => "INSERT INTO `%1\$s`.`tbl_role` (`name`, `grants`) VALUES ('%2\$s', '%3\$s')",
        'UPDATE' => "UPDATE `%1\$s`.`tbl_role` SET `%1\$s`.`tbl_role`.`grants`='%3\$s' WHERE `%1\$s`.`tbl_role`.`name`='%2\$s'",
        'DELETE' => "DELETE FROM `%1\$s`.`tbl_role` WHERE `%1\$s`.`tbl_role`.`name`='%2\$s'"
    ];

    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var RoleRepository $repo */
        $repo = $this->em->getRepository(Role::class);

        $repo->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Role::class)->findAll();
    }

    /**
     * @param $id
     * @param GrantService $grantService
     * @return Role
     */
    public function getById($id, GrantService $grantService): ?Role
    {
        /** @var Role $role */
        $role = $this->em->getRepository(Role::class)->find($id);

        if ($role) {
            $role->setGrants($grantService->getGrants($role->getGrants()));
        }

        return $role;
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $role = new Role();
            $role->setName($params['name'] ?? '');
            $role->setGrants($params['grants'] ?? []);

            $this->validate($role, null, ['api_role_add']);

            $this->em->persist($role);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $this->syncRoles();

            $insert_id = $role->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Exception
     */
    public function edit($id, array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Role $role */
            $role = $this->em->getRepository(Role::class)->find($id);

            if ($role === null) {
                throw new RoleNotFoundException();
            }

            $role->setName($params['name'] ?? '');
            $role->setGrants($params['grants'] ?? []);

            $this->validate($role, null, ['api_role_edit']);

            $this->em->persist($role);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $this->syncRoles();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /**
             * @var Role $role
             */
            $role = $this->em->getRepository(Role::class)->find($id);

            if ($role === null) {
                throw new RoleNotFoundException();
            }

            $this->em->remove($role);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $this->syncRoles();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new RoleNotFoundException();
            }

            /** @var RoleRepository $repo */
            $repo = $this->em->getRepository(Role::class);

            $entities = $repo->findByIds($ids);

            if (empty($entities)) {
                throw new RoleNotFoundException();
            }

            /**
             * @var Role $entity
             */
            foreach ($entities as $entity) {
                $this->em->remove($entity);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();

            $this->syncRoles();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    private function syncRoles()
    {
        try {
            $vhosts = $this->em->getRepository(Vhost::class)->findAll();
            $roles = $this->em->getRepository(Role::class)->findAll();

            /** @var Vhost $vhost */
            foreach ($vhosts as $vhost) {
                $db = [
                    'host' => $vhost->getDbHost(),
                    'name' => $vhost->getDbName(),
                    'user' => $vhost->getDbUser(),
                    'pass' => $vhost->getDbPassword(),
                ];

                $query = sprintf(self::$QUERY['SELECT'], $db['name']);
                $stmt = $this->em->getConnection()->query($query);
                $old_roles = $stmt->fetchAll(FetchMode::ASSOCIATIVE);

                foreach ($old_roles as $key => $old_role) {
                    $f_roles = array_filter($roles, function (Role $value) use ($old_role) {
                        return $old_role['name'] === $value->getName();
                    });

                    if (count($f_roles) > 0) {
                        $old_roles[$key]['cmd'] = "UPDATE";
                        $old_roles[$key]['new_role'] = reset($f_roles);
                    } else {
                        $old_roles[$key]['cmd'] = "DELETE";
                    }
                }
                /** @var Role $role */
                foreach ($roles as $role) {
                    $old_role = array_filter($old_roles, function ($value) use ($role) {
                        return $value['name'] === $role->getName();
                    });

                    if (count($old_role) == 0) {
                        $old_role = [];
                        $old_role['name'] = $role->getName();
                        $old_role['cmd'] = "INSERT";
                        $old_role['new_role'] = $role;

                        $old_roles[] = $old_role;
                    }
                }

                foreach ($old_roles as $old_role) {
                    switch ($old_role['cmd']) {
                        case 'INSERT':
                            $query = sprintf(
                                self::$QUERY['INSERT'],
                                $db['name'],
                                $old_role['new_role']->getName(),
                                json_encode($old_role['new_role']->getGrants())
                            );
                            break;
                        case 'DELETE':
                            $query = sprintf(
                                self::$QUERY['DELETE'],
                                $db['name'],
                                $old_role['name']
                            );
                            break;
                        case 'UPDATE':
                            $query = sprintf(
                                self::$QUERY['UPDATE'],
                                $db['name'],
                                $old_role['new_role']->getName(),
                                json_encode($old_role['new_role']->getGrants())
                            );
                            break;
                    }
                    $stmt = $this->em->getConnection()->prepare($query);
                    $stmt->execute();
                }
            }
        } catch (\Throwable $e) {
            throw new RoleSyncException($e->getMessage());
        }
    }
}
