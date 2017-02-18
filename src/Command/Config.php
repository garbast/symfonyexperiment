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
        $this->ignoreValidationErrors();

        $this->setName('config')
            ->setDescription('Start internal webserver and open browser');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Evoweb\CurseDownloader\Application $application */
        $application = $this->getApplication();
        $host = '127.0.0.1:9876';
        exec('sensible-browser ' . $host . ' && php -S ' . $host . ' -t ' . $application->path . '/Web/');
    }
}
