<?php 
namespace Neos\Flow\Persistence\Generic;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 * A lazy loading variant of \SplObjectStorage
 *
 * @api
 */
class LazySplObjectStorage_Original extends \SplObjectStorage
{
    /**
     * The identifiers of the objects contained in the \SplObjectStorage
     * @var array
     */
    protected $objectIdentifiers = [];

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @param PersistenceManagerInterface $persistenceManager
     * @return void
     */
    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @param array $objectIdentifiers
     */
    public function __construct(array $objectIdentifiers)
    {
        $this->objectIdentifiers = $objectIdentifiers;
    }

    /**
     * Loads the objects this LazySplObjectStorage is supposed to hold.
     *
     * @return void
     */
    protected function initialize()
    {
        if (is_array($this->objectIdentifiers)) {
            foreach ($this->objectIdentifiers as $identifier) {
                try {
                    parent::attach($this->persistenceManager->getObjectByIdentifier($identifier));
                } catch (Exception\InvalidObjectDataException $exception) {
                    // when security query rewriting holds back an object here, we skip it...
                }
            }
            $this->objectIdentifiers = null;
        }
    }

    /**
     * Returns TRUE if the LazySplObjectStorage has been initialized.
     *
     * @return boolean
     */
    public function isInitialized()
    {
        return !is_array($this->objectIdentifiers);
    }


    // Standard SplObjectStorage methods below


    public function addAll($storage)
    {
        $this->initialize();
        parent::addAll($storage);
    }

    public function attach($object, $data = null)
    {
        $this->initialize();
        parent::attach($object, $data);
    }

    public function contains($object)
    {
        $this->initialize();
        return parent::contains($object);
    }

    public function count()
    {
        if (is_array($this->objectIdentifiers)) {
            return count($this->objectIdentifiers);
        } else {
            return parent::count();
        }
    }

    public function current()
    {
        $this->initialize();
        return parent::current();
    }

    public function detach($object)
    {
        $this->initialize();
        parent::detach($object);
    }

    public function getInfo()
    {
        $this->initialize();
        return parent::getInfo();
    }

    public function key()
    {
        $this->initialize();
        return parent::key();
    }

    public function next()
    {
        $this->initialize();
        parent::next();
    }

    public function offsetExists($object)
    {
        $this->initialize();
        return parent::offsetExists($object);
    }

    public function offsetGet($object)
    {
        $this->initialize();
        return parent::offsetGet($object);
    }

    public function offsetSet($object, $data = null)
    {
        $this->initialize();
        parent::offsetSet($object, $data);
    }

    public function offsetUnset($object)
    {
        $this->initialize();
        parent::offsetUnset($object);
    }

    public function removeAll($storage)
    {
        $this->initialize();
        parent::removeAll($storage);
    }

    public function rewind()
    {
        $this->initialize();
        parent::rewind();
    }

    public function setInfo($data)
    {
        $this->initialize();
        parent::setInfo($data);
    }
    public function valid()
    {
        $this->initialize();
        return parent::valid();
    }


    // we don't do those (yet)


    public function serialize()
    {
        throw new \RuntimeException('A LazyLoadingSplObjectStorage instance cannot be serialized.', 1267700868);
    }

    public function unserialize($serialized)
    {
        throw new \RuntimeException('A LazyLoadingSplObjectStorage instance cannot be unserialized.', 1267700870);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Persistence\Generic;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A lazy loading variant of \SplObjectStorage
 */
class LazySplObjectStorage extends LazySplObjectStorage_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param array $objectIdentifiers
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $objectIdentifiers in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Persistence\Generic\LazySplObjectStorage' === get_class($this)) {
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
  'objectIdentifiers' => 'array',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
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
        $this->injectPersistenceManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'));
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Persistence/Generic/LazySplObjectStorage.php
#