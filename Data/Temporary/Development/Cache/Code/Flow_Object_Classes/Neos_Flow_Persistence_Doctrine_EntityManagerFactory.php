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

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\Doctrine\Mapping\Driver\FlowAnnotationDriver;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Flow\Utility\Environment;
use Neos\Utility\Files;

/**
 * EntityManager factory for Doctrine integration
 *
 * @Flow\Scope("singleton")
 */
class EntityManagerFactory_Original
{
    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @Flow\Inject
     * @var Environment
     */
    protected $environment;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * Injects the Flow settings, the persistence part is kept
     * for further use.
     *
     * @param array $settings
     * @return void
     * @throws InvalidConfigurationException
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings['persistence'];
        if (!is_array($this->settings['backendOptions'])) {
            throw new InvalidConfigurationException(sprintf('The Neos.Flow.persistence.backendOptions settings need to be an array, %s given.', gettype($this->settings['backendOptions'])), 1426149224);
        }
        if (!is_array($this->settings['doctrine']['secondLevelCache'])) {
            throw new InvalidConfigurationException(sprintf('The TYPO3.Flow.persistence.doctrine.secondLevelCache settings need to be an array, %s given.', gettype($this->settings['doctrine']['secondLevelCache'])), 1491305513);
        }
    }

    /**
     * Factory method which creates an EntityManager.
     *
     * @return EntityManager
     * @throws InvalidConfigurationException
     */
    public function create()
    {
        $config = new Configuration();
        $config->setClassMetadataFactoryName(Mapping\ClassMetadataFactory::class);

        $eventManager = new EventManager();

        $flowAnnotationDriver = $this->objectManager->get(FlowAnnotationDriver::class);
        $config->setMetadataDriverImpl($flowAnnotationDriver);

        $proxyDirectory = Files::concatenatePaths([$this->environment->getPathToTemporaryDirectory(), 'Doctrine/Proxies']);
        Files::createDirectoryRecursively($proxyDirectory);
        $config->setProxyDir($proxyDirectory);
        $config->setProxyNamespace('Neos\Flow\Persistence\Doctrine\Proxies');
        $config->setAutoGenerateProxyClasses(false);

        // Set default host to 127.0.0.1 if there is no host configured but a dbname
        if (empty($this->settings['backendOptions']['host']) && !empty($this->settings['backendOptions']['dbname']) && empty($this->settings['backendOptions']['unix_socket'])) {
            $this->settings['backendOptions']['host'] = '127.0.0.1';
        }

        // The following code tries to connect first, if that succeeds, all is well. If not, the platform is fetched directly from the
        // driver - without version checks to the database server (to which no connection can be made) - and is added to the config
        // which is then used to create a new connection. This connection will then return the platform directly, without trying to
        // detect the version it runs on, which fails if no connection can be made. But the platform is used even if no connection can
        // be made, which was no problem with Doctrine DBAL 2.3. And then came version-aware drivers and platforms...
        $connection = DriverManager::getConnection($this->settings['backendOptions'], $config, $eventManager);
        try {
            $connection->connect();
        } catch (ConnectionException $exception) {
            $settings = $this->settings['backendOptions'];
            $settings['platform'] = $connection->getDriver()->getDatabasePlatform();
            $connection = DriverManager::getConnection($settings, $config, $eventManager);
        }

        $this->emitBeforeDoctrineEntityManagerCreation($connection, $config, $eventManager);
        $entityManager = EntityManager::create($connection, $config, $eventManager);
        $flowAnnotationDriver->setEntityManager($entityManager);
        $this->emitAfterDoctrineEntityManagerCreation($config, $entityManager);

        return $entityManager;
    }

    /**
     * @param Connection $connection
     * @param Configuration $config
     * @param EventManager $eventManager
     * @Flow\Signal
     */
    public function emitBeforeDoctrineEntityManagerCreation(Connection $connection, Configuration $config, EventManager $eventManager)
    {
    }

    /**
     * @param Configuration $config
     * @param EntityManager $entityManager
     * @Flow\Signal
     */
    public function emitAfterDoctrineEntityManagerCreation(Configuration $config, EntityManager $entityManager)
    {
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Persistence\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * EntityManager factory for Doctrine integration
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class EntityManagerFactory extends EntityManagerFactory_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if (get_class($this) === 'Neos\Flow\Persistence\Doctrine\EntityManagerFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Persistence\Doctrine\EntityManagerFactory', $this);
        if ('Neos\Flow\Persistence\Doctrine\EntityManagerFactory' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
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
            'emitBeforeDoctrineEntityManagerCreation' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
                ),
            ),
            'emitAfterDoctrineEntityManagerCreation' => array(
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
        if (get_class($this) === 'Neos\Flow\Persistence\Doctrine\EntityManagerFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Persistence\Doctrine\EntityManagerFactory', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();
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
     * @param Connection $connection
     * @param Configuration $config
     * @param EventManager $eventManager
     * @\Neos\Flow\Annotations\Signal
     */
    public function emitBeforeDoctrineEntityManagerCreation(\Doctrine\DBAL\Connection $connection, \Doctrine\ORM\Configuration $config, \Doctrine\Common\EventManager $eventManager)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitBeforeDoctrineEntityManagerCreation'])) {
            $result = parent::emitBeforeDoctrineEntityManagerCreation($connection, $config, $eventManager);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitBeforeDoctrineEntityManagerCreation'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['connection'] = $connection;
                $methodArguments['config'] = $config;
                $methodArguments['eventManager'] = $eventManager;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Persistence\Doctrine\EntityManagerFactory', 'emitBeforeDoctrineEntityManagerCreation', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitBeforeDoctrineEntityManagerCreation']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitBeforeDoctrineEntityManagerCreation']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Persistence\Doctrine\EntityManagerFactory', 'emitBeforeDoctrineEntityManagerCreation', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitBeforeDoctrineEntityManagerCreation']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitBeforeDoctrineEntityManagerCreation']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param Configuration $config
     * @param EntityManager $entityManager
     * @\Neos\Flow\Annotations\Signal
     */
    public function emitAfterDoctrineEntityManagerCreation(\Doctrine\ORM\Configuration $config, \Doctrine\ORM\EntityManager $entityManager)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterDoctrineEntityManagerCreation'])) {
            $result = parent::emitAfterDoctrineEntityManagerCreation($config, $entityManager);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterDoctrineEntityManagerCreation'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['config'] = $config;
                $methodArguments['entityManager'] = $entityManager;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Persistence\Doctrine\EntityManagerFactory', 'emitAfterDoctrineEntityManagerCreation', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAfterDoctrineEntityManagerCreation']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAfterDoctrineEntityManagerCreation']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Persistence\Doctrine\EntityManagerFactory', 'emitAfterDoctrineEntityManagerCreation', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterDoctrineEntityManagerCreation']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterDoctrineEntityManagerCreation']);
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
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'environment' => 'Neos\\Flow\\Utility\\Environment',
  'settings' => 'array',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Utility\Environment', 'Neos\Flow\Utility\Environment', 'environment', 'cce2af5ed9f80b598c497d98c35a5eb3', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Utility\Environment'); });
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'objectManager',
  2 => 'reflectionService',
  3 => 'environment',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Persistence/Doctrine/EntityManagerFactory.php
#