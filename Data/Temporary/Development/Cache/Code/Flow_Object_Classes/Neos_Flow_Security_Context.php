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
use Neos\Cache\CacheAwareInterface;
use Neos\Flow\Log\SecurityLoggerInterface;
use Neos\Flow\Mvc\RequestInterface;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Policy\Role;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Session\SessionManagerInterface;
use Neos\Flow\Utility\Algorithms;
use Neos\Utility\TypeHandling;

/**
 * This is the default implementation of a security context, which holds current
 * security information like roles oder details of authenticated users.
 *
 * @Flow\Scope("session")
 */
class Context_Original
{
    /**
     * Authenticate as many tokens as possible but do not require
     * an authenticated token (e.g. for guest users with role Neos.Flow:Everybody).
     */
    const AUTHENTICATE_ANY_TOKEN = 1;

    /**
     * Stop authentication of tokens after first successful
     * authentication of a token.
     */
    const AUTHENTICATE_ONE_TOKEN = 2;

    /**
     * Authenticate all active tokens and throw an exception if
     * an active token could not be authenticated.
     */
    const AUTHENTICATE_ALL_TOKENS = 3;

    /**
     * Authenticate as many tokens as possible but do not fail if
     * a token could not be authenticated and at least one token
     * could be authenticated.
     */
    const AUTHENTICATE_AT_LEAST_ONE_TOKEN = 4;

    /**
     * Creates one csrf token per session
     */
    const CSRF_ONE_PER_SESSION = 1;

    /**
     * Creates one csrf token per uri
     */
    const CSRF_ONE_PER_URI = 2;

    /**
     * Creates one csrf token per request
     */
    const CSRF_ONE_PER_REQUEST = 3;

    /**
     * If the security context isn't initialized (or authorization checks are disabled)
     * this constant will be returned by getContextHash()
     */
    const CONTEXT_HASH_UNINITIALIZED = '__uninitialized__';

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
     * @var array
     */
    protected $tokenStatusLabels = [
        1 => 'no credentials given',
        2 => 'wrong credentials',
        3 => 'authentication successful',
        4 => 'authentication needed'
    ];

    /**
     * Array of tokens currently active
     * @var TokenInterface[]
     * @Flow\Transient
     */
    protected $activeTokens = [];

    /**
     * Array of tokens currently inactive
     * @var array
     * @Flow\Transient
     */
    protected $inactiveTokens = [];

    /**
     * One of the AUTHENTICATE_* constants to set the authentication strategy.
     * @var integer
     */
    protected $authenticationStrategy = self::AUTHENTICATE_ANY_TOKEN;

    /**
     * @var ActionRequest
     * @Flow\Transient
     */
    protected $request;

    /**
     * @var Authentication\AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @Flow\Inject
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @Flow\Inject
     * @var SecurityLoggerInterface
     */
    protected $securityLogger;

    /**
     * @Flow\Inject
     * @var Policy\PolicyService
     */
    protected $policyService;

    /**
     * @Flow\Inject
     * @var Cryptography\HashService
     */
    protected $hashService;

    /**
     * One of the CSRF_* constants to set the csrf protection strategy
     * @var integer
     */
    protected $csrfProtectionStrategy = self::CSRF_ONE_PER_SESSION;

    /**
     * @var array
     */
    protected $csrfProtectionTokens = [];

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
     * Whether authorization is disabled @see areAuthorizationChecksDisabled()
     *
     * @Flow\Transient
     * @var boolean
     */
    protected $authorizationChecksDisabled = false;

    /**
     * A hash for this security context that is unique to the currently authenticated roles. @see getContextHash()
     *
     * @Flow\Transient
     * @var string
     */
    protected $contextHash = null;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Array of registered global objects that can be accessed as operands
     *
     * @Flow\InjectConfiguration("aop.globalObjects")
     * @var array
     */
    protected $globalObjects = [];

    /**
     * Inject the authentication manager
     *
     * @param Authentication\AuthenticationManagerInterface $authenticationManager The authentication manager
     * @return void
     */
    public function injectAuthenticationManager(Authentication\AuthenticationManagerInterface $authenticationManager)
    {
        $this->authenticationManager = $authenticationManager;
        $this->authenticationManager->setSecurityContext($this);
    }

    /**
     * Lets you switch off authorization checks (CSRF token, policies, content security, ...) for the runtime of $callback
     *
     * Usage:
     * $this->securityContext->withoutAuthorizationChecks(function () use ($accountRepository, $username, $providerName, &$account) {
     *   // this will disable the PersistenceQueryRewritingAspect for this one call
     *   $account = $accountRepository->findActiveByAccountIdentifierAndAuthenticationProviderName($username, $providerName)
     * });
     *
     * @param \Closure $callback
     * @return void
     * @throws \Exception
     */
    public function withoutAuthorizationChecks(\Closure $callback)
    {
        $authorizationChecksAreAlreadyDisabled = $this->authorizationChecksDisabled;
        $this->authorizationChecksDisabled = true;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $callback->__invoke();
        } catch (\Exception $exception) {
            $this->authorizationChecksDisabled = false;
            throw $exception;
        }
        if ($authorizationChecksAreAlreadyDisabled === false) {
            $this->authorizationChecksDisabled = false;
        }
    }

    /**
     * Returns TRUE if authorization should be ignored, otherwise FALSE
     * This is mainly useful to fetch records without Content Security to kick in (e.g. for AuthenticationProviders)
     *
     * @return boolean
     * @see withoutAuthorizationChecks()
     */
    public function areAuthorizationChecksDisabled()
    {
        return $this->authorizationChecksDisabled;
    }

    /**
     * Set the current action request
     *
     * This method is called manually by the request handler which created the HTTP
     * request.
     *
     * @param ActionRequest $request The current ActionRequest
     * @return void
     * @Flow\Autowiring(FALSE)
     */
    public function setRequest(ActionRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Injects the configuration settings
     *
     * @param array $settings
     * @return void
     * @throws Exception
     */
    public function injectSettings(array $settings)
    {
        if (isset($settings['security']['authentication']['authenticationStrategy'])) {
            $authenticationStrategyName = $settings['security']['authentication']['authenticationStrategy'];
            switch ($authenticationStrategyName) {
                case 'allTokens':
                    $this->authenticationStrategy = self::AUTHENTICATE_ALL_TOKENS;
                    break;
                case 'oneToken':
                    $this->authenticationStrategy = self::AUTHENTICATE_ONE_TOKEN;
                    break;
                case 'atLeastOneToken':
                    $this->authenticationStrategy = self::AUTHENTICATE_AT_LEAST_ONE_TOKEN;
                    break;
                case 'anyToken':
                    $this->authenticationStrategy = self::AUTHENTICATE_ANY_TOKEN;
                    break;
                default:
                    throw new Exception('Invalid setting "' . $authenticationStrategyName . '" for security.authentication.authenticationStrategy', 1291043022);
            }
        }

        if (isset($settings['security']['csrf']['csrfStrategy'])) {
            $csrfStrategyName = $settings['security']['csrf']['csrfStrategy'];
            switch ($csrfStrategyName) {
                case 'onePerRequest':
                    $this->csrfProtectionStrategy = self::CSRF_ONE_PER_REQUEST;
                    break;
                case 'onePerSession':
                    $this->csrfProtectionStrategy = self::CSRF_ONE_PER_SESSION;
                    break;
                case 'onePerUri':
                    $this->csrfProtectionStrategy = self::CSRF_ONE_PER_URI;
                    break;
                default:
                    throw new Exception('Invalid setting "' . $csrfStrategyName . '" for security.csrf.csrfStrategy', 1291043024);
            }
        }
    }

    /**
     * Initializes the security context for the given request.
     *
     * @return void
     * @throws Exception
     */
    public function initialize()
    {
        if ($this->initialized === true) {
            return;
        }
        if ($this->canBeInitialized() === false) {
            throw new Exception('The security Context cannot be initialized yet. Please check if it can be initialized with $securityContext->canBeInitialized() before trying to do so.', 1358513802);
        }

        if ($this->csrfProtectionStrategy !== self::CSRF_ONE_PER_SESSION) {
            $this->csrfProtectionTokens = [];
        }

        $this->tokens = $this->mergeTokens($this->authenticationManager->getTokens(), $this->tokens);
        $this->separateActiveAndInactiveTokens();
        $this->updateTokens($this->activeTokens);

        $this->initialized = true;
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
        if ($this->initialized === false) {
            $this->initialize();
        }

        return $this->authenticationStrategy;
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
        if ($this->initialized === false) {
            $this->initialize();
        }

        return $this->activeTokens;
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
        if ($this->initialized === false) {
            $this->initialize();
        }

        $activeTokens = [];
        foreach ($this->activeTokens as $token) {
            if ($token instanceof $className) {
                $activeTokens[] = $token;
            }
        }

        return $activeTokens;
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
        if ($this->initialized === false) {
            $this->initialize();
        }

        if ($this->roles === null) {
            $this->roles = ['Neos.Flow:Everybody' => $this->policyService->getRole('Neos.Flow:Everybody')];

            if ($this->authenticationManager->isAuthenticated() === false) {
                $this->roles['Neos.Flow:Anonymous'] = $this->policyService->getRole('Neos.Flow:Anonymous');
            } else {
                $this->roles['Neos.Flow:AuthenticatedUser'] = $this->policyService->getRole('Neos.Flow:AuthenticatedUser');
                /** @var $token TokenInterface */
                foreach ($this->getAuthenticationTokens() as $token) {
                    if ($token->isAuthenticated() !== true) {
                        continue;
                    }
                    $account = $token->getAccount();
                    if ($account === null) {
                        continue;
                    }
                    if ($account !== null) {
                        $accountRoles = $account->getRoles();
                        /** @var $currentRole Role */
                        foreach ($accountRoles as $currentRole) {
                            if (!in_array($currentRole, $this->roles)) {
                                $this->roles[$currentRole->getIdentifier()] = $currentRole;
                            }
                            /** @var $currentParentRole Role */
                            foreach ($currentRole->getAllParentRoles() as $currentParentRole) {
                                if (!in_array($currentParentRole, $this->roles)) {
                                    $this->roles[$currentParentRole->getIdentifier()] = $currentParentRole;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->roles;
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
            return (!$this->authenticationManager->isAuthenticated());
        }
        if ($roleIdentifier === 'Neos.Flow:AuthenticatedUser') {
            return ($this->authenticationManager->isAuthenticated());
        }

        $roles = $this->getRoles();
        return isset($roles[$roleIdentifier]);
    }

    /**

     * Returns the account of the first authenticated authentication token.
     * Note: There might be a more currently authenticated account in the
     * remaining tokens. If you need them you'll have to fetch them directly
     * from the tokens.
     * (@see getAuthenticationTokens())
     *
     * @return Account The authenticated account
     */
    public function getAccount()
    {
        if ($this->initialized === false) {
            $this->initialize();
        }

        /** @var $token TokenInterface */
        foreach ($this->getAuthenticationTokens() as $token) {
            if ($token->isAuthenticated() === true) {
                return $token->getAccount();
            }
        }
        return null;
    }

    /**
     * Returns an authenticated account for the given provider or NULL if no
     * account was authenticated or no token was registered for the given
     * authentication provider name.
     *
     * @param string $authenticationProviderName Authentication provider name of the account to find
     * @return Account The authenticated account
     */
    public function getAccountByAuthenticationProviderName($authenticationProviderName)
    {
        if ($this->initialized === false) {
            $this->initialize();
        }

        if (isset($this->activeTokens[$authenticationProviderName]) && $this->activeTokens[$authenticationProviderName]->isAuthenticated() === true) {
            return $this->activeTokens[$authenticationProviderName]->getAccount();
        }
        return null;
    }

    /**
     * Returns the current CSRF protection token. A new one is created when needed, depending on the  configured CSRF
     * protection strategy.
     *
     * @return string
     * @Flow\Session(autoStart=true)
     */
    public function getCsrfProtectionToken()
    {
        if ($this->initialized === false) {
            $this->initialize();
        }

        if (count($this->csrfProtectionTokens) === 1 && $this->csrfProtectionStrategy !== self::CSRF_ONE_PER_URI) {
            reset($this->csrfProtectionTokens);
            return key($this->csrfProtectionTokens);
        }
        $newToken = Algorithms::generateRandomToken(16);
        $this->csrfProtectionTokens[$newToken] = true;

        return $newToken;
    }

    /**
     * Returns TRUE if the context has CSRF protection tokens.
     *
     * @return boolean TRUE, if the token is valid. FALSE otherwise.
     */
    public function hasCsrfProtectionTokens()
    {
        return count($this->csrfProtectionTokens) > 0;
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
        if ($this->initialized === false) {
            $this->initialize();
        }

        if (isset($this->csrfProtectionTokens[$csrfToken])) {
            if ($this->csrfProtectionStrategy === self::CSRF_ONE_PER_URI) {
                unset($this->csrfProtectionTokens[$csrfToken]);
            }
            return true;
        }
        return false;
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
        $this->contextHash = null;
        $this->tokens = [];
        $this->activeTokens = [];
        $this->inactiveTokens = [];
        $this->request = null;
        $this->csrfProtectionTokens = [];
        $this->interceptedRequest = null;
        $this->authorizationChecksDisabled = false;
        $this->initialized = false;
    }

    /**
     * Stores all active tokens in $this->activeTokens, all others in $this->inactiveTokens
     *
     * @return void
     */
    protected function separateActiveAndInactiveTokens()
    {
        if ($this->request === null) {
            return;
        }

        /** @var $token TokenInterface */
        foreach ($this->tokens as $token) {
            if ($this->isTokenActive($token)) {
                $this->activeTokens[$token->getAuthenticationProviderName()] = $token;
            } else {
                $this->inactiveTokens[$token->getAuthenticationProviderName()] = $token;
            }
        }
    }

    /**
     * Evaluates any RequestPatterns of the given token to determine whether it is active for the current request
     * - If no RequestPattern is configured for this token, it is active
     * - Otherwise it is active only if at least one configured RequestPattern per type matches the request
     *
     * @param TokenInterface $token
     * @return bool TRUE if the given token is active, otherwise FALSE
     */
    protected function isTokenActive(TokenInterface $token)
    {
        if (!$token->hasRequestPatterns()) {
            return true;
        }
        $requestPatternsByType = [];
        /** @var $requestPattern RequestPatternInterface */
        foreach ($token->getRequestPatterns() as $requestPattern) {
            $patternType = TypeHandling::getTypeForValue($requestPattern);
            if (isset($requestPatternsByType[$patternType]) && $requestPatternsByType[$patternType] === true) {
                continue;
            }
            $requestPatternsByType[$patternType] = $requestPattern->matchRequest($this->request);
        }
        return !in_array(false, $requestPatternsByType, true);
    }

    /**
     * Merges the session and manager tokens. All manager tokens types will be in the result array
     * If a specific type is found in the session this token replaces the one (of the same type)
     * given by the manager.
     *
     * @param array $managerTokens Array of tokens provided by the authentication manager
     * @param array $sessionTokens Array of tokens restored from the session
     * @return array Array of Authentication\TokenInterface objects
     */
    protected function mergeTokens($managerTokens, $sessionTokens)
    {
        $resultTokens = [];

        if (!is_array($managerTokens)) {
            return $resultTokens;
        }

        /** @var $managerToken TokenInterface */
        foreach ($managerTokens as $managerToken) {
            $noCorrespondingSessionTokenFound = true;

            if (!is_array($sessionTokens)) {
                continue;
            }

            /** @var $sessionToken TokenInterface */
            foreach ($sessionTokens as $sessionToken) {
                if ($sessionToken->getAuthenticationProviderName() === $managerToken->getAuthenticationProviderName()) {
                    $session = $this->sessionManager->getCurrentSession();
                    $this->securityLogger->log(
                        sprintf(
                            'Session %s contains auth token %s for provider %s. Status: %s',
                            $session->getId(),
                            get_class($sessionToken),
                            $sessionToken->getAuthenticationProviderName(),
                            $this->tokenStatusLabels[$sessionToken->getAuthenticationStatus()]
                        ), LOG_INFO, null, 'Flow'
                    );

                    $resultTokens[$sessionToken->getAuthenticationProviderName()] = $sessionToken;
                    $noCorrespondingSessionTokenFound = false;
                }
            }

            if ($noCorrespondingSessionTokenFound) {
                $resultTokens[$managerToken->getAuthenticationProviderName()] = $managerToken;
            }
        }

        return $resultTokens;
    }

    /**
     * Updates the token credentials for all tokens in the given array.
     *
     * @param array $tokens Array of authentication tokens the credentials should be updated for
     * @return void
     */
    protected function updateTokens(array $tokens)
    {
        if ($this->request !== null) {
            /** @var $token TokenInterface */
            foreach ($tokens as $token) {
                $token->updateCredentials($this->request);
            }
        }

        $this->roles = null;
        $this->contextHash = null;
    }

    /**
     * Refreshes all active tokens by updating the credentials.
     * This is useful when doing an explicit authentication inside a request.
     *
     * @return void
     */
    public function refreshTokens()
    {
        if ($this->initialized === false) {
            $this->initialize();
        }

        $this->updateTokens($this->activeTokens);
    }

    /**
     * Refreshes the currently effective roles. In fact the roles first level cache
     * is reset and the effective roles get recalculated by calling getRoles().
     *
     * @return void
     */
    public function refreshRoles()
    {
        $this->roles = null;
        $this->contextHash = null;
        $this->getRoles();
    }

    /**
     * Shut the object down
     *
     * @return void
     */
    public function shutdownObject()
    {
        $this->tokens = array_merge($this->inactiveTokens, $this->activeTokens);
        $this->initialized = false;
    }

    /**
     * Check if the securityContext is ready to be initialized. Only after that security will be active.
     *
     * To be able to initialize, there needs to be an ActionRequest available, usually that is
     * provided by the MVC router.
     *
     * @return boolean
     */
    public function canBeInitialized()
    {
        if ($this->request === null) {
            return false;
        }
        return true;
    }

    /**
     * Returns a hash that is unique for the current context, depending on hash components, @see setContextHashComponent()
     *
     * @return string
     */
    public function getContextHash()
    {
        if ($this->areAuthorizationChecksDisabled()) {
            return self::CONTEXT_HASH_UNINITIALIZED;
        }
        if (!$this->isInitialized()) {
            if (!$this->canBeInitialized()) {
                return self::CONTEXT_HASH_UNINITIALIZED;
            }
            $this->initialize();
        }
        if ($this->contextHash === null) {
            $contextHashSoFar = implode('|', array_keys($this->getRoles()));

            $this->withoutAuthorizationChecks(function () use (&$contextHashSoFar) {
                foreach ($this->globalObjects as $globalObjectsRegisteredClassName) {
                    if (is_subclass_of($globalObjectsRegisteredClassName, CacheAwareInterface::class)) {
                        $globalObject = $this->objectManager->get($globalObjectsRegisteredClassName);
                        $contextHashSoFar .= '<' . $globalObject->getCacheEntryIdentifier();
                    }
                }
            });

            $this->contextHash = md5($contextHashSoFar);
        }
        return $this->contextHash;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Security;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This is the default implementation of a security context, which holds current
 * security information like roles oder details of authenticated users.
 * @\Neos\Flow\Annotations\Scope("session")
 */
class Context extends Context_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if (get_class($this) === 'Neos\Flow\Security\Context') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Context', $this);
        if ('Neos\Flow\Security\Context' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Flow\Security\Context';
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
            'getCsrfProtectionToken' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'initializeSession', $objectManager, NULL),
                ),
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'setInterceptedRequest' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'initializeSession', $objectManager, NULL),
                ),
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'withoutAuthorizationChecks' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'areAuthorizationChecksDisabled' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'setRequest' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'initialize' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'isInitialized' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'getAuthenticationStrategy' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'getAuthenticationTokens' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'getAuthenticationTokensOfType' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'getRoles' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'hasRole' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'getAccount' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'getAccountByAuthenticationProviderName' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'hasCsrfProtectionTokens' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'isCsrfProtectionTokenValid' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'getInterceptedRequest' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'clearContext' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'separateActiveAndInactiveTokens' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'isTokenActive' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'mergeTokens' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'updateTokens' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'refreshTokens' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'refreshRoles' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'canBeInitialized' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
                ),
            ),
            'getContextHash' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LazyLoadingAspect', 'callMethodOnOriginalSessionObject', $objectManager, NULL),
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
        if (get_class($this) === 'Neos\Flow\Security\Context') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Security\Context', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;
        if (method_exists(get_parent_class(), '__wakeup') && is_callable('parent::__wakeup')) parent::__wakeup();

        $isSameClass = get_class($this) === 'Neos\Flow\Security\Context';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\Security\Context', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

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
     * @return string
     * @\Neos\Flow\Annotations\Session(autoStart=true)
     */
    public function getCsrfProtectionToken()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getCsrfProtectionToken'])) {
            $result = parent::getCsrfProtectionToken();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getCsrfProtectionToken'] = TRUE;
            try {
            
                $methodArguments = [];

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['getCsrfProtectionToken']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['getCsrfProtectionToken']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'getCsrfProtectionToken', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getCsrfProtectionToken');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'getCsrfProtectionToken', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getCsrfProtectionToken']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getCsrfProtectionToken']);
        }
        return $result;
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
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'setInterceptedRequest', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('setInterceptedRequest');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'setInterceptedRequest', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
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
     * @param \Closure $callback
     * @return void
     * @throws \Exception
     */
    public function withoutAuthorizationChecks(\Closure $callback)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['withoutAuthorizationChecks'])) {
            $result = parent::withoutAuthorizationChecks($callback);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['withoutAuthorizationChecks'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['callback'] = $callback;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('withoutAuthorizationChecks');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'withoutAuthorizationChecks', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['withoutAuthorizationChecks']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['withoutAuthorizationChecks']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return boolean
     */
    public function areAuthorizationChecksDisabled()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['areAuthorizationChecksDisabled'])) {
            $result = parent::areAuthorizationChecksDisabled();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['areAuthorizationChecksDisabled'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('areAuthorizationChecksDisabled');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'areAuthorizationChecksDisabled', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['areAuthorizationChecksDisabled']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['areAuthorizationChecksDisabled']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param ActionRequest $request The current ActionRequest
     * @return void
     * @\Neos\Flow\Annotations\Autowiring(enabled=false)
     */
    public function setRequest(\Neos\Flow\Mvc\ActionRequest $request)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['setRequest'])) {
            $result = parent::setRequest($request);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['setRequest'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['request'] = $request;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('setRequest');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'setRequest', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['setRequest']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['setRequest']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     * @throws Exception
     */
    public function initialize()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initialize'])) {
            $result = parent::initialize();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['initialize'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('initialize');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'initialize', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initialize']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['initialize']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return boolean TRUE if the Context is initialized, FALSE otherwise.
     */
    public function isInitialized()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['isInitialized'])) {
            $result = parent::isInitialized();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['isInitialized'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('isInitialized');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'isInitialized', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['isInitialized']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['isInitialized']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return int One of the AUTHENTICATE_* constants
     */
    public function getAuthenticationStrategy()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAuthenticationStrategy'])) {
            $result = parent::getAuthenticationStrategy();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getAuthenticationStrategy'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getAuthenticationStrategy');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'getAuthenticationStrategy', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAuthenticationStrategy']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAuthenticationStrategy']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return TokenInterface[] Array of set tokens
     */
    public function getAuthenticationTokens()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAuthenticationTokens'])) {
            $result = parent::getAuthenticationTokens();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getAuthenticationTokens'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getAuthenticationTokens');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'getAuthenticationTokens', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAuthenticationTokens']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAuthenticationTokens']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $className The class name
     * @return TokenInterface[] Array of set tokens of the specified type
     */
    public function getAuthenticationTokensOfType($className)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAuthenticationTokensOfType'])) {
            $result = parent::getAuthenticationTokensOfType($className);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getAuthenticationTokensOfType'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['className'] = $className;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getAuthenticationTokensOfType');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'getAuthenticationTokensOfType', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAuthenticationTokensOfType']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAuthenticationTokensOfType']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return Role[]
     */
    public function getRoles()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getRoles'])) {
            $result = parent::getRoles();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getRoles'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getRoles');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'getRoles', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getRoles']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getRoles']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $roleIdentifier The string representation of the role to search for
     * @return boolean TRUE, if a role with the given string representation was found
     */
    public function hasRole($roleIdentifier)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['hasRole'])) {
            $result = parent::hasRole($roleIdentifier);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['hasRole'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['roleIdentifier'] = $roleIdentifier;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('hasRole');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'hasRole', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['hasRole']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['hasRole']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return Account The authenticated account
     */
    public function getAccount()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAccount'])) {
            $result = parent::getAccount();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getAccount'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getAccount');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'getAccount', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAccount']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAccount']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $authenticationProviderName Authentication provider name of the account to find
     * @return Account The authenticated account
     */
    public function getAccountByAuthenticationProviderName($authenticationProviderName)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAccountByAuthenticationProviderName'])) {
            $result = parent::getAccountByAuthenticationProviderName($authenticationProviderName);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getAccountByAuthenticationProviderName'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['authenticationProviderName'] = $authenticationProviderName;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getAccountByAuthenticationProviderName');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'getAccountByAuthenticationProviderName', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAccountByAuthenticationProviderName']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getAccountByAuthenticationProviderName']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return boolean TRUE, if the token is valid. FALSE otherwise.
     */
    public function hasCsrfProtectionTokens()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['hasCsrfProtectionTokens'])) {
            $result = parent::hasCsrfProtectionTokens();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['hasCsrfProtectionTokens'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('hasCsrfProtectionTokens');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'hasCsrfProtectionTokens', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['hasCsrfProtectionTokens']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['hasCsrfProtectionTokens']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $csrfToken The token string to be validated
     * @return boolean TRUE, if the token is valid. FALSE otherwise.
     */
    public function isCsrfProtectionTokenValid($csrfToken)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['isCsrfProtectionTokenValid'])) {
            $result = parent::isCsrfProtectionTokenValid($csrfToken);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['isCsrfProtectionTokenValid'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['csrfToken'] = $csrfToken;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('isCsrfProtectionTokenValid');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'isCsrfProtectionTokenValid', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['isCsrfProtectionTokenValid']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['isCsrfProtectionTokenValid']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return ActionRequest
     */
    public function getInterceptedRequest()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getInterceptedRequest'])) {
            $result = parent::getInterceptedRequest();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getInterceptedRequest'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getInterceptedRequest');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'getInterceptedRequest', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getInterceptedRequest']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getInterceptedRequest']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    public function clearContext()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['clearContext'])) {
            $result = parent::clearContext();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['clearContext'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('clearContext');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'clearContext', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['clearContext']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['clearContext']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    protected function separateActiveAndInactiveTokens()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['separateActiveAndInactiveTokens'])) {
            $result = parent::separateActiveAndInactiveTokens();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['separateActiveAndInactiveTokens'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('separateActiveAndInactiveTokens');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'separateActiveAndInactiveTokens', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['separateActiveAndInactiveTokens']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['separateActiveAndInactiveTokens']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param TokenInterface $token
     * @return bool TRUE if the given token is active, otherwise FALSE
     */
    protected function isTokenActive(\Neos\Flow\Security\Authentication\TokenInterface $token)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['isTokenActive'])) {
            $result = parent::isTokenActive($token);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['isTokenActive'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['token'] = $token;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('isTokenActive');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'isTokenActive', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['isTokenActive']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['isTokenActive']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param array $managerTokens Array of tokens provided by the authentication manager
     * @param array $sessionTokens Array of tokens restored from the session
     * @return array Array of Authentication\TokenInterface objects
     */
    protected function mergeTokens($managerTokens, $sessionTokens)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['mergeTokens'])) {
            $result = parent::mergeTokens($managerTokens, $sessionTokens);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['mergeTokens'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['managerTokens'] = $managerTokens;
                $methodArguments['sessionTokens'] = $sessionTokens;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('mergeTokens');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'mergeTokens', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['mergeTokens']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['mergeTokens']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param array $tokens Array of authentication tokens the credentials should be updated for
     * @return void
     */
    protected function updateTokens(array $tokens)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateTokens'])) {
            $result = parent::updateTokens($tokens);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['updateTokens'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['tokens'] = $tokens;
            
                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('updateTokens');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'updateTokens', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateTokens']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['updateTokens']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    public function refreshTokens()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['refreshTokens'])) {
            $result = parent::refreshTokens();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['refreshTokens'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('refreshTokens');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'refreshTokens', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['refreshTokens']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['refreshTokens']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return void
     */
    public function refreshRoles()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['refreshRoles'])) {
            $result = parent::refreshRoles();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['refreshRoles'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('refreshRoles');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'refreshRoles', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['refreshRoles']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['refreshRoles']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return boolean
     */
    public function canBeInitialized()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['canBeInitialized'])) {
            $result = parent::canBeInitialized();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['canBeInitialized'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('canBeInitialized');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'canBeInitialized', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['canBeInitialized']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['canBeInitialized']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return string
     */
    public function getContextHash()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getContextHash'])) {
            $result = parent::getContextHash();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['getContextHash'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('getContextHash');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Security\Context', 'getContextHash', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getContextHash']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['getContextHash']);
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
  1 => 'activeTokens',
  2 => 'inactiveTokens',
  3 => 'request',
  4 => 'roles',
  5 => 'authorizationChecksDisabled',
  6 => 'contextHash',
);
        $propertyVarTags = array (
  'initialized' => 'boolean',
  'tokens' => 'array',
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
  'interceptedRequest' => 'Neos\\Flow\\Mvc\\RequestInterface',
  'roles' => 'Role[]',
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
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Security/Context.php
#