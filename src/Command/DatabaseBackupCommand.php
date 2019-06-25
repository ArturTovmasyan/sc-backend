<?php

namespace App\Command;

use App\Entity\Vhost;
use BackupManager\Compressors\CompressorProvider;
use BackupManager\Compressors\GzipCompressor;
use BackupManager\Config\Config;
use BackupManager\Databases\DatabaseProvider;
use BackupManager\Databases\MysqlDatabase;
use BackupManager\Filesystems\Awss3Filesystem;
use BackupManager\Filesystems\Destination;
use BackupManager\Filesystems\FilesystemProvider;
use BackupManager\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DatabaseBackupCommand extends Command
{
    protected static $defaultName = 'app:database:backup';

    /** @var EntityManagerInterface */
    private $em;

    /** @var Manager */
    private $manager;

    public function __construct(ContainerInterface $container, $name = null)
    {
        $this->em = $container->get('doctrine')->getManager();

        parent::__construct($name);

        var_dump();
        die();
    }

    protected function configure()
    {
        $this
            ->setHelp('Backup client databases.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $vhosts = $this->em->getRepository(Vhost::class)->findAll();

            $date_string = (new \DateTime())->format('Ymd_his');

            $databases = [];

            /** @var Vhost $vhost */
            foreach ($vhosts as $vhost) {
                $domain = $vhost->getCustomer()->getDomain();

                $databases[$domain] = [
                    'type' => 'mysql',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'user' => 'root',
                    'pass' => 'guHlxo!=prIwocI5HOX2',
                    'database' => $vhost->getDbName(),
                    'singleTransaction' => false
                ];

            }

            $this->manager = $this->createManager($databases);

            /** @var Vhost $vhost */
            foreach ($vhosts as $vhost) {
                $domain = $vhost->getCustomer()->getDomain();

                $this->manager->makeBackup()->run(
                    $domain,
                    [
                        new Destination(
                            's3',
                            sprintf('backup_%s_%s.sql', $vhost->getDbName(), $date_string)
                        )
                    ],
                    'gzip'
                );
            }

        } catch (\Throwable $e) {
            dump($e->getMessage());
            dump($e->getTraceAsString());
        }
    }

    private function createManager($databaseConfig)
    {
        $filesystems = new FilesystemProvider(new Config(['s3' => [
            'type' => 'AwsS3',
            'region' => $_ENV['AWS_REGION'],
            'version' => $_ENV['AWS_VERSION'],
            'key' => $_ENV['AWS_ACCESS_KEY_ID'],
            'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
            'bucket' => $_ENV['AWS_BUCKET'],
            'root' => '',
        ]]));

        $filesystems->add(new Awss3Filesystem());

        $databases = new DatabaseProvider($databaseConfig);
        $databases->add(new MysqlDatabase());

        $compressors = new CompressorProvider();
        $compressors->add(new GzipCompressor());

        return new Manager($filesystems, $databases, $compressors);
    }
}
