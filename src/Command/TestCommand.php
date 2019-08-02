<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{
    protected static $defaultName = 'app:test';

    /** @var EntityManagerInterface */
    private $em;

    /** @var \Twig\Environment */
    private $twig;

    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine')->getManager();
        $this->twig = $container->get('twig');

        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this
            ->setDescription('Test command.')
            ->setHelp('Test command.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $process = new Process(['dir', '/?']);
        $process->run();

        if (!$process->isSuccessful()) {
            $ppp = new ProcessFailedException($process);
            $output->writeln($ppp->getMessage());
        }

        var_dump($process->getOutput());
    }

}
