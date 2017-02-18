<?php
namespace Evoweb\CurseDownloader\Command\WoW;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Download extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var FilesystemAdapter
     */
    protected $cache;

    /**
     * @var string
     */
    protected $gamePath = '~/.wine/drive_c/Program Files (x86)/World of Warcraft/';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('curse:wow:download')
            ->setDefinition(
                [
                    new InputArgument('manifest', InputArgument::OPTIONAL, 'Path to manifest.json'),
                ]
            )
            ->setDescription('Download addon');
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        /** @var \Evoweb\CurseDownloader\Application $application */
        $application = $this->getApplication();

        $this->cache = FilesystemAdapter::createSystemCache(
            'WoW',
            0,
            'nongiven',
            $application->path . DIRECTORY_SEPARATOR . 'cache'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
