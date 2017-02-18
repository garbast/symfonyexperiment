<?php
namespace Evoweb\CurseDownloader\Command\Minecraft;

use Symfony\Component\Console\Input\InputArgument;

class UpdateCommand extends \Symfony\Component\Console\Command\Command
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
        $this->ignoreValidationErrors();

        $this->setName('curse:minecraft:update')
            ->setDefinition(
                [
                    new InputArgument('manifest', InputArgument::OPTIONAL, 'Path to manifest.json'),
                ]
            )
            ->setDescription('Import mod pack based on manifest.json');
    }
}
