<?php

namespace App\Command;

use App\Entity\Customer;
use App\Entity\Job;
use App\Entity\Vhost;
use App\Model\JobStatus;
use App\Model\JobType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CustomerCreateCommand extends Command
{
    protected static $defaultName = 'app:customer:create';

    /** @var EntityManagerInterface */
    private $em;

    /** @var \Twig\Environment */
    private $twig;

    /** @var Filesystem */
    private $filesystem;

    /** @var array */
    private $env;

    public function __construct(ContainerInterface $container, $name = null)
    {
        $this->em = $container->get('doctrine')->getManager();
        $this->twig = $container->get('twig');

        $this->filesystem = new Filesystem();

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setHelp('Create Customer command.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jobs = $this->em->getRepository(Job::class)->findBy([
            'type' => JobType::TYPE_CREATE, 'status' => JobStatus::TYPE_NOT_STARTED
        ]);

        /** @var Job $job */
        foreach ($jobs as $job) {
            $job->setStartDate(new \DateTime());
            $job->setStatus(JobStatus::TYPE_STARTED);
            $this->em->persist($job);
            $this->em->flush();

            try {
                $this->createCustomer($job->getCustomer(), $output);

                $job->setEndDate(new \DateTime());
                $job->setStatus(JobStatus::TYPE_SUCCESS);
            } catch (\Throwable $ex) {
                $job->setEndDate(new \DateTime());
                $job->setStatus(JobStatus::TYPE_ERROR);
                $job->setLog($ex->getTraceAsString());

                $output->writeln($ex->getMessage());
                $output->writeln($ex->getTraceAsString());
            }

            $this->em->persist($job);
            $this->em->flush();
        }
    }

    /**
     * @param Customer $customer
     * @param OutputInterface $output
     * @throws \Throwable
     */
    private function createCustomer($customer, OutputInterface $output)
    {
        $domain = $customer->getDomain();

        $vhost_file_name = sprintf("001-%s.conf", $domain);
        $vhost_file_path = sprintf("/etc/apache2/sites-available/%s", $vhost_file_name);
        $vhost_file_link = sprintf("/etc/apache2/sites-enabled/%s", $vhost_file_name);

        $mailer = [];
        $mailer['host'] = sprintf('localhost');
        $mailer['proto'] = sprintf('gmail');
        $mailer['user'] = sprintf('support@seniorcaresw.com');
        $mailer['pass'] = sprintf('6!I9uF*Ks3sG!qG');

        $db = [];
        $db['host'] = '127.0.0.1';
        $db['name'] = sprintf('sc_%04d_db', $customer->getId());
        $db['user'] = sprintf('sc_%04d_user', $customer->getId());
        $db['pass'] = sprintf('sc_%04d_db', $customer->getId());

        $gold_dir_name = [];
        $gold_dir_name['root'] = sprintf("/srv/_vcs/");
        $gold_dir_name['dist'] = sprintf("%sfrontend/dist", $gold_dir_name['root']);
        $gold_dir_name['bin'] = sprintf("%sbackend/bin", $gold_dir_name['root']);
        $gold_dir_name['config'] = sprintf("%sbackend/config", $gold_dir_name['root']);
        $gold_dir_name['public'] = sprintf("%sbackend/public", $gold_dir_name['root']);

        $gold_dir_name['src/Annotation'] = sprintf("%sbackend/src/Annotation", $gold_dir_name['root']);
        $gold_dir_name['src/Command'] = sprintf("%sbackend/src/Command", $gold_dir_name['root']);
        $gold_dir_name['src/Controller'] = sprintf("%sbackend/src/Controller", $gold_dir_name['root']);
        $gold_dir_name['src/Entity'] = sprintf("%sbackend/src/Entity", $gold_dir_name['root']);
        $gold_dir_name['src/EventListener'] = sprintf("%sbackend/src/EventListener", $gold_dir_name['root']);
        $gold_dir_name['src/Exception'] = sprintf("%sbackend/src/Exception", $gold_dir_name['root']);
        $gold_dir_name['src/Migrations'] = sprintf("%sbackend/src/Migrations", $gold_dir_name['root']);
        $gold_dir_name['src/Model'] = sprintf("%sbackend/src/Model", $gold_dir_name['root']);
        $gold_dir_name['src/Repository'] = sprintf("%sbackend/src/Repository", $gold_dir_name['root']);
        $gold_dir_name['src/Service'] = sprintf("%sbackend/src/Service", $gold_dir_name['root']);
        $gold_dir_name['src/Util'] = sprintf("%sbackend/src/Util", $gold_dir_name['root']);

        $gold_dir_name['templates'] = sprintf("%sbackend/templates", $gold_dir_name['root']);
        $gold_dir_name['tests'] = sprintf("%sbackend/tests", $gold_dir_name['root']);
        $gold_dir_name['vendor'] = sprintf("%sbackend/vendor", $gold_dir_name['root']);

        $gold_file_name['src/Kernel'] = sprintf("%sbackend/src/Kernel.php", $gold_dir_name['root']);

        $dir_name = [];
        $dir_name['root'] = sprintf("/srv/%s", $domain);
        $dir_name['src'] = sprintf("%s/src", $dir_name['root']);
        $dir_name['var'] = sprintf("%s/var", $dir_name['root']);
        $dir_name['env'] = sprintf("%s/.env", $dir_name['root']);

        $file_name['src/Kernel'] = sprintf("%s/src/Kernel.php", $dir_name['root']);

        $vhost = new Vhost();
        $vhost->setCustomer($customer);

        $vhost->setName($vhost_file_name);
        $vhost->setWwwRoot($dir_name['root']);

        $vhost->setDbHost($db['host']);
        $vhost->setDbName($db['name']);
        $vhost->setDbUser($db['user']);
        $vhost->setDbPassword($db['pass']);

        $vhost->setMailerHost($mailer['host']);
        $vhost->setMailerProto($mailer['proto']);
        $vhost->setMailerUser($mailer['user']);
        $vhost->setMailerPassword($mailer['pass']);

        $this->em->persist($vhost);
        $this->em->flush();

        $output->writeln(sprintf("Creating WWW directory structure for '%s'...", $domain));
        $this->filesystem->mkdir($dir_name['root']);
        $this->filesystem->mkdir($dir_name['src']);
        $this->filesystem->mkdir($dir_name['var']);

        $output->writeln(sprintf("Setting filesystem permissions for '%s'...", $domain));

        $this->filesystem->chmod($dir_name['root'], 0755);

        $this->filesystem->chmod($dir_name['var'], 0755, 0000, true);
        $this->filesystem->chown($dir_name['var'], 'www-data', true);
        $this->filesystem->chgrp($dir_name['var'], 'www-data', true);

        $output->writeln(sprintf("Creating symlinks '%s'...", $domain));
        foreach ($gold_dir_name as $name => $gold_path) {
            if ($name === "root") {
                continue;
            }

            $this->filesystem->symlink($gold_path, sprintf("%s/%s", $dir_name['root'], $name));
        }

        $output->writeln(sprintf("Creating hardlinks '%s'...", $domain));
        foreach ($gold_file_name as $name => $gold_path) {
            $this->filesystem->hardlink($gold_path, $file_name[$name]);
        }

        $this->createEnv($db, $mailer, $dir_name, $domain);

        $output->writeln(sprintf("Creating Apache virtual host file..."));

        $this->filesystem->dumpFile(
            $vhost_file_path,
            $this->twig->render(
                'vhost.conf.twig',
                [
                    'domain' => $domain,
                    'dir_name' => $dir_name,
                    'env' => $this->env
                ])
        );
        $this->filesystem->symlink($vhost_file_path, $vhost_file_link);

        $output->writeln(sprintf("Creating database user and structure..."));

//        $this->filesystem->dumpFile(
//            $dir_name['env'],
//            $this->twig->render(
//                'dotenv.twig',
//                [
//                    'env' => $this->env
//                ])
//        );

        $this->createDatabaseUser($db);
        $this->createDatabase($dir_name['root']);
        $this->createSchema($dir_name['root']);

        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_udfs.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_roles.sql');

        $this->createAdminUser($dir_name['root'], $customer);

        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_allergen.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_care_level.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_csz.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_diagnosis.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_diet.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_event_definition.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_medical_history_condition.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_medication.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_medication_form_factor.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_payment_source.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_relationship.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_responsible_person_role.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_salutation.sql');
        $this->importSQL($dir_name['root'], '/srv/_mc/backend/etc/sc_data_speciality.sql');

        $this->apacheReload();

        $output->writeln(sprintf("Customer '%s' successfully created.", $domain));
    }

    private function apacheReload()
    {
        $process = new Process(['/usr/sbin/apachectl', 'graceful']);
        $process->run();

        dump($process->getOutput());

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * @param $db
     * @throws \Throwable
     */
    private function createDatabaseUser($db)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $this->em->getConnection()->prepare(sprintf("CREATE USER '%s'@'127.0.0.1' IDENTIFIED BY '%s';", $db['user'], $db['pass']))->execute();
            $this->em->getConnection()->prepare(sprintf("REVOKE ALL PRIVILEGES,GRANT OPTION FROM '%s'@'127.0.0.1';", $db['user']))->execute();
            $this->em->getConnection()->prepare(sprintf("GRANT ALL ON `%s`.* TO '%s'@'127.0.0.1';", $db['name'], $db['user']))->execute();
            $this->em->getConnection()->prepare(sprintf("FLUSH PRIVILEGES;"))->execute();


            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    private function createDatabase($root_dir)
    {
        $path = [];
        $path['php'] = '/usr/bin/php';
        $path['symfony_console'] = sprintf('%s/bin/console', $root_dir);

        $this->env['APP_ENV'] = 'dev';

        $process = new Process(
            [$path['php'], $path['symfony_console'], 'doctrine:database:create', '--no-ansi'],
            null, $this->env
        );

        $process->run();

        dump($process->getOutput());

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private function createSchema($root_dir)
    {
        $path = [];
        $path['php'] = '/usr/bin/php';
        $path['symfony_console'] = sprintf('%s/bin/console', $root_dir);

        $this->env['APP_ENV'] = 'dev';

        $process = new Process(
            [$path['php'], $path['symfony_console'], 'doctrine:schema:update', '--dump-sql', '--force'],
            null, $this->env
        );

        $process->run();

        dump($process->getOutput());

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private function importSQL($root_dir, $sql_file)
    {
        $path = [];
        $path['php'] = '/usr/bin/php';
        $path['symfony_console'] = sprintf('%s/bin/console', $root_dir);

        $this->env['APP_ENV'] = 'dev';

        $process = new Process(
            [$path['php'], $path['symfony_console'], 'doctrine:database:import', $sql_file],
            null, $this->env
        );

        $process->run();

        dump($process->getOutput());

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * @param string $root_dir
     * @param Customer $customer
     */
    private function createAdminUser($root_dir, $customer)
    {
        $path = [];
        $path['php'] = '/usr/bin/php';
        $path['symfony_console'] = sprintf('%s/bin/console', $root_dir);

        $this->env['APP_ENV'] = 'dev';

        $process = new Process(
            [
                $path['php'],
                $path['symfony_console'],
                'app:create-customer',
                $customer->getDomain(),
                $customer->getOrganization(),
                $customer->getFirstName(),
                $customer->getLastName(),
                $customer->getEmail(),
                $customer->getPhone(),
                /*
                                sprintf('--domain=%s', $customer->getDomain()),
                                sprintf('--organization=%s', $customer->getOrganization()),
                                sprintf('--first_name=%s', $customer->getFirstName()),
                                sprintf('--last_name=%s', $customer->getLastName()),
                                sprintf('--email=%s', $customer->getEmail()),
                                sprintf('--phone=%s', $customer->getPhone()),
                */
            ],
            null, $this->env
        );

        $process->run();

        dump($process->getOutput());

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private function createEnv($db, $mailer, $dir_name, $domain)
    {
        $this->env = [
            'APP_ENV' => 'prod',
            'APP_SECRET' => '441e2c01edab863446135746a45396bd',
            'DATABASE_URL' => sprintf('mysql://%s:%s@127.0.0.1:3306/%s', $db['user'], $db['pass'], $db['name']),
            'MAILER_URL' => sprintf('%s://%s:%s@%s', $mailer['proto'], $mailer['user'], $mailer['pass'], $mailer['host']),
            'CORS_ALLOW_ORIGIN' => sprintf('^https?://%s(:[0-9]+)?$', $domain),
            'WKHTMLTOPDF_PATH' => '/usr/local/bin/wkhtmltopdf',
            'WKHTMLTOIMAGE_PATH' => '/usr/local/bin/wkhtmltoimage',
            'AWS_REGION' => 'us-west-1',
            'AWS_VERSION' => 'latest',
            'AWS_KEY' => 'AKIA53G2U6QMX3WT52AG',
            'AWS_SECRET' => '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',
            'AWS_BUCKET' => $domain
        ];
    }

}
