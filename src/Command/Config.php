<?php
namespace Evoweb\CurseDownloader\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Config extends \Symfony\Component\Console\Command\Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('curse:config')
            ->setDescription('Start internal webserver and open browser');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Console\Application $application */
        $application = $this->getApplication();
        /** @var \Evoweb\CurseDownloader\AppKernel $kernel */
        $kernel = $application->getKernel();
        $host = '127.0.0.1:9876';
        exec('sensible-browser ' . $host . ' && php -S ' . $host . ' -t ' . $kernel->getWebDir());
    }
}
