<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TestDuplicateCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:test:duplicate';

    private static $QUERY = [
        'DUPLICATES' => 'SELECT
  `%1$s`.`tbl_facility`.`name` AS `facility`,
  `%1$s`.`tbl_facility_bed`.`id_facility_room` AS `room`,
  GROUP_CONCAT(`%1$s`.`tbl_facility_bed`.`id` ORDER BY `%1$s`.`tbl_facility_bed`.`id` ASC) AS `beds`
FROM `%1$s`.`tbl_facility_bed`
  INNER JOIN `%1$s`.`tbl_facility_room` ON `%1$s`.`tbl_facility_room`.`id` = `%1$s`.`tbl_facility_bed`.`id_facility_room`
  INNER JOIN `%1$s`.`tbl_facility` ON `%1$s`.`tbl_facility`.`id` = `%1$s`.`tbl_facility_room`.`id_facility`
GROUP BY `%1$s`.`tbl_facility_bed`.`id_facility_room`, `%1$s`.`tbl_facility_bed`.`number`
HAVING COUNT(*) > 1 ORDER BY `facility`, `room`',

        'ADMISSIONS' => 'SELECT * FROM `%1$s`.`tbl_resident_admission`
WHERE `%1$s`.`tbl_resident_admission`.`id_facility_bed` IN (%2$s)',

        'ADMISSION_COUNT' => 'SELECT COUNT(*) as adm_count FROM `%1$s`.`tbl_resident_admission`
WHERE `%1$s`.`tbl_resident_admission`.`id_facility_bed`=%2$s',

        'ADMISSION_RESIDENT' => 'SELECT DISTINCT(`%1$s`.`tbl_resident_admission`.`id_resident`) FROM `%1$s`.`tbl_resident_admission`
WHERE `%1$s`.`tbl_resident_admission`.`id_facility_bed`=%2$s',

        'RESIDENT_LAST' => 'SELECT `%1$s`.`tbl_resident_admission`.`id_facility_bed`, `%1$s`.`tbl_resident_admission`.`admission_type` FROM `%1$s`.`tbl_resident_admission`
WHERE `%1$s`.`tbl_resident_admission`.`id_resident`=%2$s AND `%1$s`.`tbl_resident_admission`.`end` IS NULL
 ORDER BY `%1$s`.`tbl_resident_admission`.`start` DESC LIMIT 1',

        'UPDATE_NAME' => 'UPDATE `%1$s`.`tbl_facility_bed` SET `%1$s`.`tbl_facility_bed`.`number`=CONCAT(\'ZZZZ-\', `%1$s`.`tbl_facility_bed`.`number`) WHERE `%1$s`.`tbl_facility_bed`.`id`=%2$s',
        'UPDATE_OTHER' => 'UPDATE `%1$s`.`tbl_resident_admission` SET `%1$s`.`tbl_resident_admission`.`id_facility_bed`=%2$s WHERE `%1$s`.`tbl_resident_admission`.`id_facility_bed`=%3$s AND `%1$s`.`tbl_resident_admission`.`id_resident`=%4$s',
        'DELETE_UNUSED' => 'DELETE FROM `%1$s`.`tbl_facility_bed` WHERE `%1$s`.`tbl_facility_bed`.`id` IN (%2$s);'
    ];

    private $database_name = 'sc_0015_db';

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
            ->addOption('op', null, InputOption::VALUE_REQUIRED, 'Operation.')
            ->setHelp('Job for getting duplicate beds.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        try {
            $stmt = $this->em->getConnection()
                ->prepare(sprintf(self::$QUERY['DUPLICATES'], $this->database_name));
            $stmt->execute();
            $initial_results = $stmt->fetchAll();

            switch ($input->getOption('op')) {
                case 'active':
                    $results = $this->getBedAdmissionCount($initial_results);
                    $results = $this->getBedAdmissionResident($results);
                    $queries = $this->processAcitveBedDuplicate($results);
                    dump($queries);
                    $this->queryExecute($queries);
                    break;
                case 'other':
                    $results = $this->getBedAdmissionCount($initial_results);
                    $results = $this->getBedAdmissionResident($results);
                    $queries = $this->processOtherBedDuplicate($results);
                    dump($queries);
                    $this->queryExecute($queries);
                    break;
                case 'unused':
                    $results = $this->getBedAdmissionCount($initial_results);
                    $results = $this->getBedAdmissionResident($results);
                    $queries = $this->processUnusedBedDuplicated($results);
                    dump($queries);
                    $this->queryExecute($queries);
                    break;

            }
            die();
        } catch (\Throwable $e) {
            dump($e->getMessage());
            dump($e->getTraceAsString());
        }

        $this->release();

        return 0;
    }

    /**
     * @param array $results
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function processAcitveBedDuplicate(array $results)
    {
        $query = [];

        // Get active bed admissions
        foreach ($results as &$result) {
            $bed_active_residents = [];
            foreach ($result['bed_resident'] as $bed => $residents) {
                $bed_active_residents[$bed] = [];
                foreach ($residents as $resident) {
                    $stmt = $this->em->getConnection()
                        ->prepare(sprintf(self::$QUERY['RESIDENT_LAST'], $this->database_name, $resident, $bed));
                    $stmt->execute();
                    $status = $stmt->fetch();
                    if($status['admission_type'] !== "4" && $bed == $status['id_facility_bed']) {
                        $bed_active_residents[$bed][] = $resident;
                    }
                }
            }
            $result['bed_active_resident'] = array_filter($bed_active_residents, function($value) {
                return count($value) > 0;
            });
        }

        $results = array_filter($results, function ($value) {
            return count($value['bed_active_resident']) > 1;
        });

        $results = array_map(function($value) {
            return $value['bed_active_resident'];
        }, $results);

        foreach ($results as $result) {
            $keys = array_keys($result);
            foreach (range(1, count($keys)-1) as $i) {
                $query[] = sprintf(self::$QUERY['UPDATE_NAME'], $this->database_name, $keys[$i]);
            }
        }

        return $query;
    }

    /**
     * @param array $results
     * @return array
     */
    private function processOtherBedDuplicate(array $results)
    {
        $query = [];

        $results = array_filter($results, function ($value) {
            return count($value['bed_resident']) > 1;
        });

        $results = array_map(function($value) {
            return $value['bed_resident'];
        }, $results);

        foreach($results as $result) {
            $new_bed = $this->array_key_first($result);

            foreach($result as $key => $bed_value) {
                if($key !== $new_bed) {
                    foreach($bed_value as $bed_resident) {
                        $query[] = sprintf(self::$QUERY['UPDATE_OTHER'], $this->database_name, $new_bed, $key, $bed_resident);
                    }
                }
            }
        }

        return $query;
    }

    /**
     * @param array $results
     * @return array
     */
    private function processUnusedBedDuplicated(array $results) {
        // Check if bed has no any admission add to delete list
        $beds_to_delete = [];
        foreach ($results as &$result) {
            foreach ($result['bed_admission'] as $bed => $value) {
                if(intval($value) === 0) {
                    $beds_to_delete[] = $bed;
                }
            }

            $result['bed_admission'] = array_filter($result['bed_admission'], function ($value) {
                return intval($value) !== 0;
            });
        }
        return [sprintf(self::$QUERY['DELETE_UNUSED'], $this->database_name, implode(', ', $beds_to_delete))];
    }

    /**
     * @param array $results
     * @throws \Doctrine\DBAL\DBALException
     * @return array
     */
    private function getBedAdmissionCount(array $results) {
        // Get bed admissions
        foreach ($results as $key => $result) {
            $beds = explode(',', $result['beds']);

            foreach ($beds as $bed) {
                $stmt = $this->em->getConnection()
                    ->prepare(sprintf(self::$QUERY['ADMISSION_COUNT'], $this->database_name, $bed));
                $stmt->execute();
                $results[$key]['bed_admission'][$bed] = $stmt->fetchColumn(0);
            }
        }

        return $results;
    }

    /**
     * @param array $results
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getBedAdmissionResident(array $results)
    {
        // Get bed admissions
        $results = array_filter($results, function ($value) {
            return count($value['bed_admission']) > 1;
        });

        foreach ($results as $key => $result) {
            $result['bed_resident'] = [];
            foreach ($result['bed_admission'] as $bed => $value) {
                $stmt = $this->em->getConnection()
                    ->prepare(sprintf(self::$QUERY['ADMISSION_RESIDENT'], $this->database_name, $bed));
                $stmt->execute();
                $results[$key]['bed_resident'][$bed] = $stmt->fetchAll(\Doctrine\DBAL\FetchMode::COLUMN, 0);
            }
        }

        return $results;
    }

    /**
     * @param array $queries
     * @throws \Doctrine\DBAL\DBALException
     */
    private function queryExecute(array $queries) {
        foreach ($queries as $query) {
            $stmt = $this->em->getConnection()->prepare($query);
            $stmt->execute();
        }
    }

    private function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}
