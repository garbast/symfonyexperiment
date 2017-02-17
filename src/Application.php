<?php
namespace Evoweb\CurseDownloader;

class Application extends \Symfony\Component\Console\Application
{
    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new \Evoweb\CurseDownloader\Command\Minecraft\Download();
        $defaultCommands[] = new \Evoweb\CurseDownloader\Command\Minecraft\Update();
        $defaultCommands[] = new \Evoweb\CurseDownloader\Command\WoW\Download();
        $defaultCommands[] = new \Evoweb\CurseDownloader\Command\WoW\Update();

        return $defaultCommands;
    }
}
