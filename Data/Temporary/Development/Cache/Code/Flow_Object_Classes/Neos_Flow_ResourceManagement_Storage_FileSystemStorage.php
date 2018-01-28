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
use Neos\Flow\ResourceManagement\CollectionInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\ResourceManagement\ResourceRepository;
use Neos\Flow\Utility\Environment;

/**
 * A resource storage based on the (local) file system
 */
class FileSystemStorage_Original implements StorageInterface
{
    /**
     * Name which identifies this resource storage
     *
     * @var string
     */
    protected $name;

    /**
     * The path (in a filesystem) where resources are stored
     *
     * @var string
     */
    protected $path;

    /**
     * @Flow\Inject
     * @var Environment
     */
    protected $environment;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var ResourceRepository
     */
    protected $resourceRepository;

    /**
     * Constructor
     *
     * @param string $name Name of this storage instance, according to the resource settings
     * @param array $options Options for this storage
     * @throws Exception
     */
    public function __construct($name, array $options = [])
    {
        $this->name = $name;
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'path':
                    $this->$key = $value;
                break;
                default:
                    if ($value !== null) {
                        throw new Exception(sprintf('An unknown option "%s" was specified in the configuration of a resource FileSystemStorage. Please check your settings.', $key), 1361533187);
                    }
            }
        }
    }

    /**
     * Initializes this resource storage
     *
     * @return void
     * @throws Exception If the storage directory does not exist
     */
    public function initializeObject()
    {
        if (!is_dir($this->path) && !is_link($this->path)) {
            throw new Exception('The directory "' . $this->path . '" which was configured as a resource storage does not exist.', 1361533189);
        }
    }

    /**
     * Returns the instance name of this storage
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns a stream handle which can be used internally to open / copy the given resource
     * stored in this storage.
     *
     * @param PersistentResource $resource The resource stored in this storage
     * @return resource | boolean The resource stream or FALSE if the stream could not be obtained
     */
    public function getStreamByResource(PersistentResource $resource)
    {
        $pathAndFilename = $this->getStoragePathAndFilenameByHash($resource->getSha1());
        return (file_exists($pathAndFilename) ? fopen($pathAndFilename, 'rb') : false);
    }

    /**
     * Returns a stream handle which can be used internally to open / copy the given resource
     * stored in this storage.
     *
     * @param string $relativePath A path relative to the storage root, for example "MyFirstDirectory/SecondDirectory/Foo.css"
     * @return resource | boolean A URI (for example the full path and filename) leading to the resource file or FALSE if it does not exist
     */
    public function getStreamByResourcePath($relativePath)
    {
        $pathAndFilename = $this->path . ltrim($relativePath, '/');
        return (file_exists($pathAndFilename) ? fopen($pathAndFilename, 'r') : false);
    }

    /**
     * Retrieve all Objects stored in this storage.
     *
     * @param callable $callback Function called after each iteration
     * @return \Generator<Object>
     */
    public function getObjects(callable $callback = null)
    {
        foreach ($this->resourceManager->getCollectionsByStorage($this) as $collection) {
            yield $this->getObjectsByCollection($collection, $callback);
        }
    }

    /**
     * Retrieve all Objects stored in this storage, filtered by the given collection name
     *
     * @param callable $callback Function called after each iteration
     * @param CollectionInterface $collection
     * @return \Generator<Object>
     */
    public function getObjectsByCollection(CollectionInterface $collection, callable $callback = null)
    {
        $iterator = $this->resourceRepository->findByCollectionNameIterator($collection->getName());
        $iteration = 0;
        foreach ($this->resourceRepository->iterate($iterator, $callback) as $resource) {
            /** @var PersistentResource $resource */
            $object = new StorageObject();
            $object->setFilename($resource->getFilename());
            $object->setSha1($resource->getSha1());
            $object->setMd5($resource->getMd5());
            $object->setFileSize($resource->getFileSize());
            $object->setStream(function () use ($resource) {
                return $this->getStreamByResource($resource);
            });
            yield $object;
            if (is_callable($callback)) {
                call_user_func($callback, $iteration, $object);
            }
            $iteration++;
        }
    }

    /**
     * Determines and returns the absolute path and filename for a storage file identified by the given SHA1 hash.
     *
     * This function assures a nested directory structure in order to avoid thousands of files in a single directory
     * which may result in performance problems in older file systems such as ext2, ext3 or NTFS.
     *
     * @param string $sha1Hash The SHA1 hash identifying the stored resource
     * @return string The path and filename, for example "/var/www/mysite.com/Data/Persistent/c/8/2/8/c828d0f88ce197be1aff7cc2e5e86b1244241ac6"
     */
    protected function getStoragePathAndFilenameByHash($sha1Hash)
    {
        return $this->path . $sha1Hash[0] . '/' . $sha1Hash[1] . '/' . $sha1Hash[2] . '/' . $sha1Hash[3] . '/' . $sha1Hash;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\ResourceManagement\Storage;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A resource storage based on the (local) file system
 */
class FileSystemStorage extends FileSystemStorage_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if ('Neos\Flow\ResourceManagement\Storage\FileSystemStorage' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Flow\ResourceManagement\Storage\FileSystemStorage';
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

        $isSameClass = get_class($this) === 'Neos\Flow\ResourceManagement\Storage\FileSystemStorage';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\ResourceManagement\Storage\FileSystemStorage', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Utility\Environment', 'Neos\Flow\Utility\Environment', 'environment', 'cce2af5ed9f80b598c497d98c35a5eb3', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Utility\Environment'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceRepository', 'Neos\Flow\ResourceManagement\ResourceRepository', 'resourceRepository', 'c121c89d5bf9838de842b20a63415b71', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceRepository'); });
        $this->Flow_Injected_Properties = array (
  0 => 'environment',
  1 => 'resourceManager',
  2 => 'resourceRepository',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/ResourceManagement/Storage/FileSystemStorage.php
#