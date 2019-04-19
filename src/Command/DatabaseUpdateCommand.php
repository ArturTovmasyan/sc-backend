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

class DatabaseUpdateCommand extends Command
{
    protected static $defaultName = 'app:database:update';

    /** @var EntityManagerInterface */
    private $em;

    /** @var Filesystem */
    private $filesystem;

    /** @var array */
    private $env;

    public function __construct(ContainerInterface $container, $name = null)
    {
        $this->em = $container->get('doctrine')->getManager();

        $this->filesystem = new Filesystem();

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setHelp('Database schema update for all customers command.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configs = $this->em->getRepository(CustomerConfig::class)->findBy(['enabled' => true]);

        try {
            /** @var CustomerConfig $configs */
            foreach ($configs as $configs) {
                $domain = '';

                $db = [
                    'name' => '',
                    'user' => '',
                    'pass' => '',
                ];
                $mailer = [
                    'proto' => '',
                    'host' => '',
                    'user' => '',
                    'pass' => '',
                ];

                $dir_name = [
                    'root' => '',
                    'var' => '',
                    'cdn' => '',
                ];

                $output->writeln(sprintf("Updating database structure for '%s'...", $domain));

                $this->filesystem->remove(sprintf("%s/cache/prod/*", $dir_name['var']));
                $this->filesystem->remove(sprintf("%s/cache/dev/*", $dir_name['var']));

                $this->createEnv($db, $mailer, $dir_name, $domain);
                $this->updateDatabase($dir_name['root']);

                $this->filesystem->remove(sprintf("%s/cache/prod/*", $dir_name['var']));
                $this->filesystem->remove(sprintf("%s/cache/dev/*", $dir_name['var']));

                $output->writeln(sprintf("Completed database structure update for '%s'.", $domain));
            }
        } catch (\Throwable $e) {
            dump($e->getMessage());
            dump($e->getTraceAsString());
        }
    }

    private function updateDatabase($root_dir)
    {
        $path = [];
        $path['php'] = 'php';// '/usr/bin/php'
        $path['symfony_console'] = sprintf('%s/bin/console', $root_dir);

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
