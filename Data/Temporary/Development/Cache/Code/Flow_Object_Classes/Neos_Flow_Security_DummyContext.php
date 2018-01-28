<?php 
namespace Neos\Flow\Security;

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
use Neos\Flow\Mvc\RequestInterface;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Policy\Role;

/**
 * This is dummy implementation of a security context, which holds
 * security information like roles oder details of authenticated users.
 * These information can be set manually on the context as needed.
 *
 * @Flow\Scope("prototype")
 */
class DummyContext_Original extends Context
{
    /**
     * TRUE if the context is initialized in the current request, FALSE or NULL otherwise.
     *
     * @var boolean
     * @Flow\Transient
     */
    protected $initialized = false;

    /**
     * Array of configured tokens (might have request patterns)
     * @var array
     */
    protected $tokens = [];

    /**
     * @var string
     */
    protected $csrfProtectionToken;

    /**
     * @var RequestInterface
     */
    protected $interceptedRequest;

    /**
     * @Flow\Transient
     * @var Role[]
     */
    protected $roles = null;

    /**
     * @param boolean $initialized
     * @return void
     */
    public function setInitialized($initialized)
    {
        $this->initialized = $initialized;
    }

    /**
     * @return boolean TRUE if the Context is initialized, FALSE otherwise.
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * Get the token authentication strategy
     *
     * @return int One of the AUTHENTICATE_* constants
     */
    public function getAuthenticationStrategy()
    {
        return $this->authenticationStrategy;
    }

    /**
     * Sets the Authentication\Tokens of the security context which should be active.
     *
     * @param TokenInterface[] $tokens Array of set tokens
     * @return array
     */
    public function setAuthenticationTokens(array $tokens)
    {
        return $this->tokens = $tokens;
    }

    /**
     * Returns all Authentication\Tokens of the security context which are
     * active for the current request. If a token has a request pattern that cannot match
     * against the current request it is determined as not active.
     *
     * @return TokenInterface[] Array of set tokens
     */
    public function getAuthenticationTokens()
    {
        return $this->tokens;
    }

    /**
     * Returns all Authentication\Tokens of the security context which are
     * active for the current request and of the given type. If a token has a request pattern that cannot match
     * against the current request it is determined as not active.
     *
     * @param string $className The class name
     * @return TokenInterface[] Array of set tokens of the specified type
     */
    public function getAuthenticationTokensOfType($className)
    {
        $tokens = [];
        foreach ($this->tokens as $token) {
            if ($token instanceof $className) {
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    /**
     * Returns the roles of all authenticated accounts, including inherited roles.
     *
     * If no authenticated roles could be found the "Anonymous" role is returned.
     *
     * The "Neos.Flow:Everybody" roles is always returned.
     *
     * @return Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set an array of role objects.
     *
     * @param Role[] $roles
     * @return void
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * Returns TRUE, if at least one of the currently authenticated accounts holds
     * a role with the given identifier, also recursively.
     *
     * @param string $roleIdentifier The string representation of the role to search for
     * @return boolean TRUE, if a role with the given string representation was found
     */
    public function hasRole($roleIdentifier)
    {
        if ($roleIdentifier === 'Neos.Flow:Everybody') {
            return true;
        }
        if ($roleIdentifier === 'Neos.Flow:Anonymous') {
            return (!empty($this->roles));
        }
        if ($roleIdentifier === 'Neos.Flow:AuthenticatedUser') {
            return (empty($this->roles));
        }

        return isset($this->roles[$roleIdentifier]);
    }

    /**
     * @param string $csrfProtectionToken
     * @return void
     */
    public function setCsrfProtectionToken($csrfProtectionToken)
    {
        $this->csrfProtectionToken = $csrfProtectionToken;
    }

    /**
     * Returns the current CSRF protection token. A new one is created when needed, depending on the  configured CSRF
     * protection strategy.
     *
     * @return string
     */
    public function getCsrfProtectionToken()
    {
        return $this->csrfProtectionToken;
    }

    /**
     * Returns TRUE if the context has CSRF protection tokens.
     *
     * @return boolean TRUE, if the token is valid. FALSE otherwise.
     */
    public function hasCsrfProtectionTokens()
    {
        return isset($this->csrfProtectionToken);
    }

    /**
     * Returns TRUE if the given string is a valid CSRF protection token. The token will be removed if the configured
     * csrf strategy is 'onePerUri'.
     *
     * @param string $csrfToken The token string to be validated
     * @return boolean TRUE, if the token is valid. FALSE otherwise.
     */
    public function isCsrfProtectionTokenValid($csrfToken)
    {
        return ($csrfToken === $this->csrfProtectionToken);
    }

    /**
     * Sets an action request, to be stored for later resuming after it
     * has been intercepted by a security exception.
     *
     * @param ActionRequest $interceptedRequest
     * @return void
     * @Flow\Session(autoStart=true)
     */
    public function setInterceptedRequest(ActionRequest $interceptedRequest = null)
    {
        $this->interceptedRequest = $interceptedRequest;
    }

    /**
     * Returns the request, that has been stored for later resuming after it
     * has been intercepted by a security exception, NULL if there is none.
     *
     * @return ActionRequest
     */
    public function getInterceptedRequest()
    {
        return $this->interceptedRequest;
    }

    /**
     * Clears the security context.
     *
     * @return void
     */
    public function clearContext()
    {
        $this->roles = null;
        $this->tokens = [];
        $this->csrfProtectionToken = null;
        $this->interceptedRequest = null;
        $this->initialized = false;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This is dummy implementation of a security context, which holds
 * security information like roles oder details of authenticated users.
 * These information can be set manually on the context as needed.
 * @\Neos\Flow\Annotations\Scope("prototype")
 */
class DummyContext extends DummyContext_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if ('Neos\Flow\Security\DummyContext' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Flow\Security\DummyContext';
        if ($isSameClass) {
        \Neos\Flow\Core\Bootstrap::$staticObjectManager->registerShutdownObject($this, 'shutdownObject');
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
            'setInterceptedRequest' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'initializeSession', $objectManager, NULL),
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

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();

        $isSameClass = get_class($this) === 'Neos\Flow\Security\DummyContext';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\Security\DummyContext', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

        if ($isSameClass || $isClassProxy) {
        \Neos\Flow\Core\Bootstrap::$staticObjectManager->registerShutdownObject($this, 'shutdownObject');
        }
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
     * @param ActionRequest $interceptedRequest
     * @return void
     * @\Neos\Flow\Annotations\Session(autoStart=true)
     */
    public function setInterceptedRequest(\Neos\Flow\Mvc\ActionRequest $interceptedRequest = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['setInterceptedRequest'])) {
            $result = parent::setInterceptedRequest($interceptedRequest);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['setInterceptedRequest'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['interceptedRequest'] = $interceptedRequest;
            
                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['setInterceptedRequest']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['setInterceptedRequest']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\DummyContext', 'setInterceptedRequest', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\DummyContext', 'setInterceptedRequest', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['setInterceptedRequest']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['setInterceptedRequest']);
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
  0 => 'initialized',
  1 => 'roles',
  2 => 'activeTokens',
  3 => 'inactiveTokens',
  4 => 'request',
  5 => 'authorizationChecksDisabled',
  6 => 'contextHash',
);
        $propertyVarTags = array (
  'initialized' => 'boolean',
  'tokens' => 'array',
  'csrfProtectionToken' => 'string',
  'interceptedRequest' => 'Neos\\Flow\\Mvc\\RequestInterface',
  'roles' => 'Role[]',
  'tokenStatusLabels' => 'array',
  'activeTokens' => 'TokenInterface[]',
  'inactiveTokens' => 'array',
  'authenticationStrategy' => 'integer',
  'request' => 'Neos\\Flow\\Mvc\\ActionRequest',
  'authenticationManager' => 'Neos\\Flow\\Security\\Authentication\\AuthenticationManagerInterface',
  'sessionManager' => 'Neos\\Flow\\Session\\SessionManagerInterface',
  'securityLogger' => 'Neos\\Flow\\Log\\SecurityLoggerInterface',
  'policyService' => 'Neos\\Flow\\Security\\Policy\\PolicyService',
  'hashService' => 'Neos\\Flow\\Security\\Cryptography\\HashService',
  'csrfProtectionStrategy' => 'integer',
  'csrfProtectionTokens' => 'array',
  'authorizationChecksDisabled' => 'boolean',
  'contextHash' => 'string',
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'globalObjects' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectAuthenticationManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authentication\AuthenticationManagerInterface'));
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Session\SessionManagerInterface', 'Neos\Flow\Session\SessionManager', 'sessionManager', '76e58e15e7015ece7292c22da877c6ac', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Session\SessionManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SecurityLoggerInterface', 'Neos\Flow\Log\Logger', 'securityLogger', 'fd9036110dfd050b19e35f0cbc8df678', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SecurityLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Policy\PolicyService', 'Neos\Flow\Security\Policy\PolicyService', 'policyService', '0b7a1e7038c946bf05af316d09b817a3', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Policy\PolicyService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Security\Cryptography\HashService', 'Neos\Flow\Security\Cryptography\HashService', 'hashService', '62d57ff7e7ce903303c867dd468c12fd', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Cryptography\HashService'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->globalObjects = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow.aop.globalObjects');
        $this->Flow_Injected_Properties = array (
  0 => 'authenticationManager',
  1 => 'settings',
  2 => 'sessionManager',
  3 => 'securityLogger',
  4 => 'policyService',
  5 => 'hashService',
  6 => 'objectManager',
  7 => 'globalObjects',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Security/DummyContext.php
#