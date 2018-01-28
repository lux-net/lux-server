<?php 
namespace Neos\Flow\Monitor;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cache\CacheManager;
use Neos\Cache\Frontend\StringFrontend;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\Monitor\ChangeDetectionStrategy\ChangeDetectionStrategyInterface;
use Neos\Flow\Monitor\ChangeDetectionStrategy\StrategyWithMarkDeletedInterface;
use Neos\Flow\SignalSlot\Dispatcher;
use Neos\Utility\Files;

/**
 * A monitor which detects changes in directories or files
 *
 * @api
 */
class FileMonitor_Original
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var ChangeDetectionStrategyInterface
     */
    protected $changeDetectionStrategy;

    /**
     * @var Dispatcher
     */
    protected $signalDispatcher;

    /**
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @var StringFrontend
     */
    protected $cache;

    /**
     * @var array
     */
    protected $monitoredFiles = [];

    /**
     * @var array
     */
    protected $monitoredDirectories = [];

    /**
     * Changed files for this monitor
     *
     * @var array
     */
    protected $changedFiles = null;

    /**
     * The changed paths for this monitor
     *
     * @var array
     */
    protected $changedPaths = null;

    /**
     * Array of directories and files that were cached on the last run.
     *
     * @var array
     */
    protected $directoriesAndFiles = null;

    /**
     * Constructs this file monitor
     *
     * @param string $identifier Name of this specific file monitor - will be used in the signals emitted by this monitor.
     * @api
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Helper method to create a FileMonitor instance during boot sequence as injections have to be done manually.
     *
     * @param string $identifier
     * @param Bootstrap $bootstrap
     * @return FileMonitor
     */
    public static function createFileMonitorAtBoot($identifier, Bootstrap $bootstrap)
    {
        $fileMonitorCache = $bootstrap->getEarlyInstance(CacheManager::class)->getCache('Flow_Monitor');

        // The change detector needs to be instantiated and registered manually because
        // it has a complex dependency (cache) but still needs to be a singleton.
        $fileChangeDetector = new ChangeDetectionStrategy\ModificationTimeStrategy();
        $fileChangeDetector->injectCache($fileMonitorCache);
        $bootstrap->getObjectManager()->registerShutdownObject($fileChangeDetector, 'shutdownObject');

        $fileMonitor = new FileMonitor($identifier);
        $fileMonitor->injectCache($fileMonitorCache);
        $fileMonitor->injectChangeDetectionStrategy($fileChangeDetector);
        $fileMonitor->injectSignalDispatcher($bootstrap->getEarlyInstance(Dispatcher::class));
        $fileMonitor->injectSystemLogger($bootstrap->getEarlyInstance(SystemLoggerInterface::class));

        return $fileMonitor;
    }

    /**
     * Injects the Change Detection Strategy
     *
     * @param ChangeDetectionStrategyInterface $changeDetectionStrategy The strategy to use for detecting changes
     * @return void
     */
    public function injectChangeDetectionStrategy(ChangeDetectionStrategyInterface $changeDetectionStrategy)
    {
        $this->changeDetectionStrategy = $changeDetectionStrategy;
        $this->changeDetectionStrategy->setFileMonitor($this);
    }

    /**
     * Injects the Singal Slot Dispatcher because classes of the Monitor subpackage cannot be proxied by the AOP
     * framework because it is not initialized at the time the monitoring is used.
     *
     * @param Dispatcher $signalDispatcher The Signal Slot Dispatcher
     * @return void
     */
    public function injectSignalDispatcher(Dispatcher $signalDispatcher)
    {
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Injects the system logger
     *
     * @param SystemLoggerInterface $systemLogger
     * @return void
     */
    public function injectSystemLogger(SystemLoggerInterface $systemLogger)
    {
        $this->systemLogger = $systemLogger;
    }

    /**
     * Injects the Flow_Monitor cache
     *
     * @param StringFrontend $cache
     * @return void
     */
    public function injectCache(StringFrontend $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Returns the identifier of this monitor
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Adds the specified file to the list of files to be monitored.
     * The file in question does not necessarily have to exist.
     *
     * @param string $pathAndFilename Absolute path and filename of the file to monitor
     * @return void
     * @throws \InvalidArgumentException
     * @api
     */
    public function monitorFile($pathAndFilename)
    {
        if (!is_string($pathAndFilename)) {
            throw new \InvalidArgumentException('String expected, ' . gettype($pathAndFilename), ' given.', 1231171809);
        }
        $pathAndFilename = Files::getUnixStylePath($pathAndFilename);
        if (array_search($pathAndFilename, $this->monitoredFiles) === false) {
            $this->monitoredFiles[] = $pathAndFilename;
        }
    }

    /**
     * Adds the specified directory to the list of directories to be monitored.
     * All files in these directories will be monitored too.
     *
     * @param string $path Absolute path of the directory to monitor
     * @param string $filenamePattern A pattern for filenames to consider for file monitoring (regular expression)
     * @return void
     * @throws \InvalidArgumentException
     * @api
     */
    public function monitorDirectory($path, $filenamePattern = null)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('String expected, ' . gettype($path), ' given.', 1231171810);
        }
        $path = Files::getNormalizedPath(Files::getUnixStylePath($path));
        if (!array_key_exists($path, $this->monitoredDirectories)) {
            $this->monitoredDirectories[$path] = $filenamePattern;
        }
    }

    /**
     * Returns a list of all monitored files
     *
     * @return array A list of paths and filenames of monitored files
     * @api
     */
    public function getMonitoredFiles()
    {
        return $this->monitoredFiles;
    }

    /**
     * Returns a list of all monitored directories
     *
     * @return array A list of paths of monitored directories
     * @api
     */
    public function getMonitoredDirectories()
    {
        return array_keys($this->monitoredDirectories);
    }

    /**
     * Detects changes of the files and directories to be monitored and emits signals
     * accordingly.
     *
     * @return void
     * @api
     */
    public function detectChanges()
    {
        if ($this->changedFiles === null || $this->changedPaths === null) {
            $this->loadDetectedDirectoriesAndFiles();
            $changesDetected = false;
            $this->changedPaths = $this->changedFiles = [];
            $this->changedFiles = $this->detectChangedFiles($this->monitoredFiles);

            foreach ($this->monitoredDirectories as $path => $filenamePattern) {
                $changesDetected = $this->detectChangesOnPath($path, $filenamePattern) ? true : $changesDetected;
            }

            if ($changesDetected) {
                $this->saveDetectedDirectoriesAndFiles();
            }
            $this->directoriesAndFiles = null;
        }

        $changedFileCount = count($this->changedFiles);
        $changedPathCount = count($this->changedPaths);

        if ($changedFileCount > 0) {
            $this->emitFilesHaveChanged($this->identifier, $this->changedFiles);
        }
        if ($changedPathCount > 0) {
            $this->emitDirectoriesHaveChanged($this->identifier, $this->changedPaths);
        }
        if ($changedFileCount > 0 || $changedPathCount) {
            $this->systemLogger->log(sprintf('File Monitor "%s" detected %s changed files and %s changed directories.', $this->identifier, $changedFileCount, $changedPathCount), LOG_INFO);
        }
    }

    /**
     * Detect changes for one of the monitored paths.
     *
     * @param string $path
     * @param string $filenamePattern
     * @return boolean TRUE if any changes were detected in this path
     */
    protected function detectChangesOnPath($path, $filenamePattern)
    {
        $currentDirectoryChanged = false;
        try {
            $currentSubDirectoriesAndFiles = $this->readMonitoredDirectoryRecursively($path, $filenamePattern);
        } catch (\Exception $exception) {
            $currentSubDirectoriesAndFiles = [];
            $this->changedPaths[$path] = ChangeDetectionStrategyInterface::STATUS_DELETED;
        }

        $nowDetectedFilesAndDirectories = [];
        if (!isset($this->directoriesAndFiles[$path])) {
            $this->directoriesAndFiles[$path] = [];
            $this->changedPaths[$path] = ChangeDetectionStrategyInterface::STATUS_CREATED;
        }

        foreach ($currentSubDirectoriesAndFiles as $pathAndFilename) {
            $status = $this->changeDetectionStrategy->getFileStatus($pathAndFilename);
            if ($status !== ChangeDetectionStrategyInterface::STATUS_UNCHANGED) {
                $this->changedFiles[$pathAndFilename] = $status;
                $currentDirectoryChanged = true;
            }

            if (isset($this->directoriesAndFiles[$path][$pathAndFilename])) {
                unset($this->directoriesAndFiles[$path][$pathAndFilename]);
            }
            $nowDetectedFilesAndDirectories[$pathAndFilename] = 1;
        }

        if ($this->directoriesAndFiles[$path] !== []) {
            foreach (array_keys($this->directoriesAndFiles[$path]) as $pathAndFilename) {
                $this->changedFiles[$pathAndFilename] = ChangeDetectionStrategyInterface::STATUS_DELETED;
                if ($this->changeDetectionStrategy instanceof StrategyWithMarkDeletedInterface) {
                    $this->changeDetectionStrategy->setFileDeleted($pathAndFilename);
                } else {
                    // This call is needed to mark the file deleted in any possibly existing caches of the strategy.
                    // The return value is not important as we know this file doesn't exist so we set the status to DELETED anyway.
                    $this->changeDetectionStrategy->getFileStatus($pathAndFilename);
                }
            }
            $currentDirectoryChanged = true;
        }

        if ($currentDirectoryChanged) {
            $this->setDetectedFilesForPath($path, $nowDetectedFilesAndDirectories);
        }

        return $currentDirectoryChanged;
    }

    /**
     * Read a monitored directory recursively, taking into account filename patterns
     *
     * @param string $path The path of a monitored directory
     * @param string $filenamePattern
     * @return \Generator<string> A generator returning filenames with full path
     */
    protected function readMonitoredDirectoryRecursively($path, $filenamePattern)
    {
        $directories = [Files::getNormalizedPath($path)];
        while ($directories !== []) {
            $currentDirectory = array_pop($directories);
            if (is_file($currentDirectory . '.flowFileMonitorIgnore')) {
                continue;
            }
            if ($handle = opendir($currentDirectory)) {
                while (false !== ($filename = readdir($handle))) {
                    if ($filename[0] === '.') {
                        continue;
                    }
                    $pathAndFilename = $currentDirectory . $filename;
                    if (is_dir($pathAndFilename)) {
                        array_push($directories, $pathAndFilename . DIRECTORY_SEPARATOR);
                    } elseif ($filenamePattern === null || preg_match('|' . $filenamePattern . '|', $filename) === 1) {
                        yield $pathAndFilename;
                    }
                }
                closedir($handle);
            }
        }
    }

    /**
     * Loads the last detected files for this monitor.
     *
     * @return void
     */
    protected function loadDetectedDirectoriesAndFiles()
    {
        if ($this->directoriesAndFiles === null) {
            $this->directoriesAndFiles = json_decode($this->cache->get($this->identifier . '_directoriesAndFiles'), true);
            if (!is_array($this->directoriesAndFiles)) {
                $this->directoriesAndFiles = [];
            }
        }
    }

    /**
     * Store the changed directories and files back to the cache.
     *
     * @return void
     */
    protected function saveDetectedDirectoriesAndFiles()
    {
        $this->cache->set($this->identifier . '_directoriesAndFiles', json_encode($this->directoriesAndFiles));
    }

    /**
     * @param string $path
     * @param array $files
     * @return void
     */
    protected function setDetectedFilesForPath($path, array $files)
    {
        $this->directoriesAndFiles[$path] = $files;
    }

    /**
     * Detects changes in the given list of files and emits signals if necessary.
     *
     * @param array $pathAndFilenames A list of full path and filenames of files to check
     * @return array An array of changed files (key = path and filenmae) and their status (value)
     */
    protected function detectChangedFiles(array $pathAndFilenames)
    {
        $changedFiles = [];
        foreach ($pathAndFilenames as $pathAndFilename) {
            $status = $this->changeDetectionStrategy->getFileStatus($pathAndFilename);
            if ($status !== ChangeDetectionStrategyInterface::STATUS_UNCHANGED) {
                $changedFiles[$pathAndFilename] = $status;
            }
        }
        return $changedFiles;
    }

    /**
     * Signalizes that the specified file has changed
     *
     * @param string $monitorIdentifier Name of the monitor which detected the change
     * @param array $changedFiles An array of changed files (key = path and filename) and their status (value)
     * @return void
     * @api
     */
    protected function emitFilesHaveChanged($monitorIdentifier, array $changedFiles)
    {
        $this->signalDispatcher->dispatch(FileMonitor::class, 'filesHaveChanged', [$monitorIdentifier, $changedFiles]);
    }

    /**
     * Signalizes that the specified directory has changed
     *
     * @param string $monitorIdentifier Name of the monitor which detected the change
     * @param array $changedDirectories An array of changed directories (key = path) and their status (value)
     * @return void
     * @api
     */
    protected function emitDirectoriesHaveChanged($monitorIdentifier, array $changedDirectories)
    {
        $this->signalDispatcher->dispatch(FileMonitor::class, 'directoriesHaveChanged', [$monitorIdentifier, $changedDirectories]);
    }

    /**
     * Caches the directories and their files
     *
     * @return void
     */
    public function shutdownObject()
    {
        $this->changeDetectionStrategy->shutdownObject();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Monitor;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A monitor which detects changes in directories or files
 */
class FileMonitor extends FileMonitor_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $identifier Name of this specific file monitor - will be used in the signals emitted by this monitor.
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $identifier in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Monitor\FileMonitor' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Flow\Monitor\FileMonitor';
        if ($isSameClass) {
        \Neos\Flow\Core\Bootstrap::$staticObjectManager->registerShutdownObject($this, 'shutdownObject');
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
);
        $propertyVarTags = array (
  'identifier' => 'string',
  'changeDetectionStrategy' => 'Neos\\Flow\\Monitor\\ChangeDetectionStrategy\\ChangeDetectionStrategyInterface',
  'signalDispatcher' => 'Neos\\Flow\\SignalSlot\\Dispatcher',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'cache' => 'Neos\\Cache\\Frontend\\StringFrontend',
  'monitoredFiles' => 'array',
  'monitoredDirectories' => 'array',
  'changedFiles' => 'array',
  'changedPaths' => 'array',
  'directoriesAndFiles' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;

        $isSameClass = get_class($this) === 'Neos\Flow\Monitor\FileMonitor';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\Monitor\FileMonitor', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

        if ($isSameClass || $isClassProxy) {
        \Neos\Flow\Core\Bootstrap::$staticObjectManager->registerShutdownObject($this, 'shutdownObject');
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectCache(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Cache\CacheManager')->getCache('Flow_Monitor'));
        $this->injectChangeDetectionStrategy(new \Neos\Flow\Monitor\ChangeDetectionStrategy\ModificationTimeStrategy());
        $this->injectSignalDispatcher(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\SignalSlot\Dispatcher'));
        $this->injectSystemLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'));
        $this->Flow_Injected_Properties = array (
  0 => 'cache',
  1 => 'changeDetectionStrategy',
  2 => 'signalDispatcher',
  3 => 'systemLogger',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Monitor/FileMonitor.php
#