<?php

namespace App\Command;

use App\Entity\Vhost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AccountMonitorCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:monitor:account';

    private static $TITLE = [
        'DATABASE_SIZE' => '1. Database Size',
        'LICENSE_CAPACITY' => '2. License Capacity',
        'RESIDENT_COUNT' => '3. Total Resident Count',
        'FACILITY_COUNT' => '4. Facility/Resident Count',
        'FRESIDENT_COUNT' => '5. Resident Per Facility',
        'USER_COUNT' => '6. User(s) count',
        'LAST_LOGIN_COUNT' => '7. Last Logged User',
        'LOGIN_FAILURE' => '8. Login failure(s)'
    ];

    private static $QUERY = [
        'DATABASE_SIZE' => 'SELECT CONCAT(SUM(ROUND(((data_length + index_length) / 1024 / 1024), 2)), \' MB\') AS `size`
                                           FROM `information_schema`.`TABLES`
                                           WHERE `table_schema`="%1$s" ORDER BY (data_length + index_length) DESC',

        'LICENSE_CAPACITY' => 'SELECT "Not yet implemented."',

        'USER_COUNT' => 'SELECT COUNT(*) AS `count` FROM %1$s.`tbl_user`',
        'LAST_LOGIN_COUNT' => 'SELECT
                               CONCAT(%1$s.`tbl_user`.`first_name`, \' \', %1$s.`tbl_user`.`last_name`,
                                 \' (\', %1$s.`tbl_user`.`email` ,\')\n\',
                                 %1$s.`tbl_user`.`last_activity_at`
                                 ) AS `user`
                               FROM %1$s.`tbl_user` ORDER BY %1$s.`tbl_user`.`last_activity_at` DESC LIMIT 1',

        'FACILITY_COUNT' => 'SELECT
                               CONCAT(
                                 COUNT(*),
                                 \'/\',
                                 (SELECT
                                   COUNT(%1$s.`tbl_resident_admission`.`id`)
                                 FROM
                                   %1$s.`tbl_resident_admission`
                                 WHERE (
                                     %1$s.`tbl_resident_admission`.`admission_type` < 4
                                     AND %1$s.`tbl_resident_admission`.`end` IS NULL
                                   )
                                   AND %1$s.`tbl_resident_admission`.`group_type` = 1)
                               ) AS `count`
                             FROM
                               %1$s.`tbl_facility`',

        'FRESIDENT_COUNT' => 'SELECT
                                CONCAT(
                                  %1$s.`tbl_facility`.`name`,
                                  \' (\',
                                  COUNT(%1$s.`tbl_resident_admission`.`id`),
                                  \')\n\'
                                ) AS `f_info`
                              FROM
                                %1$s.`tbl_resident_admission`
                                INNER JOIN %1$s.`tbl_facility_bed`
                                  ON %1$s.`tbl_facility_bed`.`id` = %1$s.`tbl_resident_admission`.`id_facility_bed`
                                INNER JOIN %1$s.`tbl_facility_room`
                                  ON %1$s.`tbl_facility_room`.`id` = %1$s.`tbl_facility_bed`.`id_facility_room`
                                INNER JOIN %1$s.`tbl_facility`
                                  ON %1$s.`tbl_facility`.`id` = %1$s.`tbl_facility_room`.`id_facility`
                              WHERE (
                                  %1$s.`tbl_resident_admission`.`admission_type` < 4
                                  AND %1$s.`tbl_resident_admission`.`end` IS NULL
                                )
                                AND %1$s.`tbl_resident_admission`.`group_type` = 1
                              GROUP BY %1$s.`tbl_facility`.`name`',

        'RESIDENT_COUNT' => 'SELECT COUNT(%1$s.`tbl_resident`.`id`) FROM %1$s.`tbl_resident`'
    ];

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(ContainerInterface $container, $name = null)
    {
        $this->em = $container->get('doctrine')->getManager();

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setHelp('Job for monitor account usage.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $vhosts = $this->em->getRepository(Vhost::class)->findAll();//By(['enabled' => true]);

        try {
            /** @var Vhost $vhost */
            foreach ($vhosts as $vhost) {
                $customer = $vhost->getCustomer();
                $db_name = $vhost->getDbName();

                $info = [];
                foreach (self::$QUERY as $key => $value) {
                    if ($value !== "") {
                        var_dump($value);
                        $stmt = $this->em->getConnection()->prepare(sprintf($value, $db_name));
                        $stmt->execute();

                        if($key === 'FRESIDENT_COUNT') {
                            $result = $stmt->fetchAll();

                            foreach ($result as &$record) {
                                $record = reset($record);
                            }
                        } else {
                            $result = $stmt->fetch();
                        }
                        $info[self::$TITLE[$key]] = count($result) === 1 ? reset($result) : implode('', $result);
                    }
                }

                var_dump($info);

                $customer->setInfo([$info]);

                $this->em->persist($customer);
            }
            $this->em->flush();
        } catch (\Throwable $e) {
            dump($e->getMessage());
            dump($e->getTraceAsString());
        }

        $this->release();

        return 0;
    }

}
