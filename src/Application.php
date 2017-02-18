<?php
namespace Evoweb\CurseDownloader;

use \Evoweb\CurseDownloader\Command;

/**
 * Class Application
 *
 * @package Evoweb\CurseDownloader
 */
class Application extends \Symfony\Component\Console\Application
{
    /**
     * @var string
     */
    public $path;

    /**
     * Constructor.
     *
     * @param string $name    The name of the application
     * @param string $version The version of the application
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->path = realpath(__DIR__ . '/../');

        parent::__construct($name, $version);

        $this->addCommands([
            new Command\Config(),
            new Command\Minecraft\Download(),
            new Command\Minecraft\Update(),
            new Command\WoW\Download(),
            new Command\WoW\Update(),
        ]);
    }
}
