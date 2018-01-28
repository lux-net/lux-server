<?php 
namespace Neos\Flow\Security\Authorization\Interceptor;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\EntityNotFoundException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Security\Authentication\AuthenticationManagerInterface;
use Neos\Flow\Security\Authorization\Privilege\Method\MethodPrivilegeInterface;
use Neos\Flow\Security\Authorization\Privilege\Method\MethodPrivilegeSubject;
use Neos\Flow\Security\Authorization\PrivilegeManagerInterface;
use Neos\Flow\Security\Authorization\InterceptorInterface;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Exception\AccessDeniedException;
use Neos\Flow\Security\Exception\AuthenticationRequiredException;
use Neos\Flow\Security\Exception\NoTokensAuthenticatedException;

/**
 * This is the main security interceptor, which enforces the current security policy and is usually called by the central security aspect:
 *
 * 1. If authentication has not been performed (flag is set in the security context) the configured authentication manager is called to authenticate its tokens
 * 2. If a AuthenticationRequired exception has been thrown we look for an authentication entry point in the active tokens to redirect to authentication
 * 3. Then the configured AccessDecisionManager is called to authorize the request/action
 *
 * @Flow\Scope("singleton")
 */
class PolicyEnforcement_Original implements InterceptorInterface
{
    /**
     * @var Context
     */
    protected $securityContext;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var PrivilegeManagerInterface
     */
    protected $privilegeManager;

    /**
     * The current joinpoint
     *
     * @var JoinPointInterface
     */
    protected $joinPoint;

    /**
     * @param Context $securityContext The current security context
     * @param AuthenticationManagerInterface $authenticationManager The authentication manager
     * @param PrivilegeManagerInterface $privilegeManager The access decision manager
     */
    public function __construct(Context $securityContext, AuthenticationManagerInterface $authenticationManager, PrivilegeManagerInterface $privilegeManager)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->privilegeManager = $privilegeManager;
    }

    /**
     * Sets the current joinpoint for this interception
     *
     * @param JoinPointInterface $joinPoint The current joinpoint
     * @return void
     */
    public function setJoinPoint(JoinPointInterface $joinPoint)
    {
        $this->joinPoint = $joinPoint;
    }

    /**
     * Invokes the security interception
     *
     * @return boolean TRUE if the security checks was passed
     * @throws AccessDeniedException
     * @throws AuthenticationRequiredException if an entity could not be found (assuming it is bound to the current session), causing a redirect to the authentication entrypoint
     * @throws NoTokensAuthenticatedException if no tokens could be found and the accessDecisionManager denied access to the privilege target, causing a redirect to the authentication entrypoint
     */
    public function invoke()
    {
        $reason = '';

        $privilegeSubject = new MethodPrivilegeSubject($this->joinPoint);

        try {
            $this->authenticationManager->authenticate();
        } catch (EntityNotFoundException $exception) {
            throw new AuthenticationRequiredException('Could not authenticate. Looks like a broken session.', 1358971444, $exception);
        } catch (NoTokensAuthenticatedException $noTokensAuthenticatedException) {
            // We still need to check if the privilege is available to "Neos.Flow:Everybody".
            if ($this->privilegeManager->isGranted(MethodPrivilegeInterface::class, $privilegeSubject, $reason) === false) {
                throw new NoTokensAuthenticatedException($noTokensAuthenticatedException->getMessage() . chr(10) . $reason, $noTokensAuthenticatedException->getCode());
            }
        }

        if ($this->privilegeManager->isGranted(MethodPrivilegeInterface::class, $privilegeSubject, $reason) === false) {
            throw new AccessDeniedException($this->renderDecisionReasonMessage($reason), 1222268609);
        }
    }

    /**
     * Returns a string message, giving insights what happened during privilege evaluation.
     *
     * @param string $privilegeReasonMessage
     * @return string
     */
    protected function renderDecisionReasonMessage($privilegeReasonMessage)
    {
        if (count($this->securityContext->getRoles()) === 0) {
            $rolesMessage = 'No authenticated roles';
        } else {
            $rolesMessage = 'Authenticated roles: ' . implode(', ', array_keys($this->securityContext->getRoles()));
        }

        return sprintf('Access denied for method' . chr(10) . 'Method: %s::%s()' . chr(10) . chr(10) . '%s' . chr(10) . chr(10) . '%s', $this->joinPoint->getClassName(), $this->joinPoint->getMethodName(), $privilegeReasonMessage, $rolesMessage);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security\Authorization\Interceptor;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This is the main security interceptor, which enforces the current security policy and is usually called by the central security aspect:
 * 
 * 1. If authentication has not been performed (flag is set in the security context) the configured authentication manager is called to authenticate its tokens
 * 2. If a AuthenticationRequired exception has been thrown we look for an authentication entry point in the active tokens to redirect to authentication
 * 3. Then the configured AccessDecisionManager is called to authorize the request/action
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class PolicyEnforcement extends PolicyEnforcement_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     * @param Context $securityContext The current security context
     * @param AuthenticationManagerInterface $authenticationManager The authentication manager
     * @param PrivilegeManagerInterface $privilegeManager The access decision manager
     */
    public function __construct()
    {
        $arguments = func_get_args();
        if (get_class($this) === 'Neos\Flow\Security\Authorization\Interceptor\PolicyEnforcement') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authorization\Interceptor\PolicyEnforcement', $this);

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context');
        if (!array_key_exists(1, $arguments)) $arguments[1] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authentication\AuthenticationManagerInterface');
        if (!array_key_exists(2, $arguments)) $arguments[2] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authorization\PrivilegeManagerInterface');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $securityContext in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $authenticationManager in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        if (!array_key_exists(2, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $privilegeManager in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
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
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'authenticationManager' => 'Neos\\Flow\\Security\\Authentication\\AuthenticationManagerInterface',
  'privilegeManager' => 'Neos\\Flow\\Security\\Authorization\\PrivilegeManagerInterface',
  'joinPoint' => 'Neos\\Flow\\Aop\\JoinPointInterface',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\Security\Authorization\Interceptor\PolicyEnforcement') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authorization\Interceptor\PolicyEnforcement', $this);

        $this->Flow_setRelatedEntities();
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Security/Authorization/Interceptor/PolicyEnforcement.php
#