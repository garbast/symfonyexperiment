<?php
namespace Evoweb\CurseDownloader\Command\WoW;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Download extends \Symfony\Component\Console\Command\Command
{
    protected $cachePath;

    protected $downloaderPath;

    protected $gamePath = '/home/sebastian/.wow/drive_c/Program Files (x86)/World of Warcraft/';

    /**
     * Constructor.
     *
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     * @throws \LogicException When the command name is empty
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->downloaderPath = rtrim($GLOBALS['basepath'], '/') . '/';
        $this->cachePath = $this->downloaderPath . 'cache/WoW/';
        $this->makeFolder($this->cachePath);
    }

    /**
     * @param string $path
     * @return void
     */
    protected function makeFolder($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this->setName('wow:download')
            ->setDefinition(
                array(
                    new InputArgument('command_name', InputArgument::OPTIONAL, 'The command name', 'update'),
                    new InputArgument('manifest', InputArgument::OPTIONAL, 'Path to manifest.json'),
                )
            )
            ->setDescription('Import mod pack based on manifest.json');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
