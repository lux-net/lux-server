<?php 
namespace Neos\Flow\Persistence\Doctrine;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\ORM\Tools\SchemaTool;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\Persistence\AbstractPersistenceManager;
use Neos\Flow\Persistence\Exception\KnownObjectException;
use Neos\Flow\Persistence\Exception\ObjectValidationFailedException;
use Neos\Flow\Persistence\Exception as PersistenceException;
use Neos\Flow\Persistence\Exception\UnknownObjectException;
use Neos\Flow\Reflection\ClassSchema;
use Neos\Utility\ObjectAccess;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Utility\TypeHandling;
use Neos\Flow\Validation\ValidatorResolver;

/**
 * Flow's Doctrine PersistenceManager
 *
 * @Flow\Scope("singleton")
 * @api
 */
class PersistenceManager_Original extends AbstractPersistenceManager
{
    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @Flow\Inject
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @Flow\Inject
     * @var ValidatorResolver
     */
    protected $validatorResolver;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * Initializes the persistence manager, called by Flow.
     *
     * @return void
     */
    public function initializeObject()
    {
        $this->entityManager->getEventManager()->addEventListener([\Doctrine\ORM\Events::onFlush], $this);
    }

    /**
     * An onFlush event listener used to validate entities upon persistence.
     *
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $eventArgs
     * @return void
     */
    public function onFlush(\Doctrine\ORM\Event\OnFlushEventArgs $eventArgs)
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $entityInsertions = $unitOfWork->getScheduledEntityInsertions();

        $validatedInstancesContainer = new \SplObjectStorage();
        $knownValueObjects = [];
        foreach ($entityInsertions as $entity) {
            $className = TypeHandling::getTypeForValue($entity);
            if ($this->reflectionService->getClassSchema($className)->getModelType() === ClassSchema::MODELTYPE_VALUEOBJECT) {
                $identifier = $this->getIdentifierByObject($entity);

                if (isset($knownValueObjects[$className][$identifier]) || $unitOfWork->getEntityPersister($className)->exists($entity)) {
                    unset($entityInsertions[spl_object_hash($entity)]);
                    continue;
                }

                $knownValueObjects[$className][$identifier] = true;
            }
            $this->validateObject($entity, $validatedInstancesContainer);
        }

        ObjectAccess::setProperty($unitOfWork, 'entityInsertions', $entityInsertions, true);

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->validateObject($entity, $validatedInstancesContainer);
        }
    }

    /**
     * Validates the given object and throws an exception if validation fails.
     *
     * @param object $object
     * @param \SplObjectStorage $validatedInstancesContainer
     * @return void
     * @throws ObjectValidationFailedException
     */
    protected function validateObject($object, \SplObjectStorage $validatedInstancesContainer)
    {
        $className = $this->entityManager->getClassMetadata(get_class($object))->getName();
        $validator = $this->validatorResolver->getBaseValidatorConjunction($className, ['Persistence', 'Default']);
        if ($validator === null) {
            return;
        }

        $validator->setValidatedInstancesContainer($validatedInstancesContainer);
        $validationResult = $validator->validate($object);
        if ($validationResult->hasErrors()) {
            $errorMessages = '';
            $errorCount = 0;
            $allErrors = $validationResult->getFlattenedErrors();
            foreach ($allErrors as $path => $errors) {
                $errorMessages .= $path . ':' . PHP_EOL;
                foreach ($errors as $error) {
                    $errorCount++;
                    $errorMessages .= (string)$error . PHP_EOL;
                }
            }
            throw new ObjectValidationFailedException('An instance of "' . get_class($object) . '" failed to pass validation with ' . $errorCount . ' error(s): ' . PHP_EOL . $errorMessages, 1322585164);
        }
    }

    /**
     * Commits new objects and changes to objects in the current persistence
     * session into the backend
     *
     * @param boolean $onlyWhitelistedObjects If TRUE an exception will be thrown if there are scheduled updates/deletes or insertions for objects that are not "whitelisted" (see AbstractPersistenceManager::whitelistObject())
     * @return void
     * @api
     */
    public function persistAll($onlyWhitelistedObjects = false)
    {
        if ($onlyWhitelistedObjects) {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            /** @var \Doctrine\ORM\UnitOfWork $unitOfWork */
            $unitOfWork->computeChangeSets();
            $objectsToBePersisted = $unitOfWork->getScheduledEntityUpdates() + $unitOfWork->getScheduledEntityDeletions() + $unitOfWork->getScheduledEntityInsertions();
            foreach ($objectsToBePersisted as $object) {
                $this->throwExceptionIfObjectIsNotWhitelisted($object);
            }
        }

        if (!$this->entityManager->isOpen()) {
            $this->systemLogger->log('persistAll() skipped flushing data, the Doctrine EntityManager is closed. Check the logs for error message.', LOG_ERR);
            return;
        }

        /** @var Connection $connection */
        $connection = $this->entityManager->getConnection();
        try {
            if ($connection->ping() === false) {
                $this->systemLogger->log('Reconnecting the Doctrine EntityManager to the persistence backend.', LOG_INFO);
                $connection->close();
                $connection->connect();
            }
        } catch (ConnectionException $exception) {
            $this->systemLogger->logException($exception);
        }

        $this->entityManager->flush();
        $this->emitAllObjectsPersisted();
    }

    /**
     * Clears the in-memory state of the persistence.
     *
     * Managed instances become detached, any fetches will
     * return data directly from the persistence "backend".
     *
     * @return void
     */
    public function clearState()
    {
        parent::clearState();
        $this->entityManager->clear();
    }

    /**
     * Checks if the given object has ever been persisted.
     *
     * @param object $object The object to check
     * @return boolean TRUE if the object is new, FALSE if the object exists in the repository
     * @api
     */
    public function isNewObject($object)
    {
        return ($this->entityManager->getUnitOfWork()->getEntityState($object, \Doctrine\ORM\UnitOfWork::STATE_NEW) === \Doctrine\ORM\UnitOfWork::STATE_NEW);
    }

    /**
     * Returns the (internal) identifier for the object, if it is known to the
     * backend. Otherwise NULL is returned.
     *
     * Note: this returns an identifier even if the object has not been
     * persisted in case of AOP-managed entities. Use isNewObject() if you need
     * to distinguish those cases.
     *
     * @param object $object
     * @return mixed The identifier for the object if it is known, or NULL
     * @api
     * @todo improve try/catch block
     */
    public function getIdentifierByObject($object)
    {
        if (property_exists($object, 'Persistence_Object_Identifier')) {
            $identifierCandidate = ObjectAccess::getProperty($object, 'Persistence_Object_Identifier', true);
            if ($identifierCandidate !== null) {
                return $identifierCandidate;
            }
        }
        if ($this->entityManager->contains($object)) {
            try {
                return current($this->entityManager->getUnitOfWork()->getEntityIdentifier($object));
            } catch (\Doctrine\ORM\ORMException $exception) {
            }
        }
        return null;
    }

    /**
     * Returns the object with the (internal) identifier, if it is known to the
     * backend. Otherwise NULL is returned.
     *
     * @param mixed $identifier
     * @param string $objectType
     * @param boolean $useLazyLoading Set to TRUE if you want to use lazy loading for this object
     * @return object The object for the identifier if it is known, or NULL
     * @throws \RuntimeException
     * @api
     */
    public function getObjectByIdentifier($identifier, $objectType = null, $useLazyLoading = false)
    {
        if ($objectType === null) {
            throw new \RuntimeException('Using only the identifier is not supported by Doctrine 2. Give classname as well or use repository to query identifier.', 1296646103);
        }
        if (isset($this->newObjects[$identifier])) {
            return $this->newObjects[$identifier];
        }
        if ($useLazyLoading === true) {
            return $this->entityManager->getReference($objectType, $identifier);
        } else {
            return $this->entityManager->find($objectType, $identifier);
        }
    }

    /**
     * Return a query object for the given type.
     *
     * @param string $type
     * @return Query
     */
    public function createQueryForType($type)
    {
        return new Query($type);
    }

    /**
     * Adds an object to the persistence.
     *
     * @param object $object The object to add
     * @return void
     * @throws KnownObjectException if the given $object is not new
     * @throws PersistenceException if another error occurs
     * @api
     */
    public function add($object)
    {
        if (!$this->isNewObject($object)) {
            throw new KnownObjectException('The object of type "' . get_class($object) . '" (identifier: "' . $this->getIdentifierByObject($object) . '") which was passed to EntityManager->add() is not a new object. Check the code which adds this entity to the repository and make sure that only objects are added which were not persisted before. Alternatively use update() for updating existing objects."', 1337934295);
        } else {
            try {
                $this->entityManager->persist($object);
            } catch (\Exception $exception) {
                throw new PersistenceException('Could not add object of type "' . get_class($object) . '"', 1337934455, $exception);
            }
        }
    }

    /**
     * Removes an object to the persistence.
     *
     * @param object $object The object to remove
     * @return void
     * @api
     */
    public function remove($object)
    {
        $this->entityManager->remove($object);
    }

    /**
     * Update an object in the persistence.
     *
     * @param object $object The modified object
     * @return void
     * @throws UnknownObjectException if the given $object is new
     * @throws PersistenceException if another error occurs
     * @api
     */
    public function update($object)
    {
        if ($this->isNewObject($object)) {
            throw new UnknownObjectException('The object of type "' . get_class($object) . '" (identifier: "' . $this->getIdentifierByObject($object) . '") which was passed to EntityManager->update() is not a previously persisted object. Check the code which updates this entity and make sure that only objects are updated which were persisted before. Alternatively use add() for persisting new objects."', 1313663277);
        }
        try {
            $this->entityManager->persist($object);
        } catch (\Exception $exception) {
            throw new PersistenceException('Could not merge object of type "' . get_class($object) . '"', 1297778180, $exception);
        }
    }

    /**
     * Returns TRUE, if an active connection to the persistence
     * backend has been established, e.g. entities can be persisted.
     *
     * @return boolean TRUE, if an connection has been established, FALSE if add object will not be persisted by the backend
     * @api
     */
    public function isConnected()
    {
        return $this->entityManager->getConnection()->isConnected();
    }

    /**
     * Called from functional tests, creates/updates database tables and compiles proxies.
     *
     * @return boolean
     */
    public function compile()
    {
        // "driver" is used only for Doctrine, thus we (mis-)use it here
        // additionally, when no path is set, skip this step, assuming no DB is needed
        if ($this->settings['backendOptions']['driver'] !== null && $this->settings['backendOptions']['path'] !== null) {
            $schemaTool = new SchemaTool($this->entityManager);
            if ($this->settings['backendOptions']['driver'] === 'pdo_sqlite') {
                $schemaTool->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
            } else {
                $schemaTool->updateSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
            }

            $proxyFactory = $this->entityManager->getProxyFactory();
            $proxyFactory->generateProxyClasses($this->entityManager->getMetadataFactory()->getAllMetadata());

            $this->systemLogger->log('Doctrine 2 setup finished');
            return true;
        } else {
            $this->systemLogger->log('Doctrine 2 setup skipped, driver and path backend options not set!', LOG_NOTICE);
            return false;
        }
    }

    /**
     * Called after a functional test in Flow, dumps everything in the database.
     *
     * @return void
     */
    public function tearDown()
    {
        // "driver" is used only for Doctrine, thus we (mis-)use it here
        // additionally, when no path is set, skip this step, assuming no DB is needed
        if ($this->settings['backendOptions']['driver'] !== null && $this->settings['backendOptions']['path'] !== null) {
            $this->entityManager->clear();

            $schemaTool = new SchemaTool($this->entityManager);
            $schemaTool->dropDatabase();
            $this->systemLogger->log('Doctrine 2 schema destroyed.', LOG_NOTICE);
        } else {
            $this->systemLogger->log('Doctrine 2 destroy skipped, driver and path backend options not set!', LOG_NOTICE);
        }
    }

    /**
     * Signals that all persistAll() has been executed successfully.
     *
     * @Flow\Signal
     * @return void
     */
    protected function emitAllObjectsPersisted()
    {
    }

    /**
     * Gives feedback if the persistence Manager has unpersisted changes.
     *
     * This is primarily used to inform the user if he tries to save
     * data in an unsafe request.
     *
     * @return boolean
     */
    public function hasUnpersistedChanges()
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $unitOfWork->computeChangeSets();

        if ($unitOfWork->getScheduledEntityInsertions() !== []
            || $unitOfWork->getScheduledEntityUpdates() !== []
            || $unitOfWork->getScheduledEntityDeletions() !== []
            || $unitOfWork->getScheduledCollectionDeletions() !== []
            || $unitOfWork->getScheduledCollectionUpdates() !== []
        ) {
            return true;
        }

        return false;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Persistence\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Flow's Doctrine PersistenceManager
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class PersistenceManager extends PersistenceManager_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Flow\Persistence\Doctrine\PersistenceManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Persistence\Doctrine\PersistenceManager', $this);
        if (get_class($this) === 'Neos\Flow\Persistence\Doctrine\PersistenceManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Persistence\PersistenceManagerInterface', $this);
        parent::__construct();
        if ('Neos\Flow\Persistence\Doctrine\PersistenceManager' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Flow\Persistence\Doctrine\PersistenceManager';
        if ($isSameClass) {
            $this->initializeObject(1);
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
            'emitAllObjectsPersisted' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
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
        if (get_class($this) === 'Neos\Flow\Persistence\Doctrine\PersistenceManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Persistence\Doctrine\PersistenceManager', $this);
        if (get_class($this) === 'Neos\Flow\Persistence\Doctrine\PersistenceManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Persistence\PersistenceManagerInterface', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();

        $isSameClass = get_class($this) === 'Neos\Flow\Persistence\Doctrine\PersistenceManager';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\Persistence\Doctrine\PersistenceManager', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

        if ($isSameClass || $isClassProxy) {
            $this->initializeObject(2);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __clone()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitAllObjectsPersisted()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAllObjectsPersisted'])) {
            $result = parent::emitAllObjectsPersisted();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAllObjectsPersisted'] = TRUE;
            try {
            
                $methodArguments = [];

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'emitAllObjectsPersisted', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAllObjectsPersisted']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAllObjectsPersisted']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'emitAllObjectsPersisted', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAllObjectsPersisted']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAllObjectsPersisted']);
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
);
        $propertyVarTags = array (
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'entityManager' => 'Doctrine\\Common\\Persistence\\ObjectManager',
  'validatorResolver' => 'Neos\\Flow\\Validation\\ValidatorResolver',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'settings' => 'array',
  'newObjects' => 'array',
  'hasUnpersistedChanges' => 'boolean',
  'whitelistedObjects' => '\\SplObjectStorage',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Doctrine\Common\Persistence\ObjectManager', 'Doctrine\Common\Persistence\ObjectManager', 'entityManager', 'b34c32b6d660d4fb8aaafae6c0286b19', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Doctrine\Common\Persistence\ObjectManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Validation\ValidatorResolver', 'Neos\Flow\Validation\ValidatorResolver', 'validatorResolver', 'e992f50de62d81bfe770d5c5f1242621', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Validation\ValidatorResolver'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'systemLogger',
  2 => 'entityManager',
  3 => 'validatorResolver',
  4 => 'reflectionService',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Persistence/Doctrine/PersistenceManager.php
#