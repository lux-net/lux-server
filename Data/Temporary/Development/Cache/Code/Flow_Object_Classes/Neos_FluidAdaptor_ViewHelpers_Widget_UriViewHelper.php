<?php 
namespace Neos\FluidAdaptor\ViewHelpers\Widget;

/*
 * This script belongs to the Flow framework.                             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the MIT license.                                          *
 *                                                                        */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Cryptography\HashService;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper;
use Neos\FluidAdaptor\Core\Widget\Exception\WidgetContextNotFoundException;
use Neos\FluidAdaptor\Core\Widget\WidgetContext;

/**
 * widget.uri ViewHelper
 * This ViewHelper can be used inside widget templates in order to render URIs pointing to widget actions
 *
 * = Examples =
 *
 * <code>
 * {f:widget.uri(action: 'widgetAction')}
 * </code>
 * <output>
 *  --widget[@action]=widgetAction
 *  (depending on routing setup and current widget)
 * </output>
 *
 * @api
 */
class UriViewHelper_Original extends AbstractViewHelper
{
    /**
     * @Flow\Inject
     * @var HashService
     */
    protected $hashService;

    /**
     * Render the Uri.
     *
     * @param string $action Target action
     * @param array $arguments Arguments
     * @param string $section The anchor to be added to the URI
     * @param string $format The requested format, e.g. ".html"
     * @param boolean $ajax TRUE if the URI should be to an AJAX widget, FALSE otherwise.
     * @param boolean $includeWidgetContext TRUE if the URI should contain the serialized widget context (only useful for stateless AJAX widgets)
     * @return string The rendered link
     * @throws ViewHelper\Exception if $action argument is not specified and $ajax is FALSE
     * @api
     */
    public function render($action = null, $arguments = array(), $section = '', $format = '', $ajax = false, $includeWidgetContext = false)
    {
        if ($ajax === true) {
            return $this->getAjaxUri();
        } else {
            if ($action === null) {
                throw new ViewHelper\Exception('You have to specify the target action when creating a widget URI with the widget.uri ViewHelper', 1357648232);
            }
            return $this->getWidgetUri();
        }
    }

    /**
     * Get the URI for an AJAX Request.
     *
     * @return string the AJAX URI
     * @throws WidgetContextNotFoundException
     */
    protected function getAjaxUri()
    {
        $action = $this->arguments['action'];
        $arguments = $this->arguments['arguments'];

        if ($action === null) {
            $action = $this->controllerContext->getRequest()->getControllerActionName();
        }
        $arguments['@action'] = $action;
        if (strlen($this->arguments['format']) > 0) {
            $arguments['@format'] = $this->arguments['format'];
        }
        /** @var $widgetContext WidgetContext */
        $widgetContext = $this->controllerContext->getRequest()->getInternalArgument('__widgetContext');
        if ($widgetContext === null) {
            throw new WidgetContextNotFoundException('Widget context not found in <f:widget.uri>', 1307450639);
        }
        if ($this->arguments['includeWidgetContext'] === true) {
            $serializedWidgetContext = base64_encode(serialize($widgetContext));
            $arguments['__widgetContext'] = $this->hashService->appendHmac($serializedWidgetContext);
        } else {
            $arguments['__widgetId'] = $widgetContext->getAjaxWidgetIdentifier();
        }
        return '?' . http_build_query($arguments, null, '&');
    }

    /**
     * Get the URI for a non-AJAX Request.
     *
     * @return string the Widget URI
     * @throws ViewHelper\Exception
     * @todo argumentsToBeExcludedFromQueryString does not work yet, needs to be fixed.
     */
    protected function getWidgetUri()
    {
        $uriBuilder = $this->controllerContext->getUriBuilder();

        $argumentsToBeExcludedFromQueryString = array(
            '@package',
            '@subpackage',
            '@controller'
        );

        $uriBuilder
            ->reset()
            ->setSection($this->arguments['section'])
            ->setCreateAbsoluteUri(true)
            ->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString)
            ->setFormat($this->arguments['format']);
        try {
            $uri = $uriBuilder->uriFor($this->arguments['action'], $this->arguments['arguments'], '', '', '');
        } catch (\Exception $exception) {
            throw new ViewHelper\Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
        return $uri;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\ViewHelpers\Widget;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * widget.uri ViewHelper
 * This ViewHelper can be used inside widget templates in order to render URIs pointing to widget actions
 * 
 * = Examples =
 * 
 * <code>
 * {f:widget.uri(action: 'widgetAction')}
 * </code>
 * <output>
 *  --widget[@action]=widgetAction
 *  (depending on routing setup and current widget)
 * </output>
 */
class UriViewHelper extends UriViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\FluidAdaptor\ViewHelpers\Widget\UriViewHelper' === get_class($this)) {
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
  'hashService' => 'Neos\\Flow\\Security\\Cryptography\\HashService',
  'controllerContext' => 'Neos\\Flow\\Mvc\\Controller\\ControllerContext',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'argumentDefinitions' => 'ArgumentDefinition[]',
  'viewHelperNode' => 'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode',
  'arguments' => 'array',
  'childNodes' => 'NodeInterface[] array',
  'templateVariableContainer' => 'TYPO3Fluid\\Fluid\\Core\\Variables\\VariableProviderInterface',
  'renderingContext' => 'TYPO3Fluid\\Fluid\\Core\\Rendering\\RenderingContextInterface',
  'renderChildrenClosure' => '\\Closure',
  'viewHelperVariableContainer' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer',
  'escapeChildren' => 'boolean',
  'escapeOutput' => 'boolean',
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
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->injectSystemLogger(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Cryptography\HashService', 'Neos\Flow\Security\Cryptography\HashService', 'hashService', '62d57ff7e7ce903303c867dd468c12fd', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Cryptography\HashService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'systemLogger',
  2 => 'hashService',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.FluidAdaptor/Classes/ViewHelpers/Widget/UriViewHelper.php
#