<?php

namespace App\Command;

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
        $domain = 'aaa.seniorcaresw.com';
        $domain_sc = preg_replace("/[^A-Za-z0-9]+/", "_", $domain);

        $vhost_file_name = sprintf("001-%s.conf", $domain);
        $vhost_file_path = sprintf("/etc/apache2/sites-available/%s", $vhost_file_name);
        $vhost_file_link = sprintf("/etc/apache2/sites-enabled/%s", $vhost_file_name);

        $mailer = [];
        $mailer['proto'] = sprintf('gmail');
        $mailer['host'] = sprintf('localhost');
        $mailer['user'] = sprintf('imt.tester1@gmail.com');
        $mailer['pass'] = sprintf('ImtArmenia');

        $db = [];
        $db['name'] = sprintf('sc_%s_db', $domain_sc);
        $db['user'] = sprintf('sc_%s_user', $domain_sc);
        $db['pass'] = sprintf('sc_%s_db', $domain_sc);

        $gold_dir_name = [];
        $gold_dir_name['root'] = sprintf("D:/SVN/senior-care.");
        $gold_dir_name['dist'] = sprintf("%sfrontend/dist", $gold_dir_name['root']);
        $gold_dir_name['bin'] = sprintf("%sbackend/bin", $gold_dir_name['root']);
        $gold_dir_name['config'] = sprintf("%sbackend/config", $gold_dir_name['root']);
        $gold_dir_name['public'] = sprintf("%sbackend/public", $gold_dir_name['root']);
        $gold_dir_name['src'] = sprintf("%sbackend/src", $gold_dir_name['root']);
        $gold_dir_name['templates'] = sprintf("%sbackend/templates", $gold_dir_name['root']);
        $gold_dir_name['tests'] = sprintf("%sbackend/tests", $gold_dir_name['root']);
        $gold_dir_name['vendor'] = sprintf("%sbackend/vendor", $gold_dir_name['root']);

        $dir_name = [];
        $dir_name['root'] = sprintf("/srv/%s", $domain);
        $dir_name['cdn'] = sprintf("%s/cdn", $dir_name['root']);
        $dir_name['var'] = sprintf("%s/var", $dir_name['root']);
        $dir_name['env'] = sprintf("%s/.env", $dir_name['root']);


        try {
            $output->writeln(sprintf("Creating WWW directory structure for '%s'...", $domain));
            $this->filesystem->mkdir($dir_name['root']);
            $this->filesystem->mkdir($dir_name['cdn']);
            $this->filesystem->mkdir(sprintf('%s/resident-photo', $dir_name['cdn']));
            $this->filesystem->mkdir(sprintf('%s/user-avatar', $dir_name['cdn']));
            $this->filesystem->mkdir($dir_name['var']);

            $output->writeln(sprintf("Setting filesystem permissions for '%s'...", $domain));
//            $this->filesystem->chmod($dir_name['root'], 755);
//
//            $this->filesystem->chmod($dir_name['var'], 755, true);
//            $this->filesystem->chown($dir_name['var'], 'www-data', true);
//            $this->filesystem->chgrp($dir_name['var'], 'www-data', true);
//
//            $this->filesystem->chmod($dir_name['cdn'], 755, true);
//            $this->filesystem->chown($dir_name['cdn'], 'www-data', true);
//            $this->filesystem->chgrp($dir_name['cdn'], 'www-data', true);

            $output->writeln(sprintf("Creating symlinks '%s'...", $domain));
            foreach ($gold_dir_name as $name => $gold_path) {
                if ($name === "root") {
                    continue;
                }

                $this->filesystem->symlink($gold_path, sprintf("%s/%s", $dir_name['root'], $name));
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
            $this->filesystem->dumpFile(
                $dir_name['env'],
                $this->twig->render(
                    'dotenv.twig',
                    [
                        'env' => $this->env
                    ])
            );

            $this->createDatabaseUser($db);
            $this->createDatabase($dir_name['root']);

            // Add db import
            // Create user/user invite

            $this->apacheReload();

            $output->writeln(sprintf("Customer '%s' successfully created.", $domain));
        } catch (\Throwable $e) {
            dump($e->getMessage());
            dump($e->getTraceAsString());
        }
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
        $path['php'] = 'php';// '/usr/bin/php'
        $path['symfony_console'] = sprintf('%s/bin/console', $root_dir);

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

    private function createEnv($db, $mailer, $dir_name, $domain)
    {
        $this->env = [
            'APP_ENV' => 'dev',
            'APP_SECRET' => '441e2c01edab863446135746a45396bd',
            'DATABASE_URL' => sprintf('mysql://%s:%s@127.0.0.1:3306/%s', $db['user'], $db['pass'], $db['name']),
            'MAILER_URL' => sprintf('%s://%s:%s@%s', $mailer['proto'], $mailer['user'], $mailer['pass'], $mailer['host']),
            'CDN_PATH' => sprintf('%s', $dir_name['cdn']),
            'CORS_ALLOW_ORIGIN' => sprintf('^https?://%s(:[0-9]+)?$', $domain),
            'WKHTMLTOPDF_PATH' => '/usr/local/bin/wkhtmltopdf',
            'WKHTMLTOIMAGE_PATH' => '/usr/local/bin/wkhtmltoimage',
        ];
    }

}
