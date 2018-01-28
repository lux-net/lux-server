<?php 
namespace Neos\FluidAdaptor\Core\Rendering;

/*
 * This file is part of the Neos.FluidAdaptor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\FluidAdaptor\Core\Cache\CacheAdaptor;
use Neos\FluidAdaptor\Core\Parser\TemplateParser;
use Neos\FluidAdaptor\Core\Parser\TemplateProcessor\EscapingFlagProcessor;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\FluidAdaptor\Core\Parser\Interceptor\ResourceInterceptor;
use Neos\FluidAdaptor\Core\Parser\SyntaxTree\Expression\LegacyNamespaceExpressionNode;
use Neos\FluidAdaptor\Core\Parser\TemplateProcessor\NamespaceDetectionTemplateProcessor;
use Neos\FluidAdaptor\Core\ViewHelper\TemplateVariableContainer;
use Neos\FluidAdaptor\Core\ViewHelper\ViewHelperResolver;
use Neos\FluidAdaptor\View\TemplatePaths;
use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\CastingExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\MathExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext as FluidRenderingContext;
use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * A Fluid rendering context specifically to be used in conjunction with Flow.
 * This knows about the ControllerContext and ObjectManager.
 */
class RenderingContext_Original extends FluidRenderingContext implements FlowAwareRenderingContextInterface
{
    /**
     * List of class names implementing ExpressionNodeInterface
     * which will be consulted when an expression does not match
     * any built-in parser expression types.
     *
     * @var array
     */
    protected $expressionNodeTypes = [
        LegacyNamespaceExpressionNode::class,
        CastingExpressionNode::class,
        MathExpressionNode::class,
        TernaryExpressionNode::class,
    ];

    /**
     * @var ControllerContext
     */
    protected $controllerContext;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var ViewHelperResolver
     */
    protected $viewHelperResolver;

    /**
     * @Flow\Inject
     * @var CacheAdaptor
     */
    protected $cache;

    /**
     * @var Configuration
     */
    protected $parserConfiguration;

    /**
     * RenderingContext constructor.
     *
     * @param ViewInterface $view
     * @param array $options
     */
    public function __construct(ViewInterface $view, array $options = [])
    {
        parent::__construct($view);
        $this->setTemplateParser(new TemplateParser());
        $this->setViewHelperResolver(new ViewHelperResolver());
        $this->setTemplateProcessors([new EscapingFlagProcessor(), new NamespaceDetectionTemplateProcessor()]);
        $this->setTemplatePaths(new TemplatePaths($options));
        $this->setVariableProvider(new TemplateVariableContainer());
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return ControllerContext
     */
    public function getControllerContext()
    {
        return $this->controllerContext;
    }

    /**
     * @param ControllerContext $controllerContext
     */
    public function setControllerContext($controllerContext)
    {
        $this->controllerContext = $controllerContext;
        $request = $controllerContext->getRequest();
        if (!$this->templatePaths instanceof TemplatePaths || !$request instanceof ActionRequest) {
            return;
        }

        $this->templatePaths->setPatternReplacementVariables([
            'packageKey' => $request->getControllerPackageKey(),
            'subPackageKey' => $request->getControllerSubpackageKey(),
            'controllerName' => $request->getControllerName(),
            'action' => $request->getControllerActionName(),
            'format' => $request->getFormat() ?: 'html'
        ]);
    }

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @return \TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface
     * @deprecated use "getVariableProvider"
     */
    public function getTemplateVariableContainer()
    {
        return $this->getVariableProvider();
    }

    /**
     * Build parser configuration
     *
     * @return Configuration
     */
    public function buildParserConfiguration()
    {
        if ($this->parserConfiguration === null) {
            $this->parserConfiguration = parent::buildParserConfiguration();
            $this->parserConfiguration->addInterceptor(new ResourceInterceptor());
        }

        return $this->parserConfiguration;
    }

    /**
     * Set a specific option of this View
     *
     * @param string $optionName
     * @param mixed $value
     * @return void
     * @throws \Neos\Flow\Mvc\Exception
     */
    public function setOption($optionName, $value)
    {
        if ($this->templatePaths instanceof TemplatePaths) {
            $this->templatePaths->setOption($optionName, $value);
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\Core\Rendering;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A Fluid rendering context specifically to be used in conjunction with Flow.
 * This knows about the ControllerContext and ObjectManager.
 */
class RenderingContext extends RenderingContext_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     * @param ViewInterface $view
     * @param array $options
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $view in class ' . __CLASS__ . '. Note that constructor injection is only support for objects of scope singleton (and this is not a singleton) – for other scopes you must pass each required argument to the constructor yourself.', 1296143788);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\FluidAdaptor\Core\Rendering\RenderingContext' === get_class($this)) {
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
  'expressionNodeTypes' => 'array',
  'controllerContext' => 'Neos\\Flow\\Mvc\\Controller\\ControllerContext',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'viewHelperResolver' => 'Neos\\FluidAdaptor\\Core\\ViewHelper\\ViewHelperResolver',
  'cache' => 'Neos\\FluidAdaptor\\Core\\Cache\\CacheAdaptor',
  'parserConfiguration' => 'TYPO3Fluid\\Fluid\\Core\\Parser\\Configuration',
  'variableProvider' => 'TYPO3Fluid\\Fluid\\Core\\Variables\\VariableProviderInterface',
  'viewHelperVariableContainer' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer',
  'viewHelperInvoker' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperInvoker',
  'templatePaths' => 'TYPO3Fluid\\Fluid\\View\\TemplatePaths',
  'controllerName' => 'string',
  'controllerAction' => 'string',
  'view' => 'TYPO3Fluid\\Fluid\\View\\ViewInterface',
  'templateParser' => 'TYPO3Fluid\\Fluid\\Core\\Parser\\TemplateParser',
  'templateCompiler' => 'TYPO3Fluid\\Fluid\\Core\\Compiler\\TemplateCompiler',
  'templateProcessors' => 'TemplateProcessorInterface[]',
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
        $this->setViewHelperResolver(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\FluidAdaptor\Core\ViewHelper\ViewHelperResolver'));
        $this->setCache(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\FluidAdaptor\Core\Cache\CacheAdaptor'));
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'viewHelperResolver',
  2 => 'cache',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.FluidAdaptor/Classes/Core/Rendering/RenderingContext.php
#