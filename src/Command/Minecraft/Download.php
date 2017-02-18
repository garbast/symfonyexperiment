<?php
namespace Evoweb\CurseDownloader\Command\Minecraft;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Download extends \Symfony\Component\Console\Command\Command
{
    /**
     * Manifest content of curse minecraft mod packs
     *
     * @var \stdClass
     */
    protected $manifest;

    /**
     * MultiMC configuration to be written
     *
     * @var array
     */
    protected $multiMcConfiguration = [
        'InstanceType' => 'OneSix',
        'IntendedVersion' => 0,
        'LogPrePostOutput' => 'true',
        'MaxMemAlloc' => '4096',
        'MinMemAlloc' => '2048',
        'OverrideCommands' => 'false',
        'OverrideConsole' => 'false',
        'OverrideJavaArgs' => 'false',
        'OverrideJavaLocation' => 'false',
        'OverrideMemory' => 'false',
        'OverrideWindow' => 'false',
        'PermGen' => '128',
        'iconKey' => 'default',
        'lastLaunchTime' => 0,
        'name' => '',
        'notes' => '',
        'totalTimePlayed' => 0,
    ];

    /**
     * Parameter for guzzle client requests
     *
     * @var array
     */
    protected $redirectOptions = [
        'allow_redirects' => [
            'track_redirects' => true
        ]
    ];

    protected $downloaderPath = '';

    protected $filePath = '';

    protected $modPackPath = '';

    protected $overridePath = '';

    protected $minecraftPath = '';

    /**
     * @var FilesystemAdapter
     */
    protected $cache;

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('minecraft:download')
            ->setDefinition(
                [
                    new InputArgument('manifest', InputArgument::OPTIONAL, 'Path to manifest.json'),
                ]
            )
            ->setDescription('Import mod pack based on manifest.json');
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

        $this->downloaderPath = $application->path;

        $this->cache = FilesystemAdapter::createSystemCache(
            'WoW',
            0,
            'nongiven',
            $application->path . DIRECTORY_SEPARATOR . 'cache'
        );
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->getManifestFilePath($input)) {
            $this->getManifestContent();
            $this->createMultiMcConfigFile();
            $this->handleOverrides();
            $this->makeFolder($this->minecraftPath . 'mods/');
            $this->downloadCurseMods($output);
            $this->downloadDirectMods($output);
        }
    }

    /**
     * @param InputInterface $input
     * @return bool
     */
    protected function getManifestFilePath(InputInterface $input)
    {
        if (file_exists(getcwd() . '/manifest.json')) {
            $this->filePath = getcwd() . '/manifest.json';
        } elseif ($input->hasArgument('manifest')
            && file_exists($input->getArgument('manifest'))
            && is_file($input->getArgument('manifest'))
        ) {
            $this->filePath = $input->getArgument('manifest');
        } elseif ($input->hasArgument('manifest')
            && file_exists(rtrim($input->getArgument('manifest'), '/') . '/manifest.json')
        ) {
            $this->filePath = rtrim($input->getArgument('manifest'), '/') . '/manifest.json';
        }

        if ($this->filePath != '') {
            $this->modPackPath = dirname($this->filePath) . '/';
            $this->minecraftPath = $this->modPackPath . 'minecraft/';
            $this->makeFolder($this->minecraftPath);
        }

        return $this->filePath != '';
    }

    /**
     * @return void
     */
    protected function getManifestContent()
    {
        $this->manifest = \json_decode(file_get_contents($this->filePath));
    }

    /**
     * @return void
     */
    protected function createMultiMcConfigFile()
    {
        $configuration = array_merge(
            $this->multiMcConfiguration,
            [
                'IntendedVersion' => $this->manifest->minecraft->version,
                'name' => $this->manifest->name,
                'notes' => $this->manifest->author . ' ' . $this->manifest->version,
            ]
        );
        $this->writeConfigFile($this->modPackPath . 'instance.cfg', $configuration);

        if (!file_exists($this->modPackPath . 'patches')) {
            $patchConfig = \json_decode(
                file_get_contents($this->downloaderPath . '/Resources/MultiMC/net.minecraftforge.json')
            );
            $patchConfig->mcVersion = $this->manifest->minecraft->version;
            $patchConfig->version = str_replace(
                'forge-',
                '',
                $this->manifest->minecraft->modLoaders[0]->id
            ) . '-' . $patchConfig->mcVersion;

            foreach ($patchConfig->{'+libraries'} as &$lib) {
                if (strpos($lib->name, ':forge:') === false) {
                    continue;
                }

                $lib->name = 'net.minecraftforge:forge:' . $patchConfig->mcVersion .
                    '-' . $patchConfig->version . ':universal';
            }

            $this->makeFolder($this->modPackPath . 'patches');
            $this->writeConfigFile(
                $this->modPackPath . 'patches/net.minecraftforge.json',
                [str_replace('","', '",' . chr(10) . '"', \json_encode($patchConfig))],
                false
            );
        }
    }

    /**
     * @param string $filepath
     * @param array $configuration
     * @param bool $useKey
     */
    protected function writeConfigFile($filepath, array $configuration, $useKey = true)
    {
        if (!file_exists($filepath)) {
            $fileHandle = fopen($filepath, 'w');

            foreach ($configuration as $key => $value) {
                $key = ($useKey ? $key . '=' : '');
                fwrite($fileHandle, $key . (string)$value . chr(10));
            }

            fclose($fileHandle);
        }
    }

    /**
     * @return void
     */
    protected function handleOverrides()
    {
        $this->overridePath = $this->modPackPath . $this->manifest->overrides . '/';

        if (is_dir($this->overridePath)) {
            $this->recursiveCopy($this->overridePath, $this->minecraftPath);
        }
    }

    /**
     * @param string $source
     * @param string $destination
     */
    protected function recursiveCopy($source, $destination)
    {
        $directory = dir($source);

        $this->makeFolder($destination);

        while (false !== ($file = $directory->read())) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $sourcePath = $source . '/' . $file;
            $destinationPath = $destination . '/' . $file;
            if (is_dir($sourcePath)) {
                $this->recursiveCopy($sourcePath, $destinationPath);
            } else {
                copy($sourcePath, $destinationPath);
            }
        }

        $directory->close();
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    protected function downloadCurseMods(OutputInterface $output)
    {
        $modCount = count($this->manifest->files);
        $output->writeln('Mods to download ' . $modCount);

        $downloadCounter = 1;
        foreach ($this->manifest->files as $mod) {
            $cacheEntry = $mod->projectID . '/' . $mod->fileID . '/';

            if (!$this->cache->hasItem($cacheEntry)) {
                $result = $this->downloadCurseFile($cacheEntry, $mod);
                $item = new CacheItem();
                $this->cache->save($item);
            }
            $filename = $this->cache->getItem($cacheEntry);

            $output->writeln(
                '[' . $downloadCounter . '/' . $modCount . '] '
                . ($filename ? $filename : 'File for ' . $mod->projectID . ' not available.')
            );

            $downloadCounter++;
        }
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function getClient()
    {
        return new \GuzzleHttp\Client();
    }

    /**
     * @param string $cacheEntry
     * @return bool
     */
    protected function isCacheFileExists($cacheEntry)
    {
        $result = false;

        if (file_exists($cacheEntry)) {
            $directory = dir($cacheEntry);
            while (false !== ($file = $directory->read())) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $result = true;
                break;
            }
            $directory->close();
        }

        return $result;
    }

    /**
     * @param string $cacheEntry
     * @param \stdClass $mod
     * @return string
     */
    protected function downloadCurseFile($cacheEntry, $mod)
    {
        $this->makeFolder($cacheEntry);

        $filename = '';
        $content = '';
        $client = $this->getClient();
        try {
            $projectUrl = $this->getProjectUrl($client, $mod);
            $fileUrl = $this->getFileUrl($client, $mod, $projectUrl, $response);
            $filename = urldecode(pathinfo($fileUrl, PATHINFO_BASENAME));

            /** @var ResponseInterface $response */
            $content = $response->getBody()->getContents();
        } catch (\Exception $e) {
        }

        return ['filename' => $filename, 'content' => $content];
    }

    /**
     * @param string $cacheEntry
     * @return string
     */
    protected function copyFileFromCache($cacheEntry)
    {
        $directory = dir($cacheEntry);
        while (false !== ($filename = $directory->read())) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            copy($cacheEntry . $filename, $this->minecraftPath . 'mods/' . $filename);
            break;
        }
        $directory->close();

        return $filename;
    }

    /**
     * @param \GuzzleHttp\Client $client
     * @param \stdClass $mod
     * @return string
     */
    protected function getProjectUrl($client, \stdClass $mod)
    {
        $response = $client->request(
            'GET',
            'http://minecraft.curseforge.com/mc-mods/' . $mod->projectID,
            $this->redirectOptions
        );

        return $response->getHeader('X-Guzzle-Redirect-History')[0];
    }

    /**
     * @param \GuzzleHttp\Client $client
     * @param \stdClass $mod
     * @param string $projectUrl
     * @param ResponseInterface $response
     * @return string
     */
    protected function getFileUrl($client, \stdClass $mod, $projectUrl, &$response)
    {
        $response = $client->request(
            'GET',
            $projectUrl . '/files/' . $mod->fileID . '/download',
            $this->redirectOptions
        );

        return $response->getHeader('X-Guzzle-Redirect-History')[0];
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    protected function downloadDirectMods(OutputInterface $output)
    {
/*
    # This is not available in curse-only packs
    if 'directDownload' in manifestJson:
        i = 1
        i_len = len(manifestJson['directDownload'])
        programGui.setOutput("%d additional files to download." % i_len)
        for download_entry in manifestJson['directDownload']:
            if "url" not in download_entry or "filename" not in download_entry:
                programGui.setOutput("[%d/%d] <Error>" % (i, i_len))
                i += 1
                continue
            source_url = urlparse(download_entry['url'])
            download_cache_children = Path(source_url.path).parent.relative_to('/')
            download_cache_dir = cache_path / "directdownloads" / download_cache_children
            cache_target = Path(download_cache_dir / download_entry['filename'])
            if cache_target.exists():
                # Cached
                target_file = minecraftPath / "mods" / cache_target.name
                shutil.copyfile(str(cache_target), str(target_file))

                i += 1

                # Cache access is successful,
                # Don't download the file
                continue
            # File is not cached and needs to be downloaded
            file_response = sess.get(source_url, stream=True)
            while file_response.is_redirect:
                source = file_response
                file_response = sess.get(source, stream=True)
            programGui.setOutput("[%d/%d] %s" % (i, i_len, download_entry['filename']))
            with open(str(minecraftPath / "mods" / download_entry['filename']), "wb") as mod:
                mod.write(file_response.content)

            i += 1
 */
    }
}
