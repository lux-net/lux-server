<?php 
namespace Neos\Flow\ResourceManagement\Storage;

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
use Neos\Flow\Package\PackageInterface;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Utility\Files;
use Neos\Utility\Unicode\Functions as UnicodeFunctions;

/**
 * A resource storage which stores and retrieves resources from active Flow packages.
 */
class PackageStorage_Original extends FileSystemStorage
{
    /**
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * Initializes this resource storage
     *
     * @return void
     */
    public function initializeObject()
    {
        // override the parent method because we don't need that here
    }

    /**
     * Retrieve all Objects stored in this storage.
     *
     * @param callable $callback Function called after each iteration
     * @return \Generator<StorageObject>
     */
    public function getObjects(callable $callback = null)
    {
        return $this->getObjectsByPathPattern('*');
    }

    /**
     * Return all Objects stored in this storage filtered by the given directory / filename pattern
     *
     * @param string $pattern A glob compatible directory / filename pattern
     * @param callable $callback Function called after each object
     * @return \Generator<StorageObject>
     */
    public function getObjectsByPathPattern($pattern, callable $callback = null)
    {
        $directories = [];

        if (strpos($pattern, '/') !== false) {
            list($packageKeyPattern, $directoryPattern) = explode('/', $pattern, 2);
        } else {
            $packageKeyPattern = $pattern;
            $directoryPattern = '*';
        }
        // $packageKeyPattern can be used in a future implementation to filter by package key

        $packages = $this->packageManager->getActivePackages();
        foreach ($packages as $packageKey => $package) {
            /** @var PackageInterface $package */
            if ($directoryPattern === '*') {
                $directories[$packageKey][] = $package->getPackagePath();
            } else {
                $directories[$packageKey] = glob($package->getPackagePath() . $directoryPattern, GLOB_ONLYDIR);
            }
        }

        $iteration = 0;
        foreach ($directories as $packageKey => $packageDirectories) {
            foreach ($packageDirectories as $directoryPath) {
                foreach (Files::getRecursiveDirectoryGenerator($directoryPath) as $resourcePathAndFilename) {
                    $object = $this->createStorageObject($resourcePathAndFilename, $packages[$packageKey]);
                    yield $object;
                    if (is_callable($callback)) {
                        call_user_func($callback, $iteration, $object);
                    }
                    $iteration++;
                }
            }
        }
    }

    /**
     * Create a storage object for the given static resource path.
     *
     * @param string $resourcePathAndFilename
     * @param PackageInterface $resourcePackage
     * @return StorageObject
     */
    protected function createStorageObject($resourcePathAndFilename, PackageInterface $resourcePackage)
    {
        $pathInfo = UnicodeFunctions::pathinfo($resourcePathAndFilename);

        $object = new StorageObject();
        $object->setFilename($pathInfo['basename']);
        $object->setSha1(sha1_file($resourcePathAndFilename));
        $object->setMd5(md5_file($resourcePathAndFilename));
        $object->setFileSize(filesize($resourcePathAndFilename));
        if (isset($pathInfo['dirname'])) {
            $object->setRelativePublicationPath($this->prepareRelativePublicationPath($pathInfo['dirname'], $resourcePackage->getPackageKey(), $resourcePackage->getResourcesPath()));
        }
        $object->setStream(function () use ($resourcePathAndFilename) {
            return fopen($resourcePathAndFilename, 'r');
        });

        return $object;
    }

    /**
     * Prepares a relative publication path for a package resource.
     *
     * @param string $objectPath
     * @param string $packageKey
     * @param string $packageResourcePath
     * @return string
     */
    protected function prepareRelativePublicationPath($objectPath, $packageKey, $packageResourcePath)
    {
        $relativePathParts = explode('/', str_replace($packageResourcePath, '', $objectPath), 2);
        $relativePath = '';
        if (isset($relativePathParts[1])) {
            $relativePath = $relativePathParts[1];
        }

        return Files::concatenatePaths([$packageKey, $relativePath]) . '/';
    }

    /**
     * Because we cannot store persistent resources in a PackageStorage, this method always returns FALSE.
     *
     * @param PersistentResource $resource The resource stored in this storage
     * @return resource | boolean The resource stream or FALSE if the stream could not be obtained
     */
    public function getStreamByResource(PersistentResource $resource)
    {
        return false;
    }

    /**
     * Returns the absolute paths of public resources directories of all active packages.
     * This method is used directly by the FileSystemSymlinkTarget.
     *
     * @return array<string>
     */
    public function getPublicResourcePaths()
    {
        $paths = [];
        $packages = $this->packageManager->getActivePackages();
        foreach ($packages as $packageKey => $package) {
            /** @var PackageInterface $package */
            $publicResourcesPath = Files::concatenatePaths([$package->getResourcesPath(), 'Public']);
            if (is_dir($publicResourcesPath)) {
                $paths[$packageKey] = $publicResourcesPath;
            }
        }
        return $paths;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\ResourceManagement\Storage;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A resource storage which stores and retrieves resources from active Flow packages.
 */
class PackageStorage extends PackageStorage_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $name Name of this storage instance, according to the resource settings
     * @param array $options Options for this storage
     * @throws Exception
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $name in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\ResourceManagement\Storage\PackageStorage' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Flow\ResourceManagement\Storage\PackageStorage';
        if ($isSameClass) {
            $this->initializeObject(1);
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
  'packageManager' => 'Neos\\Flow\\Package\\PackageManagerInterface',
  'name' => 'string',
  'path' => 'string',
  'environment' => 'Neos\\Flow\\Utility\\Environment',
  'resourceManager' => 'Neos\\Flow\\ResourceManagement\\ResourceManager',
  'resourceRepository' => 'Neos\\Flow\\ResourceManagement\\ResourceRepository',
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

        $isSameClass = get_class($this) === 'Neos\Flow\ResourceManagement\Storage\PackageStorage';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\ResourceManagement\Storage\PackageStorage', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

        if ($isSameClass || $isClassProxy) {
            $this->initializeObject(2);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Package\PackageManagerInterface', 'Neos\Flow\Package\PackageManager', 'packageManager', 'b44be8eaae4695ec4f42edfbf6f8880a', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Package\PackageManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Utility\Environment', 'Neos\Flow\Utility\Environment', 'environment', 'cce2af5ed9f80b598c497d98c35a5eb3', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Utility\Environment'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceRepository', 'Neos\Flow\ResourceManagement\ResourceRepository', 'resourceRepository', 'c121c89d5bf9838de842b20a63415b71', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceRepository'); });
        $this->Flow_Injected_Properties = array (
  0 => 'packageManager',
  1 => 'environment',
  2 => 'resourceManager',
  3 => 'resourceRepository',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/ResourceManagement/Storage/PackageStorage.php
#