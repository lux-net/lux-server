<?php 
namespace Neos\Flow\ResourceManagement;

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
use Neos\Flow\ResourceManagement\Storage\PackageStorage;
use Neos\Flow\ResourceManagement\Storage\StorageInterface;
use Neos\Flow\ResourceManagement\Storage\WritableStorageInterface;
use Neos\Flow\ResourceManagement\Target\TargetInterface;
use Neos\Flow\ResourceManagement\Exception as ResourceException;

/**
 * A resource collection
 */
class Collection_Original implements CollectionInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var TargetInterface
     */
    protected $target;

    /**
     * @var array
     */
    protected $pathPatterns;

    /**
     * @Flow\Inject
     * @var ResourceRepository
     */
    protected $resourceRepository;

    /**
     * Constructor
     *
     * @param string $name User-space name of this collection, as specified in the settings
     * @param StorageInterface $storage The storage for data used in this collection
     * @param TargetInterface $target The publication target for this collection
     * @param array $pathPatterns Glob patterns for paths to consider – only supported by specific storages
     */
    public function __construct($name, StorageInterface $storage, TargetInterface $target, array $pathPatterns)
    {
        $this->name = $name;
        $this->storage = $storage;
        $this->target = $target;
        $this->pathPatterns = $pathPatterns;
    }

    /**
     * Returns the name of this collection
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the storage used for this collection
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Returns the publication target defined for this collection
     *
     * @return TargetInterface
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Imports a resource (file) from the given URI or PHP resource stream into this collection.
     *
     * On a successful import this method returns a PersistentResource object representing the newly
     * imported persistent resource.
     *
     * Note that this collection must have a writable storage in order to import resources.
     *
     * @param string | resource $source The URI (or local path and filename) or the PHP resource stream to import the resource from
     * @return PersistentResource A resource object representing the imported resource
     * @throws ResourceException
     */
    public function importResource($source)
    {
        if (!$this->storage instanceof WritableStorageInterface) {
            throw new ResourceException(sprintf('Could not import resource into collection "%s" because its storage "%s" is a read-only storage.', $this->name, $this->storage->getName()), 1375197288);
        }
        return $this->storage->importResource($source, $this->name);
    }

    /**
     * Imports a resource from the given string content into this collection.
     *
     * On a successful import this method returns a PersistentResource object representing the newly
     * imported persistent resource.
     *
     * Note that this collection must have a writable storage in order to import resources.
     *
     * The specified filename will be used when presenting the resource to a user. Its file extension is
     * important because the resource management will derive the IANA Media Type from it.
     *
     * @param string $content The actual content to import
     * @return PersistentResource A resource object representing the imported resource
     * @throws ResourceException
     */
    public function importResourceFromContent($content)
    {
        if (!$this->storage instanceof WritableStorageInterface) {
            throw new ResourceException(sprintf('Could not import resource into collection "%s" because its storage "%s" is a read-only storage.', $this->name, $this->storage->getName()), 1381155740);
        }
        return $this->storage->importResourceFromContent($content, $this->name);
    }

    /**
     * Publishes the whole collection to the corresponding publishing target
     *
     * @return void
     */
    public function publish()
    {
        $this->target->publishCollection($this);
    }

    /**
     * Returns all internal data objects of the storage attached to this collection.
     *
     * @param callable $callback Function called after each object
     * @return \Generator<Storage\Object>
     */
    public function getObjects(callable $callback = null)
    {
        $objects = [];
        if ($this->storage instanceof PackageStorage && $this->pathPatterns !== []) {
            $objects = new \AppendIterator();
            foreach ($this->pathPatterns as $pathPattern) {
                $objects->append($this->storage->getObjectsByPathPattern($pathPattern, $callback));
            }
        } else {
            $objects = $this->storage->getObjectsByCollection($this, $callback);
        }

        // TODO: Implement filter manipulation here:
        // foreach ($objects as $object) {
        // 	$object->setStream(function() { return fopen('/tmp/test.txt', 'rb');});
        // }

        return $objects;
    }

    /**
     * Returns a stream handle of the given persistent resource which allows for opening / copying the resource's
     * data. Note that this stream handle may only be used read-only.
     *
     * @param PersistentResource $resource The resource to retrieve the stream for
     * @return resource | boolean The resource stream or FALSE if the stream could not be obtained
     */
    public function getStreamByResource(PersistentResource $resource)
    {
        $stream = $this->getStorage()->getStreamByResource($resource);
        if ($stream !== false) {
            $meta = stream_get_meta_data($stream);
            if ($meta['seekable']) {
                rewind($stream);
            }
        }
        return $stream;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\ResourceManagement;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A resource collection
 */
class Collection extends Collection_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param string $name User-space name of this collection, as specified in the settings
     * @param StorageInterface $storage The storage for data used in this collection
     * @param TargetInterface $target The publication target for this collection
     * @param array $pathPatterns Glob patterns for paths to consider – only supported by specific storages
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $name in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $storage in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $target in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        if (!array_key_exists(3, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $pathPatterns in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\ResourceManagement\Collection' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
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
  'storage' => 'Neos\\Flow\\ResourceManagement\\Storage\\StorageInterface',
  'target' => 'Neos\\Flow\\ResourceManagement\\Target\\TargetInterface',
  'pathPatterns' => 'array',
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
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceRepository', 'Neos\Flow\ResourceManagement\ResourceRepository', 'resourceRepository', 'c121c89d5bf9838de842b20a63415b71', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceRepository'); });
        $this->Flow_Injected_Properties = array (
  0 => 'resourceRepository',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/ResourceManagement/Collection.php
#