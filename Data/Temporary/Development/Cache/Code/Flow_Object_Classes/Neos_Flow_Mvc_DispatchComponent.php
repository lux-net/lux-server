<?php 
namespace Neos\Flow\Mvc;

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
use Neos\Flow\Http\Component\ComponentContext;
use Neos\Flow\Http\Component\ComponentInterface;
use Neos\Flow\Http\Request as HttpRequest;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\Property\PropertyMappingConfiguration;
use Neos\Flow\Property\TypeConverter\MediaTypeConverterInterface;
use Neos\Flow\Security\Context;
use Neos\Utility\Arrays;

/**
 * A dispatch component
 */
class DispatchComponent_Original implements ComponentInterface
{
    /**
     * @Flow\Inject(lazy=false)
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @Flow\Inject(lazy=false)
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject(lazy=false)
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * Options of this component
     *
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Create an action request from stored route match values and dispatch to that
     *
     * @param ComponentContext $componentContext
     * @return void
     */
    public function handle(ComponentContext $componentContext)
    {
        $componentContext = $this->prepareActionRequest($componentContext);
        $actionRequest = $componentContext->getParameter(DispatchComponent::class, 'actionRequest');
        $this->setDefaultControllerAndActionNameIfNoneSpecified($actionRequest);
        $this->dispatcher->dispatch($actionRequest, $componentContext->getHttpResponse());
    }

    /**
     * Create ActionRequest with arguments from body and routing merged and add it to the component context.
     *
     * TODO: This could be a separate HTTP component (ActionRequestFactoryComponent) that sits in the chain before the DispatchComponent.
     *
     * @param ComponentContext $componentContext
     * @return ComponentContext
     */
    protected function prepareActionRequest(ComponentContext $componentContext)
    {
        $httpRequest = $componentContext->getHttpRequest();
        $arguments = $httpRequest->getArguments();

        $parsedBody = $this->parseRequestBody($httpRequest);
        if ($parsedBody !== []) {
            $arguments = Arrays::arrayMergeRecursiveOverrule($arguments, $parsedBody);
            $httpRequest = $httpRequest->withParsedBody($parsedBody);
        }


        $routingMatchResults = $componentContext->getParameter(Routing\RoutingComponent::class, 'matchResults');
        if ($routingMatchResults !== null) {
            $arguments = Arrays::arrayMergeRecursiveOverrule($arguments, $routingMatchResults);
        }

        /** @var $actionRequest ActionRequest */
        $actionRequest = $this->objectManager->get(ActionRequest::class, $httpRequest);

        $actionRequest->setArguments($arguments);
        $this->securityContext->setRequest($actionRequest);
        $componentContext->replaceHttpRequest($httpRequest);

        $componentContext->setParameter(DispatchComponent::class, 'actionRequest', $actionRequest);

        return $componentContext;
    }

    /**
     * Parses the request body according to the media type.
     *
     * @param HttpRequest $httpRequest
     * @return array
     */
    protected function parseRequestBody(HttpRequest $httpRequest)
    {
        $requestBody = $httpRequest->getContent();
        if ($requestBody === null || $requestBody === '') {
            return [];
        }

        $mediaTypeConverter = $this->objectManager->get(MediaTypeConverterInterface::class);
        $propertyMappingConfiguration = new PropertyMappingConfiguration();
        $propertyMappingConfiguration->setTypeConverter($mediaTypeConverter);
        $propertyMappingConfiguration->setTypeConverterOption(MediaTypeConverterInterface::class, MediaTypeConverterInterface::CONFIGURATION_MEDIA_TYPE, $httpRequest->getHeader('Content-Type'));
        $arguments = $this->propertyMapper->convert($requestBody, 'array', $propertyMappingConfiguration);

        return $arguments;
    }

    /**
     * Set the default controller and action names if none has been specified.
     *
     * @param ActionRequest $actionRequest
     * @return void
     */
    protected function setDefaultControllerAndActionNameIfNoneSpecified(ActionRequest $actionRequest)
    {
        if ($actionRequest->getControllerName() === null) {
            $actionRequest->setControllerName('Standard');
        }
        if ($actionRequest->getControllerActionName() === null) {
            $actionRequest->setControllerActionName('index');
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Mvc;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A dispatch component
 */
class DispatchComponent extends DispatchComponent_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param array $options
     */
    public function __construct()
    {
        $arguments = func_get_args();
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Mvc\DispatchComponent' === get_class($this)) {
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
  'dispatcher' => 'Neos\\Flow\\Mvc\\Dispatcher',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'propertyMapper' => 'Neos\\Flow\\Property\\PropertyMapper',
  'options' => 'array',
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
        $this->dispatcher = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Dispatcher');
        $this->securityContext = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context');
        $this->objectManager = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface');
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Property\PropertyMapper', 'Neos\Flow\Property\PropertyMapper', 'propertyMapper', '2ab4a1ce2ee31715713d0f207f0ac637', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Property\PropertyMapper'); });
        $this->Flow_Injected_Properties = array (
  0 => 'dispatcher',
  1 => 'securityContext',
  2 => 'objectManager',
  3 => 'propertyMapper',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Mvc/DispatchComponent.php
#