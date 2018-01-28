<?php 
namespace Neos\Flow\Cache;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Cache\Backend\BackendInterface;
use Neos\Cache\Backend\SimpleFileBackend;
use Neos\Cache\CacheFactoryInterface;
use Neos\Cache\EnvironmentConfiguration;
use Neos\Cache\Frontend\FrontendInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cache\Backend\AbstractBackend as FlowAbstractBackend;
use Neos\Flow\Cache\Backend\FlowSpecificBackendInterface;
use Neos\Cache\Exception\InvalidBackendException;
use Neos\Flow\Core\ApplicationContext;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Utility\Environment;

/**
 * This cache factory takes care of instantiating a cache frontend and injecting
 * a certain cache backend. In a Flow context you should use the CacheManager to
 * get a Cache.
 *
 * @Flow\Scope("singleton")
 * @api
 */
class CacheFactory_Original extends \Neos\Cache\CacheFactory implements CacheFactoryInterface
{
    /**
     * The current Flow context ("Production", "Development" etc.)
     *
     * @var ApplicationContext
     */
    protected $context;

    /**
     * A reference to the cache manager
     *
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @var EnvironmentConfiguration
     */
    protected $environmentConfiguration;

    /**
     * @param CacheManager $cacheManager
     * @Flow\Autowiring(enabled=false)
     */
    public function injectCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param EnvironmentConfiguration $environmentConfiguration
     * @Flow\Autowiring(enabled=false)
     */
    public function injectEnvironmentConfiguration(EnvironmentConfiguration $environmentConfiguration)
    {
        $this->environmentConfiguration = $environmentConfiguration;
    }

    /**
     * Constructs this cache factory
     *
     * @param ApplicationContext $context The current Flow context
     * @param Environment $environment
     * @Flow\Autowiring(enabled=false)
     */
    public function __construct(ApplicationContext $context, Environment $environment)
    {
        $this->context = $context;
        $this->environment = $environment;

        $environmentConfiguration = new EnvironmentConfiguration(
            FLOW_PATH_ROOT . '~' . (string)$environment->getContext(),
            $environment->getPathToTemporaryDirectory()
        );

        parent::__construct($environmentConfiguration);
    }

    /**
     * @param string $cacheIdentifier
     * @param string $cacheObjectName
     * @param string $backendObjectName
     * @param array $backendOptions
     * @param bool $persistent
     * @return FrontendInterface
     */
    public function create($cacheIdentifier, $cacheObjectName, $backendObjectName, array $backendOptions = [], $persistent = false): FrontendInterface
    {
        $backend = $this->instantiateBackend($backendObjectName, $backendOptions, $persistent);
        $cache = $this->instantiateCache($cacheIdentifier, $cacheObjectName, $backend);
        $backend->setCache($cache);

        return $cache;
    }

    /**
     * {@inheritdoc}
     */
    protected function instantiateCache(string $cacheIdentifier, string $cacheObjectName, BackendInterface $backend): FrontendInterface
    {
        $cache = parent::instantiateCache($cacheIdentifier, $cacheObjectName, $backend);

        if (is_callable([$cache, 'initializeObject'])) {
            $cache->initializeObject(ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED);
        }

        return $cache;
    }

    /**
     * @param string $backendObjectName
     * @param array $backendOptions
     * @param boolean $persistent
     * @return FlowAbstractBackend|BackendInterface
     * @throws InvalidBackendException
     */
    protected function instantiateBackend(string $backendObjectName, array $backendOptions, bool $persistent = false): BackendInterface
    {
        if (
            $persistent &&
            is_a($backendObjectName, SimpleFileBackend::class, true) &&
            (!isset($backendOptions['cacheDirectory']) || $backendOptions['cacheDirectory'] === '') &&
            (!isset($backendOptions['baseDirectory']) || $backendOptions['baseDirectory'] === '')
        ) {
            $backendOptions['baseDirectory'] = FLOW_PATH_DATA . 'Persistent/';
        }

        if (is_a($backendObjectName, FlowSpecificBackendInterface::class, true)) {
            return $this->instantiateFlowSpecificBackend($backendObjectName, $backendOptions);
        }

        return parent::instantiateBackend($backendObjectName, $backendOptions);
    }

    /**
     * @param string $backendObjectName
     * @param array $backendOptions
     * @return FlowAbstractBackend
     * @throws InvalidBackendException
     */
    protected function instantiateFlowSpecificBackend(string $backendObjectName, array $backendOptions)
    {
        $backend = new $backendObjectName($this->context, $backendOptions);

        if (!$backend instanceof BackendInterface) {
            throw new InvalidBackendException('"' . $backendObjectName . '" is not a valid cache backend object.', 1216304301);
        }

        /** @var FlowAbstractBackend $backend */
        $backend->injectEnvironment($this->environment);

        if (is_callable([$backend, 'injectCacheManager'])) {
            $backend->injectCacheManager($this->cacheManager);
        }
        if (is_callable([$backend, 'initializeObject'])) {
            $backend->initializeObject(ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED);
        }

        return $backend;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Cache;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This cache factory takes care of instantiating a cache frontend and injecting
 * a certain cache backend. In a Flow context you should use the CacheManager to
 * get a Cache.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class CacheFactory extends CacheFactory_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param ApplicationContext $context The current Flow context
     * @param Environment $environment
     * @\Neos\Flow\Annotations\Autowiring(enabled=false)
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (get_class($this) === 'Neos\Flow\Cache\CacheFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Cache\CacheFactory', $this);
        if (get_class($this) === 'Neos\Flow\Cache\CacheFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Cache\CacheFactoryInterface', $this);

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->getSettingsByPath(explode('.', 'Neos.Flow.context'));
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $context in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $environment in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        call_user_func_array('parent::__construct', $arguments);
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
  'context' => 'Neos\\Flow\\Core\\ApplicationContext',
  'cacheManager' => 'Neos\\Flow\\Cache\\CacheManager',
  'environment' => 'Neos\\Flow\\Utility\\Environment',
  'environmentConfiguration' => 'Neos\\Cache\\EnvironmentConfiguration',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\Cache\CacheFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Cache\CacheFactory', $this);
        if (get_class($this) === 'Neos\Flow\Cache\CacheFactory') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Cache\CacheFactoryInterface', $this);

        $this->Flow_setRelatedEntities();
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Cache/CacheFactory.php
#