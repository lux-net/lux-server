<?php 
namespace Neos\Flow\Security\Authorization;

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
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Security\Exception\AccessDeniedException;
use Neos\Flow\Security\RequestPatternInterface;
use Neos\Flow\Security\RequestPatternResolver;

/**
 * Default Firewall which analyzes the request with a RequestFilter chain.
 *
 * @Flow\Scope("singleton")
 */
class FilterFirewall_Original implements FirewallInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var RequestPatternResolver
     */
    protected $requestPatternResolver = null;

    /**
     * @var InterceptorResolver
     */
    protected $interceptorResolver = null;

    /**
     * @var array<RequestFilter>
     */
    protected $filters = [];

    /**
     * If set to TRUE the firewall will reject any request except the ones explicitly
     * whitelisted by a \Neos\Flow\Security\Authorization\AccessGrantInterceptor
     * @var boolean
     */
    protected $rejectAll = false;

    /**
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager The object manager
     * @param RequestPatternResolver $requestPatternResolver The request pattern resolver
     * @param InterceptorResolver $interceptorResolver The interceptor resolver
     */
    public function __construct(ObjectManagerInterface $objectManager,
                                RequestPatternResolver $requestPatternResolver,
                                InterceptorResolver $interceptorResolver)
    {
        $this->objectManager = $objectManager;
        $this->requestPatternResolver = $requestPatternResolver;
        $this->interceptorResolver = $interceptorResolver;
    }

    /**
     * Injects the configuration settings
     *
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->rejectAll = $settings['security']['firewall']['rejectAll'];
        $this->buildFiltersFromSettings($settings['security']['firewall']['filters']);
    }

    /**
     * Analyzes a request against the configured firewall rules and blocks
     * any illegal request.
     *
     * @param ActionRequest $request The request to be analyzed
     * @return void
     * @throws AccessDeniedException if the
     */
    public function blockIllegalRequests(ActionRequest $request)
    {
        $filterMatched = false;
        /** @var $filter RequestFilter */
        foreach ($this->filters as $filter) {
            if ($filter->filterRequest($request)) {
                $filterMatched = true;
            }
        }
        if ($this->rejectAll && !$filterMatched) {
            throw new AccessDeniedException('The request was blocked, because no request filter explicitly allowed it.', 1216923741);
        }
    }

    /**
     * Sets the internal filters based on the given configuration.
     *
     * @param array $filterSettings The filter settings
     * @return void
     */
    protected function buildFiltersFromSettings(array $filterSettings)
    {
        foreach ($filterSettings as $singleFilterSettings) {
            $patternType = isset($singleFilterSettings['pattern']) ? $singleFilterSettings['pattern'] : $singleFilterSettings['patternType'];
            $patternClassName = $this->requestPatternResolver->resolveRequestPatternClass($patternType);

            $patternOptions = isset($singleFilterSettings['patternOptions']) ? $singleFilterSettings['patternOptions'] : [];
            /** @var $requestPattern RequestPatternInterface */
            $requestPattern = $this->objectManager->get($patternClassName, $patternOptions);

            // The following check needed for backwards compatibility:
            // Previously each pattern had only one option that was set via the setPattern() method. Now options are passed to the constructor.
            if (isset($singleFilterSettings['patternValue']) && is_callable([$requestPattern, 'setPattern'])) {
                $requestPattern->setPattern($singleFilterSettings['patternValue']);
            }
            $interceptor = $this->objectManager->get($this->interceptorResolver->resolveInterceptorClass($singleFilterSettings['interceptor']));

            $this->filters[] = $this->objectManager->get(RequestFilter::class, $requestPattern, $interceptor);
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security\Authorization;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Default Firewall which analyzes the request with a RequestFilter chain.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class FilterFirewall extends FilterFirewall_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param ObjectManagerInterface $objectManager The object manager
     * @param RequestPatternResolver $requestPatternResolver The request pattern resolver
     * @param InterceptorResolver $interceptorResolver The interceptor resolver
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (get_class($this) === 'Neos\Flow\Security\Authorization\FilterFirewall') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authorization\FilterFirewall', $this);
        if (get_class($this) === 'Neos\Flow\Security\Authorization\FilterFirewall') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authorization\FirewallInterface', $this);

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface');
        if (!array_key_exists(1, $arguments)) $arguments[1] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\RequestPatternResolver');
        if (!array_key_exists(2, $arguments)) $arguments[2] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authorization\InterceptorResolver');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $objectManager in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $requestPatternResolver in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $interceptorResolver in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Security\Authorization\FilterFirewall' === get_class($this)) {
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
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'requestPatternResolver' => 'Neos\\Flow\\Security\\RequestPatternResolver',
  'interceptorResolver' => 'Neos\\Flow\\Security\\Authorization\\InterceptorResolver',
  'filters' => 'array<Neos\\Flow\\Security\\Authorization\\RequestFilter>',
  'rejectAll' => 'boolean',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\Security\Authorization\FilterFirewall') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authorization\FilterFirewall', $this);
        if (get_class($this) === 'Neos\Flow\Security\Authorization\FilterFirewall') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authorization\FirewallInterface', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow'));
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Security/Authorization/FilterFirewall.php
#