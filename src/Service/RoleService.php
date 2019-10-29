<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\EmailLog;
use App\Entity\Role;
use App\Entity\Vhost;
use App\Exception\DomainNotFoundException;
use App\Exception\RoleNotFoundException;
use App\Exception\RoleSyncException;
use App\Exception\ValidationException;
use App\Model\Grant;
use App\Repository\RoleRepository;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\QueryBuilder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RoleService
 */
class RoleService extends BaseService implements IGridService
{
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

    private static $QUERY = [
        'SELECT' => "SELECT * FROM `%1\$s`.`tbl_role`",

        'INSERT' => "INSERT INTO `%1\$s`.`tbl_role` (`name`, `grants`) VALUES ('%3\$s', '%4\$s')",
        'UPDATE' => "UPDATE `%1\$s`.`tbl_role` SET `%1\$s`.`tbl_role`.`name`='%3\$s', `grants`='%4\$s' WHERE `%1\$s`.`tbl_role`.`id`='%2\$d'",
        'DELETE' => "DELETE FROM `%1\$s`.`tbl_role` WHERE `%1\$s`.`tbl_role`.`id`='%2\$d'",
        'DELETE_IDS' => "DELETE FROM `%1\$s`.`tbl_role` WHERE `%1\$s`.`tbl_role`.`id` IN(%2\$s)",

        'SELECT_NAME' => "SELECT * FROM `%1\$s`.`tbl_role` WHERE `%1\$s`.`tbl_role`.`name`='%2\$s'",
        'SELECT_ID' => "SELECT * FROM `%1\$s`.`tbl_role` WHERE `%1\$s`.`tbl_role`.`id`='%2\$d'",
        'SELECT_IDS' => "SELECT * FROM `%1\$s`.`tbl_role` WHERE `%1\$s`.`tbl_role`.`id` IN(%2\$s)",

        'USER_ROLE' => "SELECT `%1\$s`.`tbl_user`.`first_name`, `%1\$s`.`tbl_user`.`last_name`, `%1\$s`.`tbl_user`.`email` FROM `%1\$s`.`tbl_role`"
            ."INNER JOIN `%1\$s`.`tbl_user_role` ON `%1\$s`.`tbl_user_role`.`id_role`=`%1\$s`.`tbl_role`.`id`"
            ."INNER JOIN `%1\$s`.`tbl_user` ON `%1\$s`.`tbl_user_role`.`id_user`=`%1\$s`.`tbl_user`.`id`"
            ."WHERE `%1\$s`.`tbl_role`.`id` IN(%2\$s)"
    ];

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
     * @throws \Throwable
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $domain = $params['domain'];
            $name = $params['name'];
            $grants = $params['grants'] ?? [];

            if ($domain === null) {
                throw new DomainNotFoundException();
            }

            if ($domain === '_mc') {
                $role = new Role();
                $role->setName($params['name']);
                $role->setGrants($grants);

                $this->validate($role, null, ['api_role_add']);

                $this->em->persist($role);
                $this->em->flush();
            } else {
                $role = new Role();
                $role->setName($params['name']);
                $role->setGrants($grants);
                $this->validate($role, null, ['api_role_customer']);

                if ($this->validateRole($domain, 'add', null, $name) === false) {
                    throw new ValidationException([
                        'name' => 'This name is already in use.'
                    ]);
                }

                $this->syncCustomerRole($domain, 'INSERT', null, $params['name'], $grants);
            }

            $this->em->getConnection()->commit();

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
     * @throws \Throwable
     */
    public function edit($id, array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $domain = $params['domain'];
            $name = $params['name'];
            $grants = $params['grants'] ?? [];

            if ($domain === null) {
                throw new DomainNotFoundException();
            }

            if ($domain === '_mc') {
                /** @var Role $role */
                $role = $this->em->getRepository(Role::class)->find($id);

                if ($role === null) {
                    throw new RoleNotFoundException();
                }

                $role->setName($params['name']);
                $role->setGrants($grants);

                $this->validate($role, null, ['api_role_edit']);

                $this->em->persist($role);
                $this->em->flush();
            } else {
                $role = new Role();
                $role->setName($params['name']);
                $role->setGrants($grants);
                $this->validate($role, null, ['api_role_customer']);

                if ($this->validateRole($domain, 'edit', $id, $name) === false) {
                    throw new ValidationException([
                        'name' => 'This name is already in use.'
                    ]);
                }

                $this->syncCustomerRole($domain, 'UPDATE', $id, $name, $grants);
            }

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
    public function remove($id): void
    {
        /*** TODO: review ***/
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
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $domain
     * @param array $ids
     * @throws ConnectionException
     * @throws \Throwable
     */
    public function removeBulk($domain, array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new RoleNotFoundException();
            }

            if ($domain === null) {
                throw new DomainNotFoundException();
            }

            if ($domain === '_mc') {
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
            } else {
                $this->syncCustomerRole($domain, 'DELETE_IDS', implode(', ', $ids), null, null);
            }

            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param GrantService $grantService
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getRoles(GrantService $grantService): array
    {
        $roles = [];

        $roles['_mc'] = $this->em->getRepository(Role::class)->findAll();

        $query = sprintf(self::$QUERY['SELECT'], 'db_seniorcare_scpp');
        $stmt = $this->em->getConnection()->query($query);
        $roles['scpp.seniorcaresw.com']
            = $stmt->fetchAll(FetchMode::CUSTOM_OBJECT, Role::class);

        $vhosts = $this->em->getRepository(Vhost::class)->findAll();
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

            $roles[$vhost->getCustomer()->getDomain()]
                = $stmt->fetchAll(FetchMode::CUSTOM_OBJECT, Role::class);
        }

        foreach ($roles as $key => $domain_roles) {
            /** @var Role $role */
            foreach ($domain_roles as $role) {
                $role->setGrants($grantService->getGrants($role->getGrants()));
            }
        }

        return $roles;
    }

    /**
     * @param $domain
     * @param $action
     * @param $id
     * @param $name
     * @param $grants
     * @throws \Doctrine\DBAL\DBALException
     */
    private function syncCustomerRole($domain, $action, $id, $name, $grants)
    {
        $db_name = $this->getDomainDatabase($domain);

        if ($db_name !== null) {
            $query = sprintf(self::$QUERY[$action], $db_name, $id, $name, json_encode($grants));
//            dd($query);

            $stmt = $this->em->getConnection()->prepare($query);
            $stmt->execute();
        }
    }

    /**
     * @param $domain
     * @param $action
     * @param $id
     * @param $name
     * @param null $ids
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    private function validateRole($domain, $action, $id, $name, $ids = null)
    {
        $db_name = $this->getDomainDatabase($domain);

        if ($db_name !== null) {
            switch ($action) {
                case 'delete':
                    $query = sprintf(self::$QUERY['SELECT_IDS'], $db_name, implode(', ', $ids));
                    $stmt = $this->em->getConnection()->prepare($query);
                    $stmt->execute();

                    $result = $stmt->fetchAll();

                    if (count($result) === 0) {
                        return true;
                    }
                    break;
                case 'add':
                    $query = sprintf(self::$QUERY['SELECT_NAME'], $db_name, $name);
                    $stmt = $this->em->getConnection()->prepare($query);
                    $stmt->execute();

                    $result = $stmt->fetchAll();

                    if (count($result) === 0) {
                        return true;
                    }
                    break;
                case 'edit':
                    $query = sprintf(self::$QUERY['SELECT_ID'], $db_name, $id);
                    $stmt = $this->em->getConnection()->prepare($query);
                    $stmt->execute();
                    $result_1 = $stmt->fetchAll();

                    $query = sprintf(self::$QUERY['SELECT_NAME'], $db_name, $name);
                    $stmt = $this->em->getConnection()->prepare($query);
                    $stmt->execute();

                    $result_2 = $stmt->fetchAll();

                    if (count($result_1) === 1 && count($result_2) === 0) {
                        return true;
                    }
                    if (count($result_1) === 1 && count($result_2) === 1 && $result_1[0]['id'] === $result_2[0]['id']) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * @param GrantService $grantService
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportExcel(GrantService $grantService)
    {
        $roles = $this->getRoles($grantService);
        $files = [];

        foreach ($roles as $domain => $domain_roles) {
            $spreadsheet = new Spreadsheet();

            /** @var Role $role */
            foreach($domain_roles as $role) {
                $domain = $domain === '_mc' ? '(new customer)' : $domain;

                $sheet = new Worksheet(null, $role->getName());
                $spreadsheet->addSheet($sheet);

                $sheet->setCellValue('A1', 'Group');
                $sheet->setCellValue('B1', 'Title');
                $sheet->setCellValue('C1', 'Enabled');
                $sheet->setCellValue('D1', 'Level');
                $sheet->setCellValue('E1', 'Identity');

                $i = 2;

                $medium_rows = [];

                foreach ($role->getGrants() as $grant) {
                    $group = $grant['title'];
                    $flatten = GrantService::grantFlatten($grant['children']);

                    foreach ($flatten as $grant_flat) {
                        $sheet->setCellValue(sprintf('A%d', $i), $group);
                        $sheet->setCellValue(sprintf('B%d', $i), $grant_flat['title']);
                        $sheet->setCellValue(sprintf('C%d', $i), $grant_flat['enabled'] ? 'Yes' : 'No');
                        if(array_key_exists('level', $grant_flat)) {
                            $sheet->setCellValue(sprintf('D%d', $i), Grant::level2str($grant_flat['level']));
                        }
                        if(array_key_exists('identity', $grant_flat)) {
                            $sheet->setCellValue(sprintf('E%d', $i), Grant::identity2str($grant_flat['identity']));
                        }

                        $i++;
                    }

                    $medium_rows[] = $i - 1;
                }

                $sheet->getStyle('A1:E1')->getFont()->setBold(true);

                $sheet->getStyle('A1:E1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->setStartColor(new Color('00DADFE8'))->setEndColor(new Color('00DADFE8'));

                $sheet->getStyle('A1:E1')->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM)
                    ->setColor(new Color('00DADFE8'));

                $sheet->getStyle(sprintf('A1:E%d', $i - 1))->getBorders()->getOutline()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM)
                    ->setColor(new Color('00DADFE8'));

                $sheet->getStyle(sprintf('A2:E%d', $i - 1))->getBorders()->getInside()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->setColor(new Color('00DADFE8'));

                foreach ($medium_rows as $medium_row) {
                    $sheet->getStyle(sprintf('A%1$d:E%1$d', $medium_row))->getBorders()->getBottom()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM)
                        ->setColor(new Color('00DADFE8'));
                }

                $sheet->getStyle(sprintf('A1:E%d', $i - 1))->getAlignment()
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle(sprintf('B1:B%d', $i - 1))->getAlignment()
                    ->setWrapText(true);

                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(50);
                $sheet->getColumnDimension('C')->setWidth(8);
                $sheet->getColumnDimension('D')->setWidth(10);
                $sheet->getColumnDimension('E')->setWidth(8);

                $sheet->insertNewColumnBefore('A');
                $sheet->setSelectedCell('A1');
            }

            $spreadsheet->removeSheetByIndex(0);

            $files[$domain] = sprintf('%s/var/tmp/%s',
                $this->container->get('kernel')->getProjectDir(),
                md5((new \DateTime())->format('Ymd_His'))
            );

            $writer = new Xlsx($spreadsheet);
            $writer->save($files[$domain]);
        }

        $zip = new \ZipArchive();

        $zipName = sprintf('%s/var/tmp/seniorcaresw_roles.zip', $this->container->get('kernel')->getProjectDir());
        $zip->open($zipName,  \ZipArchive::CREATE);

        foreach ($files as $domain => $file) {
            $zip->addFromString(sprintf('%s.xlsx', $domain),  file_get_contents($file));
        }
        $zip->close();

        $response = new Response(file_get_contents($zipName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename=seniorcaresw_roles.zip');
        $response->headers->set('Content-length', filesize($zipName));

        $files['zip'] = $zipName;
        foreach ($files as $domain => $file) {
            @unlink($file);
        }

        return $response;
    }

    /**
     * @param array $params
     * @throws \Throwable
     */
    public function sync(array $params)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $domain = $params['domain'];
            $id = $params['id'];
            $domains = $params['domains'];

            $domain_db_name = $this->getDomainDatabase($domain);

            if ($domain === null || $domain_db_name === null) {
                throw new DomainNotFoundException();
            }

            if(empty($domains)) {
                throw new ValidationException(['domains' => 'This value should not be blank.']);
            }

            if ($id === null) {
                throw new RoleNotFoundException();
            }

            $query = sprintf(self::$QUERY['SELECT_ID'], $domain_db_name, $id);
            $stmt = $this->em->getConnection()->prepare($query);
            $stmt->execute();

            /** @var Role[] $role */
            $roles = $stmt->fetchAll(FetchMode::CUSTOM_OBJECT, Role::class);

            if (empty($roles)) {
                throw new RoleNotFoundException();
            }

            $role = $roles[0];

            foreach ($domains as $cdomain) {
                $db_name = $this->getDomainDatabase($cdomain);

                if($db_name === null) {
                    throw new DomainNotFoundException();
                }

                $query = sprintf(self::$QUERY['SELECT_NAME'], $db_name, $role->getName());
                $stmt = $this->em->getConnection()->query($query);
                $old_role = $stmt->fetch(FetchMode::ASSOCIATIVE);

                if($old_role !== false) {
                    $this->syncCustomerRole($cdomain, 'UPDATE', $old_role['id'], $old_role['name'], $role->getGrants());
                } else {
                    $this->syncCustomerRole($cdomain, 'INSERT', null, $role->getName(), $role->getGrants());
                }
            }

            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();
            throw new RoleSyncException($e->getMessage());
        }
    }

    private function getDomainDatabase($domain) {
        $db_name = null;

        if($domain === '_mc') {
            $db_name = 'db_seniorcare_mc';
        } elseif ($domain === "scpp.seniorcaresw.com") {
            $db_name = "db_seniorcare_scpp";
        } else {
            /** @var Customer $customer */
            $customer = $this->em->getRepository(Customer::class)
                ->findOneBy(['domain' => $domain]);

            if ($customer === null) {
                throw new DomainNotFoundException();
            }

            $db_name = $customer->getVhost()->getDbName();
        }

        return $db_name;
    }

    /**
     * @param array $params
     * @throws \Throwable
     */
    public function email(array $params)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $domain = $params['domain'];
            $roleIds = $params['roles'];
            $subject = $params['subject'];
            $message = $params['message'];
            $cc = $params['cc'];

            $domain_db_name = $this->getDomainDatabase($domain);

            if ($domain === null || $domain_db_name === null) {
                throw new DomainNotFoundException();
            }

            if(empty($subject)) {
                throw new ValidationException(['subject' => 'This value should not be blank.']);
            }

            if(empty($message)) {
                throw new ValidationException(['message' => 'This value should not be blank.']);
            }

            $query = sprintf(self::$QUERY['SELECT_IDS'], $domain_db_name, implode(',', $roleIds));
            $stmt = $this->em->getConnection()->prepare($query);
            $stmt->execute();

            $roles = $stmt->fetchAll();

            $role_names = [];

            foreach ($roles as $role) {
                $role_names[] = $role['name'];
            }


            $query = sprintf(self::$QUERY['USER_ROLE'], $domain_db_name, implode(',', $roleIds));
            $stmt = $this->em->getConnection()->prepare($query);
            $stmt->execute();

            $users = $stmt->fetchAll(FetchMode::ASSOCIATIVE);

            $emails = [];

            foreach ($users as $user) {
                $emails[] = $user['email'];
            }

            if(!empty($cc)) {
                $constraint = new Assert\All([
                   new Assert\Email()
                ]);
                $violations = $this->validator->validate($cc, $constraint);

                if(count($violations) > 0) {
                    throw new ValidationException(['cc' => 'This field contains invalid email address(es).']);
                }

                $emails = array_merge($emails, $cc);
            }

            $emailMessage = (new \Swift_Message($subject))
                ->setFrom('support@seniorcaresw.com')
                ->setBcc($emails)
                ->setBody($message)
            ;

            $emailLog = new EmailLog();
            $emailLog->setDomain($domain);
            $emailLog->setSubject($subject);
            $emailLog->setMessage($message);
            $emailLog->setEmails(implode(', ', $emails));
            $emailLog->setRoles(implode(', ', $role_names));
            $emailLog->setDate(new \DateTime());
            $emailLog->setStatus($this->container->get('mailer')->send($emailMessage));

            $this->em->persist($emailLog);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();
            if($e instanceof ValidationException) {
                throw $e;
            } else {
                throw new RoleSyncException($e->getMessage());
            }
        }
    }
}
