<?php
namespace Evoweb\CurseDownloader\Command\WoW;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommmand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var array
     */
    protected $configuration;

    /**
     * DownloadCommmand constructor.
     *
     * @param array $configuration
     */
    public function __construct($configuration)
    {
        parent::__construct();

        $this->configuration = $configuration;
    }

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
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}
