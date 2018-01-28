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
use Neos\Flow\Security\Account;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * This view helper implements an ifHasRole/else condition.
 *
 * = Examples =
 *
 * <code title="Basic usage">
 * <f:security.ifHasRole role="Administrator">
 *   This is being shown in case you have the Administrator role (aka role) defined in the
 *   current package according to the controllerContext
 * </f:security.ifHasRole>
 * </code>
 *
 * <code title="Usage with packageKey attribute">
 * <f:security.ifHasRole role="Administrator" packageKey="Acme.MyPackage">
 *   This is being shown in case you have the Acme.MyPackage:Administrator role (aka role).
 * </f:security.ifHasRole>
 * </code>
 *
 * <code title="Usage with full role identifier in role attribute">
 * <f:security.ifHasRole role="Acme.MyPackage:Administrator">
 *   This is being shown in case you have the Acme.MyPackage:Administrator role (aka role).
 * </f:security.ifHasRole>
 * </code>
 *
 * Everything inside the <f:ifHasRole> tag is being displayed if you have the given role.
 *
 * <code title="IfRole / then / else">
 * <f:security.ifHasRole role="Administrator">
 *   <f:then>
 *     This is being shown in case you have the role.
 *   </f:then>
 *   <f:else>
 *     This is being displayed in case you do not have the role.
 *   </f:else>
 * </f:security.ifHasRole>
 * </code>
 *
 * Everything inside the "then" tag is displayed if you have the role.
 * Otherwise, everything inside the "else"-tag is displayed.
 *
 * <code title="Usage with role object in role attribute">
 * <f:security.ifHasRole role="{someRoleObject}">
 *   This is being shown in case you have the specified role
 * </f:security.ifHasRole>
 * </code>
 *
 * <code title="Usage with specific account instead of currently logged in account">
 * <f:security.ifHasRole role="Administrator" account="{otherAccount}">
 *   This is being shown in case "otherAccount" has the Acme.MyPackage:Administrator role (aka role).
 * </f:security.ifHasRole>
 * </code>
 *
 *
 * @api
 */
class IfHasRoleViewHelper_Original extends AbstractConditionViewHelper
{
    /**
     * Initializes the "then" and "else" arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('role', 'mixed', 'The role or role identifier.', true);
        $this->registerArgument('packageKey', 'string', 'PackageKey of the package defining the role.', false, null);
        $this->registerArgument('account', Account::class, 'If specified, this subject of this check is the given Account instead of the currently authenticated account', false, null);
        $this->registerArgument('then', 'mixed', 'Value to be returned if the condition if met.', false);
        $this->registerArgument('else', 'mixed', 'Value to be returned if the condition if not met.', false);
    }

    /**
     * renders <f:then> child if the role could be found in the security context,
     * otherwise renders <f:else> child.
     *
     * @return string the rendered string
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
     * @param null $arguments
     * @param RenderingContextInterface $renderingContext
     * @return boolean
     */
    protected static function evaluateCondition($arguments = null, RenderingContextInterface $renderingContext)
    {
        $objectManager = $renderingContext->getObjectManager();
        /** @var PolicyService $policyService */
        $policyService = $objectManager->get(PolicyService::class);
        /** @var Context $securityContext */
        $securityContext = $objectManager->get(Context::class);

        $role = $arguments['role'];
        $account = $arguments['account'];
        $packageKey = isset($arguments['packageKey']) ? $arguments['packageKey'] : $renderingContext->getControllerContext()->getRequest()->getControllerPackageKey();

        if (is_string($role)) {
            $roleIdentifier = $role;

            if (in_array($roleIdentifier, ['Everybody', 'Anonymous', 'AuthenticatedUser'])) {
                $roleIdentifier = 'Neos.Flow:' . $roleIdentifier;
            }

            if (strpos($roleIdentifier, '.') === false && strpos($roleIdentifier, ':') === false) {
                $roleIdentifier = $packageKey . ':' . $roleIdentifier;
            }

            $role = $policyService->getRole($roleIdentifier);
        }

        $hasRole = $securityContext->hasRole($role->getIdentifier());
        if ($account instanceof Account) {
            $hasRole = $account->hasRole($role);
        }

        return $hasRole;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\FluidAdaptor\ViewHelpers\Security;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This view helper implements an ifHasRole/else condition.
 * 
 * = Examples =
 * 
 * <code title="Basic usage">
 * <f:security.ifHasRole role="Administrator">
 *   This is being shown in case you have the Administrator role (aka role) defined in the
 *   current package according to the controllerContext
 * </f:security.ifHasRole>
 * </code>
 * 
 * <code title="Usage with packageKey attribute">
 * <f:security.ifHasRole role="Administrator" packageKey="Acme.MyPackage">
 *   This is being shown in case you have the Acme.MyPackage:Administrator role (aka role).
 * </f:security.ifHasRole>
 * </code>
 * 
 * <code title="Usage with full role identifier in role attribute">
 * <f:security.ifHasRole role="Acme.MyPackage:Administrator">
 *   This is being shown in case you have the Acme.MyPackage:Administrator role (aka role).
 * </f:security.ifHasRole>
 * </code>
 * 
 * Everything inside the <f:ifHasRole> tag is being displayed if you have the given role.
 * 
 * <code title="IfRole / then / else">
 * <f:security.ifHasRole role="Administrator">
 *   <f:then>
 *     This is being shown in case you have the role.
 *   </f:then>
 *   <f:else>
 *     This is being displayed in case you do not have the role.
 *   </f:else>
 * </f:security.ifHasRole>
 * </code>
 * 
 * Everything inside the "then" tag is displayed if you have the role.
 * Otherwise, everything inside the "else"-tag is displayed.
 * 
 * <code title="Usage with role object in role attribute">
 * <f:security.ifHasRole role="{someRoleObject}">
 *   This is being shown in case you have the specified role
 * </f:security.ifHasRole>
 * </code>
 * 
 * <code title="Usage with specific account instead of currently logged in account">
 * <f:security.ifHasRole role="Administrator" account="{otherAccount}">
 *   This is being shown in case "otherAccount" has the Acme.MyPackage:Administrator role (aka role).
 * </f:security.ifHasRole>
 * </code>
 */
class IfHasRoleViewHelper extends IfHasRoleViewHelper_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\FluidAdaptor\ViewHelpers\Security\IfHasRoleViewHelper' === get_class($this)) {
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
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.FluidAdaptor/Classes/ViewHelpers/Security/IfHasRoleViewHelper.php
#