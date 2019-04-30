<?php

namespace App\Command;

use App\Entity\Job;
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

class CustomerEnableCommand extends Command
{
    protected static $defaultName = 'app:customer:enable';

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
            ->setHelp('Enable Customer command.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jobs = $this->em->getRepository(Job::class)->findBy([
            'type' => JobType::TYPE_ENABLE, 'status' => JobStatus::TYPE_NOT_STARTED
        ]);

        /** @var Job $job */
        foreach ($jobs as $job) {
            $job->setStartDate(new \DateTime());
            $job->setStatus(JobStatus::TYPE_STARTED);
            $this->em->persist($job);
            $this->em->flush();

            try {
                $this->apacheEnable($job->getCustomer()->getDomain(), $output);

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
     * @param $domain
     * @param OutputInterface $output
     * @throws \Throwable
     */
    private function apacheEnable($domain, OutputInterface $output)
    {
        $vhost_file_name = sprintf("001-%s.conf", $domain);
        $vhost_file_path = sprintf("/etc/apache2/sites-available/%s", $vhost_file_name);
        $vhost_file_link = sprintf("/etc/apache2/sites-enabled/%s", $vhost_file_name);

        $output->writeln(sprintf("Enabling Apache virtual host file..."));

        $this->filesystem->symlink($vhost_file_path, $vhost_file_link);
        $this->apacheReload();

        $output->writeln(sprintf("Customer '%s' successfully enabled.", $domain));
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

}
