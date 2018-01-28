<?php 
namespace Neos\FluidAdaptor\ViewHelpers\Security;

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
use Neos\Flow\Security\Authorization\PrivilegeManagerInterface;
use Neos\FluidAdaptor\Core\Rendering\RenderingContext;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * This view helper implements an ifAccess/else condition.
 *
 * = Examples =
 *
 * <code title="Basic usage">
 * <f:security.ifAccess privilegeTarget="somePrivilegeTargetIdentifier">
 *   This is being shown in case you have access to the given privilege
 * </f:security.ifAccess>
 * </code>
 *
 * Everything inside the <f:ifAccess> tag is being displayed if you have access to the given privilege.
 *
 * <code title="IfAccess / then / else">
 * <f:security.ifAccess privilegeTarget="somePrivilegeTargetIdentifier">
 *   <f:then>
 *     This is being shown in case you have access.
 *   </f:then>
 *   <f:else>
 *     This is being displayed in case you do not have access.
 *   </f:else>
 * </f:security.ifAccess>
 * </code>
 *
 * Everything inside the "then" tag is displayed if you have access.
 * Otherwise, everything inside the "else"-tag is displayed.
 *
 * <code title="Inline syntax with privilege parameters">
 * {f:security.ifAccess(privilegeTarget: 'someTarget', parameters: '{param1: \'value1\'}', then: 'has access', else: 'has no access')}
 * </code>
 *
 * @api
 */
class IfAccessViewHelper_Original extends AbstractConditionViewHelper
{

    /**
     * Initializes the "then" and "else" arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('then', 'mixed', 'Value to be returned if the condition if met.', false);
        $this->registerArgument('else', 'mixed', 'Value to be returned if the condition if not met.', false);
        $this->registerArgument('privilegeTarget', 'string', 'Condition expression conforming to Fluid boolean rules', true);
        $this->registerArgument('parameters', 'array', 'Condition expression conforming to Fluid boolean rules', false, []);
    }

    /**
     * renders <f:then> child if access to the given resource is allowed, otherwise renders <f:else> child.
     *
     * @return string the rendered then/else child nodes depending on the access
     * @api
     */
    public function render()
    {
        if (static::evaluateCondition($this->arguments, $this->renderingContext)) {
            return $this->renderThenChild();
        }

        return $this->renderElseChild();
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        return static::renderResult(static::evaluateCondition($arguments, $renderingContext), $arguments, $renderingContext);
    }

    /**
     * @param null $arguments
     * @param RenderingContextInterface $renderingContext
     * @return boolean
     */
    protected static function evaluateCondition($arguments = null, RenderingContextInterface $renderingContext)
    {
        $privilegeManager = static::getPrivilegeManager($renderingContext);
        return $privilegeManager->isPrivilegeTargetGranted($arguments['privilegeTarget'], $arguments['parameters']);
    }

    /**
     * @param RenderingContext $renderingContext
     * @return PrivilegeManagerInterface
     */
    protected static function getPrivilegeManager(RenderingContext $renderingContext)
    {
        $objectManager = $renderingContext->getObjectManager();
        return $objectManager->get(PrivilegeManagerInterface::class);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\ViewHelpers\Security;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This view helper implements an ifAccess/else condition.
 * 
 * = Examples =
 * 
 * <code title="Basic usage">
 * <f:security.ifAccess privilegeTarget="somePrivilegeTargetIdentifier">
 *   This is being shown in case you have access to the given privilege
 * </f:security.ifAccess>
 * </code>
 * 
 * Everything inside the <f:ifAccess> tag is being displayed if you have access to the given privilege.
 * 
 * <code title="IfAccess / then / else">
 * <f:security.ifAccess privilegeTarget="somePrivilegeTargetIdentifier">
 *   <f:then>
 *     This is being shown in case you have access.
 *   </f:then>
 *   <f:else>
 *     This is being displayed in case you do not have access.
 *   </f:else>
 * </f:security.ifAccess>
 * </code>
 * 
 * Everything inside the "then" tag is displayed if you have access.
 * Otherwise, everything inside the "else"-tag is displayed.
 * 
 * <code title="Inline syntax with privilege parameters">
 * {f:security.ifAccess(privilegeTarget: 'someTarget', parameters: '{param1: \'value1\'}', then: 'has access', else: 'has no access')}
 * </code>
 */
class IfAccessViewHelper extends IfAccessViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\FluidAdaptor\ViewHelpers\Security\IfAccessViewHelper' === get_class($this)) {
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
  'escapeOutput' => 'boolean',
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
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'systemLogger',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.FluidAdaptor/Classes/ViewHelpers/Security/IfAccessViewHelper.php
#