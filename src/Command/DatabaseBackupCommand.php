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
use BackupManager\Filesystems\LocalFilesystem;
use BackupManager\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DatabaseBackupCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:database:backup';

    /** @var EntityManagerInterface */
    private $em;

    /** @var Manager */
    private $manager;

    public function __construct(ContainerInterface $container, $name = null)
    {
        $this->em = $container->get('doctrine')->getManager();

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setHelp('Backup client databases.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return -1;
        }

        try {
            $date_string = (new \DateTime())->format('Ymd');
            $datetime_string = (new \DateTime())->format('Ymd_His');

            $databases = [];

            $databases['console.seniorcaresw.com'] = [
                'type' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'user' => 'root',
                'pass' => 'guHlxo!=prIwocI5HOX2',
                'database' => 'db_seniorcare_mc',
                'singleTransaction' => false
            ];

            $databases['scpp.seniorcaresw.com'] = [
                'type' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'user' => 'root',
                'pass' => 'guHlxo!=prIwocI5HOX2',
                'database' => 'db_seniorcare_scpp',
                'singleTransaction' => false
            ];

            $vhosts = $this->em->getRepository(Vhost::class)->findAll();
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

            $this->manager = $this->createManager($date_string, $databases);

            foreach ($databases as $domain => $value) {
                $output->writeln(sprintf("Backup database of '%s'...", $domain));

                $this->manager->makeBackup()->run(
                    $domain,
                    [
                        new Destination(
                            's3',
                            sprintf('%s_%s.sql', $datetime_string, $value['database'])
                        )
                    ],
                    'gzip'
                );

                $output->writeln(sprintf("Backup finished database of '%s'.", $domain));
            }

        } catch (\Throwable $e) {
            dump($e->getMessage());
            dump($e->getTraceAsString());
        }

        $this->release();

        return 0;
    }

    private function createManager($date_string, $databases)
    {
        $filesystems = new FilesystemProvider(new Config([
            'local' => [
                'type' => 'Local',
                'root' => '/backup',
            ],
            's3' => [
                'type' => 'AwsS3',
                'region' => $_ENV['AWS_REGION'],
                'version' => $_ENV['AWS_VERSION'],
                'key' => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
                'bucket' => $_ENV['AWS_BUCKET'],
                'root' => $date_string,
            ]]));

        $filesystems->add(new LocalFilesystem());
        $filesystems->add(new Awss3Filesystem());

        $databases = new DatabaseProvider(new Config($databases));
        $databases->add(new MysqlDatabase());

        $compressors = new CompressorProvider();
        $compressors->add(new GzipCompressor());

        return new Manager($filesystems, $databases, $compressors);
    }
}
