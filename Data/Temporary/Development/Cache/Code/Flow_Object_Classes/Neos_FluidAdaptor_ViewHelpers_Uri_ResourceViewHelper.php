<?php 
namespace Neos\FluidAdaptor\ViewHelpers\Uri;

/*
 * This file is part of the Neos.FluidAdaptor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Service;
use Neos\Flow\ResourceManagement\Exception;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper\Exception\InvalidVariableException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * A view helper for creating URIs to resources.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <link href="{f:uri.resource(path: 'CSS/Stylesheet.css')}" rel="stylesheet" />
 * </code>
 * <output>
 * <link href="http://yourdomain.tld/_Resources/Static/YourPackage/CSS/Stylesheet.css" rel="stylesheet" />
 * (depending on current package)
 * </output>
 *
 * <code title="Other package resource">
 * {f:uri.resource(path: 'gfx/SomeImage.png', package: 'DifferentPackage')}
 * </code>
 * <output>
 * http://yourdomain.tld/_Resources/Static/DifferentPackage/gfx/SomeImage.png
 * (depending on domain)
 * </output>
 *
 * <code title="Static resource URI">
 * {f:uri.resource(path: 'resource://DifferentPackage/Public/gfx/SomeImage.png')}
 * </code>
 * <output>
 * http://yourdomain.tld/_Resources/Static/DifferentPackage/gfx/SomeImage.png
 * (depending on domain)
 * </output>
 *
 * <code title="Persistent resource object">
 * <img src="{f:uri.resource(resource: myImage.resource)}" />
 * </code>
 * <output>
 * <img src="http://yourdomain.tld/_Resources/Persistent/69e73da3ce0ad08c717b7b9f1c759182d6650944.jpg" />
 * (depending on your resource object)
 * </output>
 *
 * @api
 */
class ResourceViewHelper_Original extends AbstractViewHelper
{
    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var Service
     */
    protected $i18nService;

    /**
     * Initialize and register all arguments.
     *
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('path', 'string', 'Location of the resource, can be either a path relative to the Public resource directory of the package or a resource://... URI', false, null);
        $this->registerArgument('package', 'string', 'Target package key. If not set, the current package key will be used', false, null);
        $this->registerArgument('resource', PersistentResource::class, 'If specified, this resource object is used instead of the path and package information', false, null);
        $this->registerArgument('localize', 'bool', 'Whether resource localization should be attempted or not.', false, true);
    }

    /**
     * Render the URI to the resource. The filename is used from child content.
     *
     * @return string The absolute URI to the resource
     * @throws InvalidVariableException
     * @api
     */
    public function render()
    {
        return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     * @throws InvalidVariableException
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var ResourceManager $resourceManager */
        $resourceManager = $renderingContext->getObjectManager()->get(ResourceManager::class);

        $resource = $arguments['resource'] ?? null;

        if ($resource !== null) {
            $uri = $resourceManager->getPublicPersistentResourceUri($resource);
            if ($uri === false) {
                $uri = '404-Resource-Not-Found';
            }

            return $uri;
        }

        $path = $arguments['path'] ?? null;
        if ($path === null) {
            throw new InvalidVariableException('The ResourceViewHelper did neither contain a valuable "resource" nor "path" argument.', 1353512742);
        }

        $package = $arguments['package'] ?? null;
        if ($package === null) {
            $controllerContext = $renderingContext->getControllerContext();
            $package = $controllerContext->getRequest()->getControllerPackageKey();
        }
        if (strpos($path, 'resource://') === 0) {
            try {
                list($package, $path) = $resourceManager->getPackageAndPathByPublicPath($path);
            } catch (Exception $exception) {
                throw new InvalidVariableException(sprintf('The specified path "%s" does not point to a public resource.', $path), 1386458851);
            }
        }

        $localize = $arguments['localize'] ?? true;
        if ($localize === true) {
            $i18nService = $renderingContext->getObjectManager()->get(Service::class);
            $resourcePath = 'resource://' . $package . '/Public/' . $path;
            $localizedResourcePathData = $i18nService->getLocalizedFilename($resourcePath);
            $matches = [];
            if (preg_match('#resource://([^/]+)/Public/(.*)#', current($localizedResourcePathData), $matches) === 1) {
                $package = $matches[1];
                $path = $matches[2];
            }
        }

        $uri = $resourceManager->getPublicPackageResourceUri($package, $path);
        return $uri;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\ViewHelpers\Uri;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A view helper for creating URIs to resources.
 * 
 * = Examples =
 * 
 * <code title="Defaults">
 * <link href="{f:uri.resource(path: 'CSS/Stylesheet.css')}" rel="stylesheet" />
 * </code>
 * <output>
 * <link href="http://yourdomain.tld/_Resources/Static/YourPackage/CSS/Stylesheet.css" rel="stylesheet" />
 * (depending on current package)
 * </output>
 * 
 * <code title="Other package resource">
 * {f:uri.resource(path: 'gfx/SomeImage.png', package: 'DifferentPackage')}
 * </code>
 * <output>
 * http://yourdomain.tld/_Resources/Static/DifferentPackage/gfx/SomeImage.png
 * (depending on domain)
 * </output>
 * 
 * <code title="Static resource URI">
 * {f:uri.resource(path: 'resource://DifferentPackage/Public/gfx/SomeImage.png')}
 * </code>
 * <output>
 * http://yourdomain.tld/_Resources/Static/DifferentPackage/gfx/SomeImage.png
 * (depending on domain)
 * </output>
 * 
 * <code title="Persistent resource object">
 * <img src="{f:uri.resource(resource: myImage.resource)}" />
 * </code>
 * <output>
 * <img src="http://yourdomain.tld/_Resources/Persistent/69e73da3ce0ad08c717b7b9f1c759182d6650944.jpg" />
 * (depending on your resource object)
 * </output>
 */
class ResourceViewHelper extends ResourceViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\FluidAdaptor\ViewHelpers\Uri\ResourceViewHelper' === get_class($this)) {
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
  'resourceManager' => 'Neos\\Flow\\ResourceManagement\\ResourceManager',
  'i18nService' => 'Neos\\Flow\\I18n\\Service',
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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ResourceManagement\ResourceManager', 'Neos\Flow\ResourceManagement\ResourceManager', 'resourceManager', '5c4c2fb284addde18c78849a54b02875', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ResourceManagement\ResourceManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\I18n\Service', 'Neos\Flow\I18n\Service', 'i18nService', 'bdcad09aa1b6973b35f2987887987892', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\I18n\Service'); });
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'systemLogger',
  2 => 'resourceManager',
  3 => 'i18nService',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.FluidAdaptor/Classes/ViewHelpers/Uri/ResourceViewHelper.php
#