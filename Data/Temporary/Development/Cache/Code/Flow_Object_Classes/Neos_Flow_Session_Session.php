<?php 
namespace Neos\Flow\Session;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Cache\Backend\IterableBackendInterface;
use Neos\Cache\Exception\InvalidBackendException;
use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\ObjectManagement\Configuration\Configuration as ObjectConfiguration;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\ObjectManagement\Proxy\ProxyInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Context;
use Neos\Flow\Utility\Algorithms;
use Neos\Flow\Http\HttpRequestHandlerInterface;
use Neos\Flow\Http;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Cache\Frontend\FrontendInterface;

/**
 * A modular session implementation based on the caching framework.
 *
 * You may access the currently active session in userland code. In order to do this,
 * inject SessionInterface and NOT just the Session object.
 * The former will be a unique instance (singleton) representing the current session
 * while the latter would be a completely new session instance!
 *
 * You can use the Session Manager for accessing sessions which are not currently
 * active.
 *
 * Note that Flow's bootstrap (that is, Neos\Flow\Core\Scripts) will try to resume
 * a possibly existing session automatically. If a session could be resumed during
 * that phase already, calling start() at a later stage will be a no-operation.
 *
 * @see SessionManager
 */
class Session_Original implements SessionInterface
{
    const TAG_PREFIX = 'customtag-';

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var SystemLoggerInterface
     * @Flow\Inject
     */
    protected $systemLogger;

    /**
     * Meta data cache for this session
     *
     * @Flow\Inject
     * @var VariableFrontend
     */
    protected $metaDataCache;

    /**
     * Storage cache for this session
     *
     * @Flow\Inject
     * @var VariableFrontend
     */
    protected $storageCache;

    /**
     * Bootstrap for retrieving the current HTTP request
     *
     * @Flow\Inject
     * @var Bootstrap
     */
    protected $bootstrap;

    /**
     * @var string
     */
    protected $sessionCookieName;

    /**
     * @var integer
     */
    protected $sessionCookieLifetime = 0;

    /**
     * @var string
     */
    protected $sessionCookieDomain;

    /**
     * @var string
     */
    protected $sessionCookiePath;

    /**
     * @var boolean
     */
    protected $sessionCookieSecure = true;

    /**
     * @var boolean
     */
    protected $sessionCookieHttpOnly = true;

    /**
     * @var Http\Cookie
     */
    protected $sessionCookie;

    /**
     * @var integer
     */
    protected $inactivityTimeout;

    /**
     * @var integer
     */
    protected $lastActivityTimestamp;

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var integer
     */
    protected $now;

    /**
     * @var float
     */
    protected $garbageCollectionProbability;

    /**
     * @var integer
     */
    protected $garbageCollectionMaximumPerRun;

    /**
     * The session identifier
     *
     * @var string
     */
    protected $sessionIdentifier;

    /**
     * Internal identifier used for storing session data in the cache
     *
     * @var string
     */
    protected $storageIdentifier;

    /**
     * If this session has been started
     *
     * @var boolean
     */
    protected $started = false;

    /**
     * If this session is remote or the "current" session
     *
     * @var boolean
     */
    protected $remote = false;

    /**
     * @var Http\Request
     */
    protected $request;

    /**
     * @var Http\Response
     */
    protected $response;

    /**
     * Constructs this session
     *
     * If $sessionIdentifier is specified, this constructor will create a session
     * instance representing a remote session. In that case $storageIdentifier and
     * $lastActivityTimestamp are also required arguments.
     *
     * Session instances MUST NOT be created manually! They should be retrieved via
     * the Session Manager or through dependency injection (use SessionInterface!).
     *
     * @param string $sessionIdentifier The public session identifier which is also used in the session cookie
     * @param string $storageIdentifier The private storage identifier which is used for storage cache entries
     * @param integer $lastActivityTimestamp Unix timestamp of the last known activity for this session
     * @param array $tags A list of tags set for this session
     * @throws \InvalidArgumentException
     */
    public function __construct($sessionIdentifier = null, $storageIdentifier = null, $lastActivityTimestamp = null, array $tags = [])
    {
        if ($sessionIdentifier !== null) {
            if ($storageIdentifier === null || $lastActivityTimestamp === null) {
                throw new \InvalidArgumentException('Session requires a storage identifier and last activity timestamp for remote sessions.', 1354045988);
            }
            $this->sessionIdentifier = $sessionIdentifier;
            $this->storageIdentifier = $storageIdentifier;
            $this->lastActivityTimestamp = $lastActivityTimestamp;
            $this->started = true;
            $this->remote = true;
            $this->tags = $tags;
        }
        $this->now = time();
    }

    /**
     * Injects the Flow settings
     *
     * @param array $settings Settings of the Flow package
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->sessionCookieName = $settings['session']['name'];
        $this->sessionCookieLifetime = (integer)$settings['session']['cookie']['lifetime'];
        $this->sessionCookieDomain = $settings['session']['cookie']['domain'];
        $this->sessionCookiePath = $settings['session']['cookie']['path'];
        $this->sessionCookieSecure = (boolean)$settings['session']['cookie']['secure'];
        $this->sessionCookieHttpOnly = (boolean)$settings['session']['cookie']['httponly'];
        $this->garbageCollectionProbability = $settings['session']['garbageCollection']['probability'];
        $this->garbageCollectionMaximumPerRun = $settings['session']['garbageCollection']['maximumPerRun'];
        $this->inactivityTimeout = (integer)$settings['session']['inactivityTimeout'];
    }

    /**
     * @return void
     * @throws InvalidBackendException
     */
    public function initializeObject()
    {
        if (!$this->metaDataCache->getBackend() instanceof IterableBackendInterface) {
            throw new InvalidBackendException(sprintf('The session meta data cache must provide a backend implementing the IterableBackendInterface, but the given backend "%s" does not implement it.', get_class($this->metaDataCache->getBackend())), 1370964557);
        }
        if (!$this->storageCache->getBackend() instanceof IterableBackendInterface) {
            throw new InvalidBackendException(sprintf('The session storage cache must provide a backend implementing the IterableBackendInterface, but the given backend "%s" does not implement it.', get_class($this->storageCache->getBackend())), 1370964558);
        }
    }

    /**
     * Tells if the session has been started already.
     *
     * @return boolean
     * @api
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Tells if the session is local (the current session bound to the current HTTP
     * request) or remote (retrieved through the Session Manager).
     *
     * @return boolean TRUE if the session is remote, FALSE if this is the current session
     * @api
     */
    public function isRemote()
    {
        return $this->remote;
    }

    /**
     * Starts the session, if it has not been already started
     *
     * @return void
     * @api
     * @throws Exception\InvalidRequestHandlerException
     */
    public function start()
    {
        if ($this->request === null) {
            $requestHandler = $this->bootstrap->getActiveRequestHandler();
            if (!$requestHandler instanceof HttpRequestHandlerInterface) {
                throw new Exception\InvalidRequestHandlerException('Could not start a session because the currently active request handler (%s) is not an HTTP Request Handler.', 1364367520);
            }
            $this->initializeHttpAndCookie($requestHandler);
        }
        if ($this->started === false) {
            $this->sessionIdentifier = Algorithms::generateRandomString(32);
            $this->storageIdentifier = Algorithms::generateUUID();
            $this->sessionCookie = new Http\Cookie($this->sessionCookieName, $this->sessionIdentifier, 0, $this->sessionCookieLifetime, $this->sessionCookieDomain, $this->sessionCookiePath, $this->sessionCookieSecure, $this->sessionCookieHttpOnly);
            $this->response->setCookie($this->sessionCookie);
            $this->lastActivityTimestamp = $this->now;
            $this->started = true;

            $this->writeSessionMetaDataCacheEntry();
        }
    }

    /**
     * Returns TRUE if there is a session that can be resumed.
     *
     * If a to-be-resumed session was inactive for too long, this function will
     * trigger the expiration of that session. An expired session cannot be resumed.
     *
     * NOTE that this method does a bit more than the name implies: Because the
     * session info data needs to be loaded, this method stores this data already
     * so it doesn't have to be loaded again once the session is being used.
     *
     * @return boolean
     * @api
     */
    public function canBeResumed()
    {
        if ($this->request === null) {
            $this->initializeHttpAndCookie($this->bootstrap->getActiveRequestHandler());
        }
        if ($this->sessionCookie === null || $this->request === null || $this->started === true) {
            return false;
        }
        $sessionMetaData = $this->metaDataCache->get($this->sessionCookie->getValue());
        if ($sessionMetaData === false) {
            return false;
        }
        $this->lastActivityTimestamp = $sessionMetaData['lastActivityTimestamp'];
        $this->storageIdentifier = $sessionMetaData['storageIdentifier'];
        $this->tags = $sessionMetaData['tags'];
        return !$this->autoExpire();
    }

    /**
     * Resumes an existing session, if any.
     *
     * @return integer If a session was resumed, the inactivity of since the last request is returned
     * @api
     */
    public function resume()
    {
        if ($this->started === false && $this->canBeResumed()) {
            $this->sessionIdentifier = $this->sessionCookie->getValue();
            $this->response->setCookie($this->sessionCookie);
            $this->started = true;

            $sessionObjects = $this->storageCache->get($this->storageIdentifier . md5('Neos_Flow_Object_ObjectManager'));
            if (is_array($sessionObjects)) {
                foreach ($sessionObjects as $object) {
                    if ($object instanceof ProxyInterface) {
                        $objectName = $this->objectManager->getObjectNameByClassName(get_class($object));
                        if ($this->objectManager->getScope($objectName) === ObjectConfiguration::SCOPE_SESSION) {
                            $this->objectManager->setInstance($objectName, $object);
                            $this->objectManager->get(Aspect\LazyLoadingAspect::class)->registerSessionInstance($objectName, $object);
                        }
                    }
                }
            } else {
                // Fallback for some malformed session data, if it is no array but something else.
                // In this case, we reset all session objects (graceful degradation).
                $this->storageCache->set($this->storageIdentifier . md5('Neos_Flow_Object_ObjectManager'), [], [$this->storageIdentifier], 0);
            }

            $lastActivitySecondsAgo = ($this->now - $this->lastActivityTimestamp);
            $this->lastActivityTimestamp = $this->now;
            return $lastActivitySecondsAgo;
        }
    }

    /**
     * Returns the current session identifier
     *
     * @return string The current session identifier
     * @throws Exception\SessionNotStartedException
     * @api
     */
    public function getId()
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('Tried to retrieve the session identifier, but the session has not been started yet.)', 1351171517);
        }
        return $this->sessionIdentifier;
    }

    /**
     * Generates and propagates a new session ID and transfers all existing data
     * to the new session.
     *
     * @return string The new session ID
     * @throws Exception\SessionNotStartedException
     * @throws Exception\OperationNotSupportedException
     * @api
     */
    public function renewId()
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('Tried to renew the session identifier, but the session has not been started yet.', 1351182429);
        }
        if ($this->remote === true) {
            throw new Exception\OperationNotSupportedException(sprintf('Tried to renew the session identifier on a remote session (%s).', $this->sessionIdentifier), 1354034230);
        }

        $this->removeSessionMetaDataCacheEntry($this->sessionIdentifier);
        $this->sessionIdentifier = Algorithms::generateRandomString(32);
        $this->writeSessionMetaDataCacheEntry();

        $this->sessionCookie->setValue($this->sessionIdentifier);
        return $this->sessionIdentifier;
    }

    /**
     * Returns the data associated with the given key.
     *
     * @param string $key An identifier for the content stored in the session.
     * @return mixed The contents associated with the given key
     * @throws Exception\SessionNotStartedException
     */
    public function getData($key)
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('Tried to get session data, but the session has not been started yet.', 1351162255);
        }
        return $this->storageCache->get($this->storageIdentifier . md5($key));
    }

    /**
     * Returns TRUE if a session data entry $key is available.
     *
     * @param string $key Entry identifier of the session data
     * @return boolean
     * @throws Exception\SessionNotStartedException
     */
    public function hasKey($key)
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('Tried to check a session data entry, but the session has not been started yet.', 1352488661);
        }
        return $this->storageCache->has($this->storageIdentifier . md5($key));
    }

    /**
     * Stores the given data under the given key in the session
     *
     * @param string $key The key under which the data should be stored
     * @param mixed $data The data to be stored
     * @return void
     * @throws Exception\DataNotSerializableException
     * @throws Exception\SessionNotStartedException
     * @api
     */
    public function putData($key, $data)
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('Tried to create a session data entry, but the session has not been started yet.', 1351162259);
        }
        if (is_resource($data)) {
            throw new Exception\DataNotSerializableException('The given data cannot be stored in a session, because it is of type "' . gettype($data) . '".', 1351162262);
        }
        $this->storageCache->set($this->storageIdentifier . md5($key), $data, [$this->storageIdentifier], 0);
    }

    /**
     * Returns the unix time stamp marking the last point in time this session has
     * been in use.
     *
     * For the current (local) session, this method will always return the current
     * time. For a remote session, the unix timestamp will be returned.
     *
     * @return integer unix timestamp
     * @throws Exception\SessionNotStartedException
     * @api
     */
    public function getLastActivityTimestamp()
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('Tried to retrieve the last activity timestamp of a session which has not been started yet.', 1354290378);
        }
        return $this->lastActivityTimestamp;
    }

    /**
     * Tags this session with the given tag.
     *
     * Note that third-party libraries might also tag your session. Therefore it is
     * recommended to use namespaced tags such as "Acme-Demo-MySpecialTag".
     *
     * @param string $tag The tag – must match be a valid cache frontend tag
     * @return void
     * @throws Exception\SessionNotStartedException
     * @throws \InvalidArgumentException
     * @api
     */
    public function addTag($tag)
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('Tried to tag a session which has not been started yet.', 1355143533);
        }
        if (!$this->metaDataCache->isValidTag($tag)) {
            throw new \InvalidArgumentException(sprintf('The tag used for tagging session %s contained invalid characters. Make sure it matches this regular expression: "%s"', $this->sessionIdentifier, FrontendInterface::PATTERN_TAG));
        }
        if (!in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
    }

    /**
     * Removes the specified tag from this session.
     *
     * @param string $tag The tag – must match be a valid cache frontend tag
     * @return void
     * @throws Exception\SessionNotStartedException
     * @api
     */
    public function removeTag($tag)
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('Tried to tag a session which has not been started yet.', 1355150140);
        }
        $index = array_search($tag, $this->tags);
        if ($index !== false) {
            unset($this->tags[$index]);
        }
    }


    /**
     * Returns the tags this session has been tagged with.
     *
     * @return array The tags or an empty array if there aren't any
     * @throws Exception\SessionNotStartedException
     * @api
     */
    public function getTags()
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('Tried to retrieve tags from a session which has not been started yet.', 1355141501);
        }
        return $this->tags;
    }

    /**
     * Updates the last activity time to "now".
     *
     * @return void
     * @throws Exception\SessionNotStartedException
     */
    public function touch()
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('Tried to touch a session, but the session has not been started yet.', 1354284318);
        }

        // Only makes sense for remote sessions because the currently active session
        // will be updated on shutdown anyway:
        if ($this->remote === true) {
            $this->lastActivityTimestamp = $this->now;
            $this->writeSessionMetaDataCacheEntry();
        }
    }

    /**
     * Explicitly writes and closes the session
     *
     * @return void
     * @api
     */
    public function close()
    {
        $this->shutdownObject();
    }

    /**
     * Explicitly destroys all session data
     *
     * @param string $reason A reason for destroying the session – used by the LoggingAspect
     * @return void
     * @throws Exception\SessionNotStartedException
     * @api
     */
    public function destroy($reason = null)
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('Tried to destroy a session which has not been started yet.', 1351162668);
        }
        if ($this->remote !== true) {
            if (!$this->response->hasCookie($this->sessionCookieName)) {
                $this->response->setCookie($this->sessionCookie);
            }
            $this->sessionCookie->expire();
        }

        $this->removeSessionMetaDataCacheEntry($this->sessionIdentifier);
        $this->storageCache->flushByTag($this->storageIdentifier);
        $this->started = false;
        $this->sessionIdentifier = null;
        $this->storageIdentifier = null;
        $this->tags = [];
        $this->request = null;
    }

    /**
     * Iterates over all existing sessions and removes their data if the inactivity
     * timeout was reached.
     *
     * @return integer The number of outdated entries removed
     * @api
     */
    public function collectGarbage()
    {
        if ($this->inactivityTimeout === 0) {
            return 0;
        }
        if ($this->metaDataCache->has('_garbage-collection-running')) {
            return false;
        }

        $sessionRemovalCount = 0;
        $this->metaDataCache->set('_garbage-collection-running', true, [], 120);

        foreach ($this->metaDataCache->getIterator() as $sessionIdentifier => $sessionInfo) {
            if ($sessionIdentifier === '_garbage-collection-running') {
                continue;
            }
            $lastActivitySecondsAgo = $this->now - $sessionInfo['lastActivityTimestamp'];
            if ($lastActivitySecondsAgo > $this->inactivityTimeout) {
                if ($sessionInfo['storageIdentifier'] === null) {
                    $this->systemLogger->log('SESSION INFO INVALID: ' . $sessionIdentifier, LOG_WARNING, $sessionInfo);
                } else {
                    $this->storageCache->flushByTag($sessionInfo['storageIdentifier']);
                    $sessionRemovalCount++;
                }
                $this->metaDataCache->remove($sessionIdentifier);
            }
            if ($sessionRemovalCount >= $this->garbageCollectionMaximumPerRun) {
                break;
            }
        }

        $this->metaDataCache->remove('_garbage-collection-running');
        return $sessionRemovalCount;
    }

    /**
     * Shuts down this session
     *
     * This method must not be called manually – it is invoked by Flow's object
     * management.
     *
     * @return void
     */
    public function shutdownObject()
    {
        if ($this->started === true && $this->remote === false) {
            if ($this->metaDataCache->has($this->sessionIdentifier)) {
                // Security context can't be injected and must be retrieved manually
                // because it relies on this very session object:
                $securityContext = $this->objectManager->get(Context::class);
                if ($securityContext->isInitialized()) {
                    $this->storeAuthenticatedAccountsInfo($securityContext->getAuthenticationTokens());
                }

                $this->putData('Neos_Flow_Object_ObjectManager', $this->objectManager->getSessionInstances());
                $this->writeSessionMetaDataCacheEntry();
            }
            $this->started = false;

            $decimals = (integer)strlen(strrchr($this->garbageCollectionProbability, '.')) - 1;
            $factor = ($decimals > -1) ? $decimals * 10 : 1;
            if (rand(1, 100 * $factor) <= ($this->garbageCollectionProbability * $factor)) {
                $this->collectGarbage();
            }
        }
        $this->request = null;
    }

    /**
     * Automatically expires the session if the user has been inactive for too long.
     *
     * @return boolean TRUE if the session expired, FALSE if not
     */
    protected function autoExpire()
    {
        $lastActivitySecondsAgo = $this->now - $this->lastActivityTimestamp;
        $expired = false;
        if ($this->inactivityTimeout !== 0 && $lastActivitySecondsAgo > $this->inactivityTimeout) {
            $this->started = true;
            $this->sessionIdentifier = $this->sessionCookie->getValue();
            $this->destroy(sprintf('Session %s was inactive for %s seconds, more than the configured timeout of %s seconds.', $this->sessionIdentifier, $lastActivitySecondsAgo, $this->inactivityTimeout));
            $expired = true;
        }
        return $expired;
    }

    /**
     * Initialize request, response and session cookie
     *
     * @param HttpRequestHandlerInterface $requestHandler
     * @return void
     * @throws Exception\InvalidRequestResponseException
     */
    protected function initializeHttpAndCookie(HttpRequestHandlerInterface $requestHandler)
    {
        $this->request = $requestHandler->getHttpRequest();
        $this->response = $requestHandler->getHttpResponse();

        if (!$this->request instanceof Http\Request || !$this->response instanceof Http\Response) {
            $className = get_class($requestHandler);
            $requestMessage = 'the request was ' . (is_object($this->request) ? 'of type ' . get_class($this->request) : gettype($this->request));
            $responseMessage = 'and the response was ' . (is_object($this->response) ? 'of type ' . get_class($this->response) : gettype($this->response));
            throw new Exception\InvalidRequestResponseException(sprintf('The active request handler "%s" did not provide a valid HTTP request / HTTP response pair: %s %s.', $className, $requestMessage, $responseMessage), 1354633950);
        }

        if ($this->request->hasCookie($this->sessionCookieName)) {
            $sessionIdentifier = $this->request->getCookie($this->sessionCookieName)->getValue();
            $this->sessionCookie = new Http\Cookie($this->sessionCookieName, $sessionIdentifier, 0, $this->sessionCookieLifetime, $this->sessionCookieDomain, $this->sessionCookiePath, $this->sessionCookieSecure, $this->sessionCookieHttpOnly);
        }
    }

    /**
     * Stores some information about the authenticated accounts in the session data.
     *
     * This method will check if a session has already been started, which is
     * the case after tokens relying on a session have been authenticated: the
     * UsernamePasswordToken does, for example, start a session in its authenticate()
     * method.
     *
     * Because more than one account can be authenticated at a time, this method
     * accepts an array of tokens instead of a single account.
     *
     * Note that if a session is started after tokens have been authenticated, the
     * session will NOT be tagged with authenticated accounts.
     *
     * @param array<TokenInterface>
     * @return void
     */
    protected function storeAuthenticatedAccountsInfo(array $tokens)
    {
        $accountProviderAndIdentifierPairs = [];
        /** @var TokenInterface $token */
        foreach ($tokens as $token) {
            $account = $token->getAccount();
            if ($token->isAuthenticated() && $account !== null) {
                $accountProviderAndIdentifierPairs[$account->getAuthenticationProviderName() . ':' . $account->getAccountIdentifier()] = true;
            }
        }
        if ($accountProviderAndIdentifierPairs !== []) {
            $this->putData('Neos_Flow_Security_Accounts', array_keys($accountProviderAndIdentifierPairs));
        }
    }

    /**
     * Writes the cache entry containing information about the session, such as the
     * last activity time and the storage identifier.
     *
     * This function does not write the whole session _data_ into the storage cache,
     * but only the "head" cache entry containing meta information.
     *
     * The session cache entry is also tagged with "session", the session identifier
     * and any custom tags of this session, prefixed with TAG_PREFIX.
     *
     * @return void
     */
    protected function writeSessionMetaDataCacheEntry()
    {
        $sessionInfo = [
            'lastActivityTimestamp' => $this->lastActivityTimestamp,
            'storageIdentifier' => $this->storageIdentifier,
            'tags' => $this->tags
        ];

        $tagsForCacheEntry = array_map(function ($tag) {
            return Session::TAG_PREFIX . $tag;
        }, $this->tags);
        $tagsForCacheEntry[] = $this->sessionIdentifier;
        $tagsForCacheEntry[] = 'session';

        $this->metaDataCache->set($this->sessionIdentifier, $sessionInfo, $tagsForCacheEntry, 0);
    }

    /**
     * Removes the session info cache entry for the specified session.
     *
     * Note that this function does only remove the "head" cache entry, not the
     * related data referred to by the storage identifier.
     *
     * @param string $sessionIdentifier
     * @return void
     */
    protected function removeSessionMetaDataCacheEntry($sessionIdentifier)
    {
        $this->metaDataCache->remove($sessionIdentifier);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Session;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A modular session implementation based on the caching framework.
 * 
 * You may access the currently active session in userland code. In order to do this,
 * inject SessionInterface and NOT just the Session object.
 * The former will be a unique instance (singleton) representing the current session
 * while the latter would be a completely new session instance!
 * 
 * You can use the Session Manager for accessing sessions which are not currently
 * active.
 * 
 * Note that Flow's bootstrap (that is, Neos\Flow\Core\Scripts) will try to resume
 * a possibly existing session automatically. If a session could be resumed during
 * that phase already, calling start() at a later stage will be a no-operation.
 */
class Session extends Session_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     * @param string $sessionIdentifier The public session identifier which is also used in the session cookie
     * @param string $storageIdentifier The private storage identifier which is used for storage cache entries
     * @param integer $lastActivityTimestamp Unix timestamp of the last known activity for this session
     * @param array $tags A list of tags set for this session
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        call_user_func_array('parent::__construct', $arguments);
        if ('Neos\Flow\Session\Session' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Flow\Session\Session';
        if ($isSameClass) {
            $this->initializeObject(1);
        }

        $isSameClass = get_class($this) === 'Neos\Flow\Session\Session';
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
            'start' => array(
                'Neos\Flow\Aop\Advice\AfterAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterAdvice('Neos\Flow\Session\Aspect\LoggingAspect', 'logStart', $objectManager, NULL),
                ),
            ),
            'resume' => array(
                'Neos\Flow\Aop\Advice\AfterAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterAdvice('Neos\Flow\Session\Aspect\LoggingAspect', 'logResume', $objectManager, NULL),
                ),
            ),
            'destroy' => array(
                'Neos\Flow\Aop\Advice\BeforeAdvice' => array(
                    new \Neos\Flow\Aop\Advice\BeforeAdvice('Neos\Flow\Session\Aspect\LoggingAspect', 'logDestroy', $objectManager, NULL),
                ),
            ),
            'renewId' => array(
                'Neos\Flow\Aop\Advice\AroundAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AroundAdvice('Neos\Flow\Session\Aspect\LoggingAspect', 'logRenewId', $objectManager, NULL),
                ),
            ),
            'collectGarbage' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\Session\Aspect\LoggingAspect', 'logCollectGarbage', $objectManager, NULL),
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

        $isSameClass = get_class($this) === 'Neos\Flow\Session\Session';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\Session\Session', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

        if ($isSameClass || $isClassProxy) {
            $this->initializeObject(2);
        }

        $isSameClass = get_class($this) === 'Neos\Flow\Session\Session';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\Session\Session', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

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
     * @return void
     * @throws Exception\InvalidRequestHandlerException
     */
    public function start()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['start'])) {
            $result = parent::start();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['start'] = TRUE;
            try {
            
                $methodArguments = [];

        $result = NULL;
        $afterAdviceInvoked = FALSE;
        try {

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\Session', 'start', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['start']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['start']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\Session', 'start', $methodArguments, NULL, $result);
                    $afterAdviceInvoked = TRUE;
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {

                if (!$afterAdviceInvoked && isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['start']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['start']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\Session', 'start', $methodArguments, NULL, NULL, $exception);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }
                }

                throw $exception;
        }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['start']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['start']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return integer If a session was resumed, the inactivity of since the last request is returned
     */
    public function resume()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['resume'])) {
            $result = parent::resume();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['resume'] = TRUE;
            try {
            
                $methodArguments = [];

        $result = NULL;
        $afterAdviceInvoked = FALSE;
        try {

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\Session', 'resume', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['resume']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['resume']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\Session', 'resume', $methodArguments, NULL, $result);
                    $afterAdviceInvoked = TRUE;
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {

                if (!$afterAdviceInvoked && isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['resume']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['resume']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\Session', 'resume', $methodArguments, NULL, NULL, $exception);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }
                }

                throw $exception;
        }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['resume']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['resume']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param string $reason A reason for destroying the session – used by the LoggingAspect
     * @return void
     * @throws Exception\SessionNotStartedException
     */
    public function destroy($reason = NULL)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['destroy'])) {
            $result = parent::destroy($reason);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['destroy'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['reason'] = $reason;
            
                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['destroy']['Neos\Flow\Aop\Advice\BeforeAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['destroy']['Neos\Flow\Aop\Advice\BeforeAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\Session', 'destroy', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\Session', 'destroy', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['destroy']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['destroy']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return string The new session ID
     * @throws Exception\SessionNotStartedException
     * @throws Exception\OperationNotSupportedException
     */
    public function renewId()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['renewId'])) {
            $result = parent::renewId();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['renewId'] = TRUE;
            try {
            
                $methodArguments = [];

                $adviceChains = $this->Flow_Aop_Proxy_getAdviceChains('renewId');
                $adviceChain = $adviceChains['Neos\Flow\Aop\Advice\AroundAdvice'];
                $adviceChain->rewind();
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\Session', 'renewId', $methodArguments, $adviceChain);
                $result = $adviceChain->proceed($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['renewId']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['renewId']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @return integer The number of outdated entries removed
     */
    public function collectGarbage()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['collectGarbage'])) {
            $result = parent::collectGarbage();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['collectGarbage'] = TRUE;
            try {
            
                $methodArguments = [];

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\Session', 'collectGarbage', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['collectGarbage']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['collectGarbage']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\Session', 'collectGarbage', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['collectGarbage']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['collectGarbage']);
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
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
  'metaDataCache' => 'Neos\\Cache\\Frontend\\VariableFrontend',
  'storageCache' => 'Neos\\Cache\\Frontend\\VariableFrontend',
  'bootstrap' => 'Neos\\Flow\\Core\\Bootstrap',
  'sessionCookieName' => 'string',
  'sessionCookieLifetime' => 'integer',
  'sessionCookieDomain' => 'string',
  'sessionCookiePath' => 'string',
  'sessionCookieSecure' => 'boolean',
  'sessionCookieHttpOnly' => 'boolean',
  'sessionCookie' => 'Neos\\Flow\\Http\\Cookie',
  'inactivityTimeout' => 'integer',
  'lastActivityTimestamp' => 'integer',
  'tags' => 'array',
  'now' => 'integer',
  'garbageCollectionProbability' => 'float',
  'garbageCollectionMaximumPerRun' => 'integer',
  'sessionIdentifier' => 'string',
  'storageIdentifier' => 'string',
  'started' => 'boolean',
  'remote' => 'boolean',
  'request' => 'Neos\\Flow\\Http\\Request',
  'response' => 'Neos\\Flow\\Http\\Response',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('', '', 'metaDataCache', '0763fb7436aa7c25fc065ae46e9222fa', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Cache\CacheManager')->getCache('Flow_Session_MetaData'); });
        $this->Flow_Proxy_LazyPropertyInjection('', '', 'storageCache', 'e896a7ff09cf9b51b86e7f8434211ff4', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Cache\CacheManager')->getCache('Flow_Session_Storage'); });
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow'));
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Core\Bootstrap', 'Neos\Flow\Core\Bootstrap', 'bootstrap', 'aed14e789673142988a77dfdf496f415', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Core\Bootstrap'); });
        $this->Flow_Injected_Properties = array (
  0 => 'metaDataCache',
  1 => 'storageCache',
  2 => 'settings',
  3 => 'objectManager',
  4 => 'systemLogger',
  5 => 'bootstrap',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Session/Session.php
#