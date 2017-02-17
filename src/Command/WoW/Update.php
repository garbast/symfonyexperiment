<?php
namespace Evoweb\CurseDownloader\Command\WoW;

use Symfony\Component\Console\Input\InputArgument;

class Update extends \Symfony\Component\Console\Command\Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this->setName('wow:update')
            ->setDefinition(
                [
                    new InputArgument('manifest', InputArgument::OPTIONAL, 'Path to manifest.json'),
                ]
            )
            ->setDescription('Import mod pack based on manifest.json');
    }
}
