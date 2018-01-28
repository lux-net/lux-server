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

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Cache\CacheAwareInterface;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Utility\Environment;
use Neos\Utility;
use Neos\Utility\Unicode\Functions as UnicodeFunctions;
use Neos\Flow\ResourceManagement\Exception as ResourceException;

/**
 * Model representing a persistable resource
 *
 * @Flow\Entity
 */
class PersistentResource_Original implements ResourceMetaDataInterface, CacheAwareInterface
{
    /**
     * Name of a collection whose storage is used for storing this resource and whose
     * target is used for publishing.
     *
     * @var string
     */
    protected $collectionName = ResourceManager::DEFAULT_PERSISTENT_COLLECTION_NAME;

    /**
     * Filename which is used when the data of this resource is downloaded as a file or acting as a label
     *
     * @var string
     * @Flow\Validate(type="StringLength", options={ "maximum"=255 })
     * @ORM\Column(length=255)
     */
    protected $filename = '';

    /**
     * The size of this object's data
     *
     * @var integer
     * @ORM\Column(type="decimal", scale=0, precision=20, nullable=false)
     */
    protected $fileSize;

    /**
     * An optional relative path which can be used by a publishing target for structuring resources into directories
     *
     * @var string
     */
    protected $relativePublicationPath = '';

    /**
     * The IANA media type of this resource
     *
     * @var string
     * @Flow\Validate(type="StringLength", options={ "maximum"=100 })
     * @ORM\Column(length=100)
     */
    protected $mediaType;

    /**
     * SHA1 hash identifying the content attached to this resource
     *
     * @var string
     * @ORM\Column(length=40)
     */
    protected $sha1;

    /**
     * MD5 hash identifying the content attached to this resource
     *
     * @var string
     * @ORM\Column(length=32)
     */
    protected $md5;

    /**
     * As soon as the PersistentResource has been published, modifying this object is not allowed
     *
     * @Flow\Transient
     * @var boolean
     */
    protected $protected = false;

    /**
     * @Flow\Transient
     * @var boolean
     */
    protected $lifecycleEventsActive = true;

    /**
     * An internal flag which tells if this PersistentResource object has been deleted during the current request
     *
     * @Flow\Transient
     * @var boolean
     */
    protected $deleted = false;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @Flow\Inject
     * @var Environment
     */
    protected $environment;

    /**
     * @Flow\Transient
     * @var string
     */
    protected $temporaryLocalCopyPathAndFilename;

    /**
     * Protects this PersistentResource if it has been persisted already.
     *
     * @param integer $initializationCause
     * @return void
     */
    public function initializeObject($initializationCause)
    {
        if ($initializationCause === ObjectManagerInterface::INITIALIZATIONCAUSE_RECREATED) {
            $this->protected = true;
        }
    }

    /**
     * Returns a stream for use with read-only file operations such as reading or copying.
     *
     * Note: The caller is responsible to close the returned resource by calling fclose($stream)
     *
     * @return resource | boolean A stream which points to the data of this resource for read-access or FALSE if the stream could not be obtained
     * @api
     */
    public function getStream()
    {
        return $this->resourceManager->getStreamByResource($this);
    }

    /**
     * Sets the name of the collection this resource should be part of
     *
     * @param string $collectionName Name of the collection
     * @return void
     * @api
     */
    public function setCollectionName($collectionName)
    {
        $this->throwExceptionIfProtected();
        $this->collectionName = $collectionName;
    }

    /**
     * Returns the name of the collection this resource is part of
     *
     * @return string Name of the collection, for example "persistentResources"
     * @api
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * Sets the filename which is used when this resource is downloaded or saved as a file
     *
     * @param string $filename
     * @return void
     * @api
     */
    public function setFilename($filename)
    {
        $this->throwExceptionIfProtected();

        $pathInfo = UnicodeFunctions::pathinfo($filename);
        $extension = (isset($pathInfo['extension']) ? '.' . strtolower($pathInfo['extension']) : '');
        $this->filename = $pathInfo['filename'] . $extension;
        $this->mediaType = Utility\MediaTypes::getMediaTypeFromFilename($this->filename);
    }

    /**
     * Gets the filename
     *
     * @return string The filename
     * @api
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Returns the file extension used for this resource
     *
     * @return string The file extension used for this file
     * @api
     */
    public function getFileExtension()
    {
        $pathInfo = pathinfo($this->filename);
        return isset($pathInfo['extension']) ? $pathInfo['extension'] : '';
    }

    /**
     * Sets a relative path which can be used by a publishing target for structuring resources into directories
     *
     * @param string $path
     * @return void
     * @api
     */
    public function setRelativePublicationPath($path)
    {
        $this->throwExceptionIfProtected();
        $this->relativePublicationPath = $path;
    }

    /**
     * Returns the relative publication path
     *
     * @return string
     * @api
     */
    public function getRelativePublicationPath()
    {
        return $this->relativePublicationPath;
    }

    /**
     * Explicitly sets the Media Type for this resource
     *
     * @param string $mediaType The IANA Media Type
     * @return void
     * @api
     */
    public function setMediaType($mediaType)
    {
        $this->mediaType = $mediaType;
    }

    /**
     * Returns the Media Type for this resource
     *
     * @return string The IANA Media Type
     * @api
     */
    public function getMediaType()
    {
        if ($this->mediaType === null) {
            return Utility\MediaTypes::getMediaTypeFromFilename($this->filename);
        } else {
            return $this->mediaType;
        }
    }

    /**
     * Sets the size of the content of this resource
     *
     * @param integer $fileSize The content size
     * @return void
     */
    public function setFileSize($fileSize)
    {
        $this->throwExceptionIfProtected();
        $this->fileSize = $fileSize;
    }

    /**
     * Returns the size of the content of this resource
     *
     * @return integer The content size
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Returns the SHA1 hash of the content of this resource
     *
     * @return string The sha1 hash
     * @api
     */
    public function getSha1()
    {
        return $this->sha1;
    }

    /**
     * Sets the SHA1 hash of the content of this resource
     *
     * @param string $sha1 The sha1 hash
     * @return void
     * @api
     */
    public function setSha1($sha1)
    {
        $this->throwExceptionIfProtected();
        if (!is_string($sha1) || preg_match('/[A-Fa-f0-9]{40}/', $sha1) !== 1) {
            throw new \InvalidArgumentException('Specified invalid hash to setSha1()', 1362564220);
        }
        $this->sha1 = strtolower($sha1);
    }

    /**
     * Returns the MD5 hash of the content of this resource
     *
     * @return string The MD5 hash
     * @api
     */
    public function getMd5()
    {
        return $this->md5;
    }

    /**
     * Sets the MD5 hash of the content of this resource
     *
     * @param string $md5 The MD5 hash
     * @return void
     * @api
     */
    public function setMd5($md5)
    {
        $this->throwExceptionIfProtected();
        $this->md5 = $md5;
    }

    /**
     * Returns the path to a local file representing this resource for use with read-only file operations such as reading or copying.
     *
     * Note that you must not store or publish file paths returned from this method as they will change with every request.
     *
     * @return string Absolute path and filename pointing to the temporary local copy of this resource
     * @throws Exception
     * @api
     */
    public function createTemporaryLocalCopy()
    {
        if ($this->temporaryLocalCopyPathAndFilename === null) {
            $temporaryPathAndFilename = $this->environment->getPathToTemporaryDirectory() . 'ResourceFiles/';
            try {
                Utility\Files::createDirectoryRecursively($temporaryPathAndFilename);
            } catch (Utility\Exception\FilesException $e) {
                throw new ResourceException(sprintf('Could not create the temporary directory %s while trying to create a temporary local copy of resource %s (%s).', $temporaryPathAndFilename, $this->sha1, $this->filename), 1416221864);
            }

            $temporaryPathAndFilename .= $this->getCacheEntryIdentifier();
            $temporaryPathAndFilename .= '-' . microtime(true);

            if (function_exists('posix_getpid')) {
                $temporaryPathAndFilename .= '-' . str_pad(posix_getpid(), 10);
            } else {
                $temporaryPathAndFilename .= '-' . (string) getmypid();
            }

            $temporaryPathAndFilename = trim($temporaryPathAndFilename);
            $temporaryFileHandle = fopen($temporaryPathAndFilename, 'w');
            if ($temporaryFileHandle === false) {
                throw new ResourceException(sprintf('Could not create the temporary file %s while trying to create a temporary local copy of resource %s (%s).', $temporaryPathAndFilename, $this->sha1, $this->filename), 1416221864);
            }
            $resourceStream = $this->getStream();
            if ($resourceStream === false) {
                throw new ResourceException(sprintf('Could not open stream for resource %s ("%s") from collection "%s" while trying to create a temporary local copy.', $this->sha1, $this->filename, $this->collectionName), 1416221863);
            }
            stream_copy_to_stream($resourceStream, $temporaryFileHandle);
            fclose($resourceStream);
            fclose($temporaryFileHandle);
            $this->temporaryLocalCopyPathAndFilename = $temporaryPathAndFilename;
        }

        return $this->temporaryLocalCopyPathAndFilename;
    }

    /**
     * Doctrine lifecycle event callback which is triggered on "postPersist" events.
     * This method triggers the publication of this resource.
     *
     * @return void
     * @ORM\PostPersist
     */
    public function postPersist()
    {
        if ($this->lifecycleEventsActive) {
            $collection = $this->resourceManager->getCollection($this->collectionName);
            $collection->getTarget()->publishResource($this, $collection);
        }
    }

    /**
     * Doctrine lifecycle event callback which is triggered on "preRemove" events.
     * This method triggers the deletion of data related to this resource.
     *
     * @return void
     * @ORM\PreRemove
     */
    public function preRemove()
    {
        if ($this->lifecycleEventsActive && $this->deleted === false) {
            $this->resourceManager->deleteResource($this);
        }
    }

    /**
     * A very internal function which disables the Doctrine lifecycle events for this PersistentResource.
     *
     * This is needed when some low-level operations need to be done, for example deleting a PersistentResource from the
     * ResourceRepository without unpublishing the (probably not existing) data from the storage.
     *
     * @return void
     */
    public function disableLifecycleEvents()
    {
        $this->lifecycleEventsActive = false;
    }

    /**
     * An internal method which marks the PersistentResource object as deleted.
     *
     * This method is called by the ResourceManager in order to prevent other code parts, for example the Doctrine
     * lifecycle events, to delete this resource again.
     *
     * @param boolean $flag
     * @return void
     */
    public function setDeleted($flag = true)
    {
        $this->deleted = $flag;
    }

    /**
     * An internal method which tells if this PersistentResource object has been already deleted by the ResourceManager.
     *
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Returns a string which distinctly identifies this object and thus can be used as an identifier for cache entries
     * related to this object. Introduced through the CacheAwareInterface.
     *
     * @return string
     */
    public function getCacheEntryIdentifier()
    {
        return $this->sha1;
    }

    /**
     * Throws an exception if this PersistentResource object is protected against modifications.
     *
     * @return void
     * @throws ResourceException
     */
    protected function throwExceptionIfProtected()
    {
        if ($this->protected) {
            throw new ResourceException(sprintf('Tried to set a property of the resource object with SHA1 hash %s after it has been protected. Modifications are not allowed as soon as the PersistentResource has been published or persisted.', $this->sha1), 1377852347);
        }
    }

    /**
     * Takes care of removing a possibly existing temporary local copy on destruction of this object.
     *
     * Note: we can't use __destruct() here because this would lead Doctrine to create a proxy method __destruct() which
     *       will run __load(), which in turn will trigger the SQL protection in Flow Security, which will then discover
     *       that a possibly previously existing session has been half-destroyed already (see FLOW-121).
     *
     * @return void
     */
    public function shutdownObject()
    {
        if ($this->temporaryLocalCopyPathAndFilename !== null) {
            unlink($this->temporaryLocalCopyPathAndFilename);
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\ResourceManagement;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Model representing a persistable resource
 * @\Neos\Flow\Annotations\Entity
 */
class PersistentResource extends PersistentResource_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface, \Neos\Flow\Persistence\Aspect\PersistenceMagicInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(length=40)
     * introduced by Neos\Flow\Persistence\Aspect\PersistenceMagicAspect
     */
    protected $Persistence_Object_Identifier = NULL;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'])) {

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct'] = TRUE;
            try {
            
                $methodArguments = [];

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__construct']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\ResourceManagement\PersistentResource', '__construct', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\ResourceManagement\PersistentResource', '__construct', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__construct']);
            return;
        }
        if ('Neos\Flow\ResourceManagement\PersistentResource' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Flow\ResourceManagement\PersistentResource';
        if ($isSameClass) {
            $this->initializeObject(1);
        }

        $isSameClass = get_class($this) === 'Neos\Flow\ResourceManagement\PersistentResource';
        if ($isSameClass) {
        \Neos\Flow\Core\Bootstrap::$staticObjectManager->registerShutdownObject($this, 'shutdownObject');
        }
    }

    /**
     * Autogenerated Proxy Method
     */
    protected function Flow_Aop_Proxy_buildMethodsAndAdvicesArray()
    {
        if (method_exists(get_parent_class(), 'Flow_Aop_Proxy_buildMethodsAndAdvicesArray') && is_callable('parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray')) parent::Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager;
        $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array(
            '__construct' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
                ),
            ),
            '__clone' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'generateUuid', $objectManager, NULL),
                ),
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', 'cloneObject', $objectManager, NULL),
                ),
            ),
        );
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();

        $isSameClass = get_class($this) === 'Neos\Flow\ResourceManagement\PersistentResource';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\ResourceManagement\PersistentResource', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

        if ($isSameClass || $isClassProxy) {
            $this->initializeObject(2);
        }

        $isSameClass = get_class($this) === 'Neos\Flow\ResourceManagement\PersistentResource';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\ResourceManagement\PersistentResource', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

        if ($isSameClass || $isClassProxy) {
        \Neos\Flow\Core\Bootstrap::$staticObjectManager->registerShutdownObject($this, 'shutdownObject');
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __clone()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'])) {
            $result = NULL;

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone'] = TRUE;
            try {
            
                $methodArguments = [];

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\ResourceManagement\PersistentResource', '__clone', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\ResourceManagement\PersistentResource', '__clone', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['__clone']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\ResourceManagement\PersistentResource', '__clone', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['__clone']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
  0 => 'protected',
  1 => 'lifecycleEventsActive',
  2 => 'deleted',
  3 => 'temporaryLocalCopyPathAndFilename',
);
        $propertyVarTags = array (
  'collectionName' => 'string',
  'filename' => 'string',
  'fileSize' => 'integer',
  'relativePublicationPath' => 'string',
  'mediaType' => 'string',
  'sha1' => 'string',
  'md5' => 'string',
  'protected' => 'boolean',
  'lifecycleEventsActive' => 'boolean',
  'deleted' => 'boolean',
  'resourceManager' => 'Neos\\Flow\\ResourceManagement\\ResourceManager',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'environment' => 'Neos\\Flow\\Utility\\Environment',
  'temporaryLocalCopyPathAndFilename' => 'string',
  'Persistence_Object_Identifier' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Utility\Environment', 'Neos\Flow\Utility\Environment', 'environment', 'cce2af5ed9f80b598c497d98c35a5eb3', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Utility\Environment'); });
        $this->Flow_Injected_Properties = array (
  0 => 'resourceManager',
  1 => 'systemLogger',
  2 => 'environment',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/ResourceManagement/PersistentResource.php
#