<?php 
namespace Neos\Flow\Security\Authentication;

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
use Neos\Flow\Log\SecurityLoggerInterface;
use Neos\Flow\Security\Authentication\Token\SessionlessTokenInterface;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Exception\NoTokensAuthenticatedException;
use Neos\Flow\Security\Exception\AuthenticationRequiredException;
use Neos\Flow\Security\Exception;
use Neos\Flow\Security\RequestPatternInterface;
use Neos\Flow\Security\RequestPatternResolver;
use Neos\Flow\Session\SessionInterface;

/**
 * The default authentication manager, which relies on Authentication Providers
 * to authenticate the tokens stored in the security context.
 *
 * @Flow\Scope("singleton")
 */
class AuthenticationProviderManager_Original implements AuthenticationManagerInterface
{
    /**
     * @Flow\Inject
     * @var SecurityLoggerInterface
     */
    protected $securityLogger;

    /**
     * @var SessionInterface
     * @Flow\Inject
     */
    protected $session;

    /**
     * The provider resolver
     *
     * @var AuthenticationProviderResolver
     */
    protected $providerResolver;

    /**
     * The security context of the current request
     *
     * @var Context
     */
    protected $securityContext;

    /**
     * The request pattern resolver
     *
     * @var RequestPatternResolver
     */
    protected $requestPatternResolver;

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $tokens = [];

    /**
     * @var boolean
     */
    protected $isAuthenticated = null;

    /**
     * @param AuthenticationProviderResolver $providerResolver The provider resolver
     * @param RequestPatternResolver $requestPatternResolver The request pattern resolver
     */
    public function __construct(AuthenticationProviderResolver $providerResolver, RequestPatternResolver $requestPatternResolver)
    {
        $this->providerResolver = $providerResolver;
        $this->requestPatternResolver = $requestPatternResolver;
    }

    /**
     * Inject the settings and does a fresh build of tokens based on the injected settings
     *
     * @param array $settings The settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        if (!isset($settings['security']['authentication']['providers']) || !is_array($settings['security']['authentication']['providers'])) {
            return;
        }

        $this->buildProvidersAndTokensFromConfiguration($settings['security']['authentication']['providers']);
    }

    /**
     * Sets the security context
     *
     * @param Context $securityContext The security context of the current request
     * @return void
     */
    public function setSecurityContext(Context $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * Returns the security context
     *
     * @return Context $securityContext The security context of the current request
     */
    public function getSecurityContext()
    {
        return $this->securityContext;
    }

    /**
     * Returns clean tokens this manager is responsible for.
     * Note: The order of the tokens in the array is important, as the tokens will be authenticated in the given order.
     *
     * @return array Array of TokenInterface this manager is responsible for
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Returns all configured authentication providers
     *
     * @return array Array of \Neos\Flow\Security\Authentication\AuthenticationProviderInterface
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Tries to authenticate the tokens in the security context (in the given order)
     * with the available authentication providers, if needed.
     * If the authentication strategy is set to "allTokens", all tokens have to be authenticated.
     * If the strategy is set to "oneToken", only one token needs to be authenticated, but the
     * authentication will stop after the first authenticated token. The strategy
     * "atLeastOne" will try to authenticate at least one and as many tokens as possible.
     *
     * @return void
     * @throws Exception
     * @throws AuthenticationRequiredException
     */
    public function authenticate()
    {
        $this->isAuthenticated = false;
        $anyTokenAuthenticated = false;

        if ($this->securityContext === null) {
            throw new Exception('Cannot authenticate because no security context has been set.', 1232978667);
        }

        $tokens = $this->securityContext->getAuthenticationTokens();
        if (count($tokens) === 0) {
            throw new NoTokensAuthenticatedException('The security context contained no tokens which could be authenticated.', 1258721059);
        }

        /** @var $token TokenInterface */
        foreach ($tokens as $token) {
            /** @var $provider AuthenticationProviderInterface */
            foreach ($this->providers as $provider) {
                if ($provider->canAuthenticate($token) && $token->getAuthenticationStatus() === TokenInterface::AUTHENTICATION_NEEDED) {
                    $provider->authenticate($token);
                    if ($token->isAuthenticated()) {
                        $this->emitAuthenticatedToken($token);
                    }
                    break;
                }
            }
            if ($token->isAuthenticated()) {
                if (!$token instanceof SessionlessTokenInterface) {
                    if (!$this->session->isStarted()) {
                        $this->session->start();
                    }
                    $account = $token->getAccount();
                    if ($account !== null) {
                        $this->securityContext->withoutAuthorizationChecks(function () use ($account) {
                            $this->session->addTag('Neos-Flow-Security-Account-' . md5($account->getAccountIdentifier()));
                        });
                    }
                }
                if ($this->securityContext->getAuthenticationStrategy() === Context::AUTHENTICATE_ONE_TOKEN) {
                    $this->isAuthenticated = true;
                    $this->securityContext->refreshRoles();
                    return;
                }
                $anyTokenAuthenticated = true;
            } else {
                if ($this->securityContext->getAuthenticationStrategy() === Context::AUTHENTICATE_ALL_TOKENS) {
                    throw new AuthenticationRequiredException('Could not authenticate all tokens, but authenticationStrategy was set to "all".', 1222203912);
                }
            }
        }

        if (!$anyTokenAuthenticated && $this->securityContext->getAuthenticationStrategy() !== Context::AUTHENTICATE_ANY_TOKEN) {
            throw new NoTokensAuthenticatedException('Could not authenticate any token. Might be missing or wrong credentials or no authentication provider matched.', 1222204027);
        }

        $this->isAuthenticated = $anyTokenAuthenticated;
        $this->securityContext->refreshRoles();
    }

    /**
     * Checks if one or all tokens are authenticated (depending on the authentication strategy).
     *
     * Will call authenticate() if not done before.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        if ($this->isAuthenticated === null) {
            try {
                $this->authenticate();
            } catch (AuthenticationRequiredException $exception) {
            }
        }
        return $this->isAuthenticated;
    }

    /**
     * Logout all active authentication tokens
     *
     * @return void
     */
    public function logout()
    {
        if ($this->isAuthenticated() !== true) {
            return;
        }
        $this->isAuthenticated = null;
        /** @var $token TokenInterface */
        foreach ($this->securityContext->getAuthenticationTokens() as $token) {
            $token->setAuthenticationStatus(TokenInterface::NO_CREDENTIALS_GIVEN);
        }
        $this->emitLoggedOut();
        if ($this->session->isStarted()) {
            $this->session->destroy('Logout through AuthenticationProviderManager');
        }
        $this->securityContext->refreshTokens();
    }

    /**
     * Signals that the specified token has been successfully authenticated.
     *
     * @param TokenInterface $token The token which has been authenticated
     * @return void
     * @Flow\Signal
     */
    protected function emitAuthenticatedToken(TokenInterface $token)
    {
    }

    /**
     * Signals that all active authentication tokens have been invalidated.
     * Note: the session will be destroyed after this signal has been emitted.
     *
     * @return void
     * @Flow\Signal
     */
    protected function emitLoggedOut()
    {
    }

    /**
     * Builds the provider and token objects based on the given configuration
     *
     * @param array $providerConfigurations The configured provider settings
     * @return void
     * @throws Exception\InvalidAuthenticationProviderException
     * @throws Exception\NoEntryPointFoundException
     */
    protected function buildProvidersAndTokensFromConfiguration(array $providerConfigurations)
    {
        foreach ($providerConfigurations as $providerName => $providerConfiguration) {
            if (isset($providerConfiguration['providerClass'])) {
                throw new Exception\InvalidAuthenticationProviderException('The configured authentication provider "' . $providerName . '" uses the deprecated option "providerClass". Check your settings and use the new option "provider" instead.', 1327672030);
            }
            if (isset($providerConfiguration['options'])) {
                throw new Exception\InvalidAuthenticationProviderException('The configured authentication provider "' . $providerName . '" uses the deprecated option "options". Check your settings and use the new option "providerOptions" instead.', 1327672031);
            }
            if (!is_array($providerConfiguration) || !isset($providerConfiguration['provider'])) {
                throw new Exception\InvalidAuthenticationProviderException('The configured authentication provider "' . $providerName . '" needs a "provider" option!', 1248209521);
            }

            $providerObjectName = $this->providerResolver->resolveProviderClass((string)$providerConfiguration['provider']);
            if ($providerObjectName === null) {
                throw new Exception\InvalidAuthenticationProviderException('The configured authentication provider "' . $providerConfiguration['provider'] . '" could not be found!', 1237330453);
            }
            $providerOptions = [];
            if (isset($providerConfiguration['providerOptions']) && is_array($providerConfiguration['providerOptions'])) {
                $providerOptions = $providerConfiguration['providerOptions'];
            }

            /** @var $providerInstance AuthenticationProviderInterface */
            $providerInstance = new $providerObjectName($providerName, $providerOptions);
            $this->providers[$providerName] = $providerInstance;

            /** @var $tokenInstance TokenInterface */
            $tokenInstance = null;
            foreach ($providerInstance->getTokenClassNames() as $tokenClassName) {
                if (isset($providerConfiguration['token']) && $providerConfiguration['token'] !== $tokenClassName) {
                    continue;
                }

                $tokenInstance = new $tokenClassName();
                $tokenInstance->setAuthenticationProviderName($providerName);
                $this->tokens[] = $tokenInstance;
                break;
            }

            if (isset($providerConfiguration['requestPatterns']) && is_array($providerConfiguration['requestPatterns'])) {
                $requestPatterns = [];
                foreach ($providerConfiguration['requestPatterns'] as $patternName => $patternConfiguration) {
                    // skip request patterns that are set to NULL (i.e. `somePattern: ~` in a YAML file)
                    if ($patternConfiguration === null) {
                        continue;
                    }

                    // The following check is needed for backwards compatibility:
                    // Previously the request pattern configuration was just a key/value where the value was passed to the setPattern() method
                    if (is_string($patternConfiguration)) {
                        $patternType = $patternName;
                        $patternOptions = [];
                    } else {
                        $patternType = $patternConfiguration['pattern'];
                        $patternOptions = isset($patternConfiguration['patternOptions']) ? $patternConfiguration['patternOptions'] : [];
                    }
                    $patternClassName = $this->requestPatternResolver->resolveRequestPatternClass($patternType);
                    $requestPattern = new $patternClassName($patternOptions);
                    if (!$requestPattern instanceof RequestPatternInterface) {
                        throw new Exception\InvalidRequestPatternException(sprintf('Invalid request pattern configuration in setting "Neos:Flow:security:authentication:providers:%s": Class "%s" does not implement RequestPatternInterface', $providerName, $patternClassName), 1446222774);
                    }

                    // The following check needed for backwards compatibility:
                    // Previously each pattern had only one option that was set via the setPattern() method. Now options are passed to the constructor.
                    if (is_string($patternConfiguration) && is_callable([$requestPattern, 'setPattern'])) {
                        $requestPattern->setPattern($patternConfiguration);
                    }
                    $requestPatterns[] = $requestPattern;
                }
                if ($tokenInstance !== null) {
                    $tokenInstance->setRequestPatterns($requestPatterns);
                }
            }

            if (isset($providerConfiguration['entryPoint'])) {
                if (is_array($providerConfiguration['entryPoint'])) {
                    $message = 'Invalid entry point configuration in setting "Neos:Flow:security:authentication:providers:' . $providerName . '. Check your settings and make sure to specify only one entry point for each provider.';
                    throw new Exception\InvalidAuthenticationProviderException($message, 1327671458);
                }
                $entryPointName = $providerConfiguration['entryPoint'];
                $entryPointClassName = $entryPointName;
                if (!class_exists($entryPointClassName)) {
                    $entryPointClassName = 'Neos\Flow\Security\Authentication\EntryPoint\\' . $entryPointClassName;
                }
                if (!class_exists($entryPointClassName)) {
                    throw new Exception\NoEntryPointFoundException('An entry point with the name: "' . $entryPointName . '" could not be resolved. Make sure it is a valid class name, either fully qualified or relative to Neos\Flow\Security\Authentication\EntryPoint!', 1236767282);
                }

                /** @var $entryPoint EntryPointInterface */
                $entryPoint = new $entryPointClassName();
                if (isset($providerConfiguration['entryPointOptions'])) {
                    $entryPoint->setOptions($providerConfiguration['entryPointOptions']);
                }

                $tokenInstance->setAuthenticationEntryPoint($entryPoint);
            }
        }
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security\Authentication;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The default authentication manager, which relies on Authentication Providers
 * to authenticate the tokens stored in the security context.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class AuthenticationProviderManager extends AuthenticationProviderManager_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     * @param AuthenticationProviderResolver $providerResolver The provider resolver
     * @param RequestPatternResolver $requestPatternResolver The request pattern resolver
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Flow\Security\Authentication\AuthenticationProviderManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authentication\AuthenticationProviderManager', $this);
        if (get_class($this) === 'Neos\Flow\Security\Authentication\AuthenticationProviderManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authentication\AuthenticationManagerInterface', $this);

        if (!array_key_exists(0, $arguments)) $arguments[0] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Authentication\AuthenticationProviderResolver');
        if (!array_key_exists(1, $arguments)) $arguments[1] = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\RequestPatternResolver');
        if (!array_key_exists(0, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $providerResolver in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        if (!array_key_exists(1, $arguments)) throw new \Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException('Missing required constructor argument $requestPatternResolver in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Security\Authentication\AuthenticationProviderManager' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
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
            'authenticate' => array(
                'Neos\Flow\Aop\Advice\AfterAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterAdvice('Neos\Flow\Security\Aspect\LoggingAspect', 'logManagerAuthenticate', $objectManager, NULL),
                ),
            ),
            'logout' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\Security\Aspect\LoggingAspect', 'logManagerLogout', $objectManager, NULL),
                ),
            ),
            'emitAuthenticatedToken' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
                ),
            ),
            'emitLoggedOut' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
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
        if (get_class($this) === 'Neos\Flow\Security\Authentication\AuthenticationProviderManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authentication\AuthenticationProviderManager', $this);
        if (get_class($this) === 'Neos\Flow\Security\Authentication\AuthenticationProviderManager') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Authentication\AuthenticationManagerInterface', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();
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
     * @return void
     * @throws Exception
     * @throws AuthenticationRequiredException
     */
    public function authenticate()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticate'])) {
            $result = parent::authenticate();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticate'] = TRUE;
            try {
            
                $methodArguments = [];

        $result = NULL;
        $afterAdviceInvoked = FALSE;
        try {

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'authenticate', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['authenticate']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['authenticate']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'authenticate', $methodArguments, NULL, $result);
                    $afterAdviceInvoked = TRUE;
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {

                if (!$afterAdviceInvoked && isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['authenticate']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['authenticate']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'authenticate', $methodArguments, NULL, NULL, $exception);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }
                }

                throw $exception;
        }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticate']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['authenticate']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    public function logout()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['logout'])) {
            $result = parent::logout();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['logout'] = TRUE;
            try {
            
                $methodArguments = [];

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'logout', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['logout']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['logout']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'logout', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['logout']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['logout']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param TokenInterface $token The token which has been authenticated
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitAuthenticatedToken(\Neos\Flow\Security\Authentication\TokenInterface $token)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAuthenticatedToken'])) {
            $result = parent::emitAuthenticatedToken($token);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAuthenticatedToken'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['token'] = $token;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'emitAuthenticatedToken', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAuthenticatedToken']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAuthenticatedToken']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'emitAuthenticatedToken', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAuthenticatedToken']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAuthenticatedToken']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitLoggedOut()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitLoggedOut'])) {
            $result = parent::emitLoggedOut();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitLoggedOut'] = TRUE;
            try {
            
                $methodArguments = [];

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'emitLoggedOut', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitLoggedOut']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitLoggedOut']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Authentication\AuthenticationProviderManager', 'emitLoggedOut', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitLoggedOut']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitLoggedOut']);
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
);
        $propertyVarTags = array (
  'securityLogger' => 'Neos\\Flow\\Log\\SecurityLoggerInterface',
  'session' => 'Neos\\Flow\\Session\\SessionInterface',
  'providerResolver' => 'Neos\\Flow\\Security\\Authentication\\AuthenticationProviderResolver',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'requestPatternResolver' => 'Neos\\Flow\\Security\\RequestPatternResolver',
  'providers' => 'array',
  'tokens' => 'array',
  'isAuthenticated' => 'boolean',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SecurityLoggerInterface', 'Neos\Flow\Log\Logger', 'securityLogger', 'fd9036110dfd050b19e35f0cbc8df678', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SecurityLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Session\SessionInterface', '', 'session', 'eab532cfba6bf3e4baebe124cda8f0cf', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Session\SessionInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'securityLogger',
  2 => 'session',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Security/Authentication/AuthenticationProviderManager.php
#