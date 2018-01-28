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

use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\Storage\Exception as StorageException;
use Neos\Flow\Utility\Algorithms;
use Neos\Utility\Files;

/**
 * A resource storage based on the (local) file system
 */
class WritableFileSystemStorage_Original extends FileSystemStorage implements WritableStorageInterface
{
    /**
     * Initializes this resource storage
     *
     * @return void
     * @throws StorageException
     */
    public function initializeObject()
    {
        if (!is_writable($this->path)) {
            Files::createDirectoryRecursively($this->path);
        }
        if (!is_dir($this->path) && !is_link($this->path)) {
            throw new StorageException('The directory "' . $this->path . '" which was configured as a resource storage does not exist.', 1361533189);
        }
        if (!is_writable($this->path)) {
            throw new StorageException('The directory "' . $this->path . '" which was configured as a resource storage is not writable.', 1361533190);
        }
    }

    /**
     * Imports a resource (file) from the given URI or PHP resource stream into this storage.
     *
     * On a successful import this method returns a PersistentResource object representing the newly imported persistent resource.
     *
     * @param string | resource $source The URI (or local path and filename) or the PHP resource stream to import the resource from
     * @param string $collectionName Name of the collection the new PersistentResource belongs to
     * @throws StorageException
     * @return PersistentResource A resource object representing the imported resource
     */
    public function importResource($source, $collectionName)
    {
        $temporaryTargetPathAndFilename = $this->environment->getPathToTemporaryDirectory() . 'Neos_Flow_ResourceImport_' . Algorithms::generateRandomString(13);

        if (is_resource($source)) {
            try {
                $target = fopen($temporaryTargetPathAndFilename, 'wb');
                stream_copy_to_stream($source, $target);
                fclose($target);
            } catch (\Exception $exception) {
                throw new StorageException(sprintf('Could import the content stream to temporary file "%s".', $temporaryTargetPathAndFilename), 1380880079);
            }
        } else {
            try {
                copy($source, $temporaryTargetPathAndFilename);
            } catch (\Exception $exception) {
                throw new StorageException(sprintf('Could not copy the file from "%s" to temporary file "%s".', $source, $temporaryTargetPathAndFilename), 1375198876);
            }
        }

        return $this->importTemporaryFile($temporaryTargetPathAndFilename, $collectionName);
    }

    /**
     * Imports a resource from the given string content into this storage.
     *
     * On a successful import this method returns a PersistentResource object representing the newly
     * imported persistent resource.
     *
     * The specified filename will be used when presenting the resource to a user. Its file extension is
     * important because the resource management will derive the IANA Media Type from it.
     *
     * @param string $content The actual content to import
     * @param string $collectionName Name of the collection the new PersistentResource belongs to
     * @return PersistentResource A resource object representing the imported resource
     * @throws StorageException
     */
    public function importResourceFromContent($content, $collectionName)
    {
        $temporaryTargetPathAndFilename = $this->environment->getPathToTemporaryDirectory() . 'Neos_Flow_ResourceImport_' . Algorithms::generateRandomString(13);
        try {
            file_put_contents($temporaryTargetPathAndFilename, $content);
        } catch (\Exception $exception) {
            throw new StorageException(sprintf('Could import the content stream to temporary file "%s".', $temporaryTargetPathAndFilename), 1381156098);
        }

        return $this->importTemporaryFile($temporaryTargetPathAndFilename, $collectionName);
    }

    /**
     * Deletes the storage data related to the given PersistentResource object
     *
     * @param PersistentResource $resource The PersistentResource to delete the storage data of
     * @return boolean TRUE if removal was successful
     */
    public function deleteResource(PersistentResource $resource)
    {
        $pathAndFilename = $this->getStoragePathAndFilenameByHash($resource->getSha1());
        if (!file_exists($pathAndFilename)) {
            return true;
        }
        if (unlink($pathAndFilename) === false) {
            return false;
        }
        Files::removeEmptyDirectoriesOnPath(dirname($pathAndFilename));
        return true;
    }

    /**
     * Imports the given temporary file into the storage and creates the new resource object.
     *
     * Note: the temporary file is (re-)moved by this method.
     *
     * @param string $temporaryPathAndFileName
     * @param string $collectionName
     * @return PersistentResource
     * @throws StorageException
     */
    protected function importTemporaryFile($temporaryPathAndFileName, $collectionName)
    {
        $this->fixFilePermissions($temporaryPathAndFileName);
        $sha1Hash = sha1_file($temporaryPathAndFileName);
        $targetPathAndFilename = $this->getStoragePathAndFilenameByHash($sha1Hash);

        if (!is_file($targetPathAndFilename)) {
            $this->moveTemporaryFileToFinalDestination($temporaryPathAndFileName, $targetPathAndFilename);
        } else {
            unlink($temporaryPathAndFileName);
        }

        $resource = new PersistentResource();
        $resource->setFileSize(filesize($targetPathAndFilename));
        $resource->setCollectionName($collectionName);
        $resource->setSha1($sha1Hash);
        $resource->setMd5(md5_file($targetPathAndFilename));

        return $resource;
    }

    /**
     * Move a temporary file to the final destination, creating missing path segments on the way.
     *
     * @param string $temporaryFile
     * @param string $finalTargetPathAndFilename
     * @return void
     * @throws StorageException
     */
    protected function moveTemporaryFileToFinalDestination($temporaryFile, $finalTargetPathAndFilename)
    {
        if (!file_exists(dirname($finalTargetPathAndFilename))) {
            Files::createDirectoryRecursively(dirname($finalTargetPathAndFilename));
        }
        if (copy($temporaryFile, $finalTargetPathAndFilename) === false) {
            throw new StorageException(sprintf('The temporary file of the file import could not be moved to the final target "%s".', $finalTargetPathAndFilename), 1381156103);
        }
        unlink($temporaryFile);

        $this->fixFilePermissions($finalTargetPathAndFilename);
    }

    /**
     * Fixes the permissions as needed for Flow to run fine in web and cli context.
     *
     * @param string $pathAndFilename
     * @return void
     */
    protected function fixFilePermissions($pathAndFilename)
    {
        @chmod($pathAndFilename, 0666 ^ umask());
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
class WritableFileSystemStorage extends WritableFileSystemStorage_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if ('Neos\Flow\ResourceManagement\Storage\WritableFileSystemStorage' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Flow\ResourceManagement\Storage\WritableFileSystemStorage';
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

        $isSameClass = get_class($this) === 'Neos\Flow\ResourceManagement\Storage\WritableFileSystemStorage';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\ResourceManagement\Storage\WritableFileSystemStorage', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

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
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/ResourceManagement/Storage/WritableFileSystemStorage.php
#