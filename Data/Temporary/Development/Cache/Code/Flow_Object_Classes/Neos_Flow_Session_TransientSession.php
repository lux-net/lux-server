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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Utility\Algorithms;

/**
 * Implementation of a transient session.
 *
 * This session behaves like any other session except that it only stores the
 * data during one request.
 *
 * @Flow\Scope("singleton")
 */
class TransientSession_Original implements SessionInterface
{
    /**
     * The session Id
     *
     * @var string
     */
    protected $sessionId;

    /**
     * If this session has been started
     *
     * @var boolean
     */
    protected $started = false;

    /**
     * The session data
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var integer
     */
    protected $lastActivityTimestamp;

    /**
     * @var array
     */
    protected $tags;

    /**
     * Tells if the session has been started already.
     *
     * @return boolean
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Starts the session, if it has not been already started
     *
     * @return void
     */
    public function start()
    {
        $this->sessionId = Algorithms::generateRandomString(13);
        $this->started = true;
    }

    /**
     * Returns TRUE if there is a session that can be resumed. FALSE otherwise
     *
     * @return boolean
     */
    public function canBeResumed()
    {
        return true;
    }

    /**
     * Resumes an existing session, if any.
     *
     * @return void
     */
    public function resume()
    {
        if ($this->started === false) {
            $this->start();
        }
    }

    /**
     * Generates and propagates a new session ID and transfers all existing data
     * to the new session.
     *
     * @return string The new session ID
     */
    public function renewId()
    {
        $this->sessionId = Algorithms::generateRandomString(13);
        return $this->sessionId;
    }

    /**
     * Returns the current session ID.
     *
     * @return string The current session ID
     * @throws Exception\SessionNotStartedException
     */
    public function getId()
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('The session has not been started yet.', 1218034659);
        }
        return $this->sessionId;
    }

    /**
     * Returns the data associated with the given key.
     *
     * @param string $key An identifier for the content stored in the session.
     * @return mixed The data associated with the given key or NULL
     * @throws Exception\SessionNotStartedException
     */
    public function getData($key)
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('The session has not been started yet.', 1218034660);
        }
        return (array_key_exists($key, $this->data)) ? $this->data[$key] : null;
    }

    /**
     * Returns TRUE if $key is available.
     *
     * @param string $key
     * @return boolean
     */
    public function hasKey($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Stores the given data under the given key in the session
     *
     * @param string $key The key under which the data should be stored
     * @param object $data The data to be stored
     * @return void
     * @throws Exception\SessionNotStartedException
     */
    public function putData($key, $data)
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('The session has not been started yet.', 1218034661);
        }
        $this->data[$key] = $data;
    }

    /**
     * Closes the session
     *
     * @return void
     * @throws Exception\SessionNotStartedException
     */
    public function close()
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('The session has not been started yet.', 1218034662);
        }
        $this->started = false;
    }

    /**
     * Explicitly destroys all session data
     *
     * @param string $reason A reason for destroying the session – used by the LoggingAspect
     * @return void
     * @throws Exception\SessionNotStartedException
     */
    public function destroy($reason = null)
    {
        if ($this->started !== true) {
            throw new Exception\SessionNotStartedException('The session has not been started yet.', 1218034663);
        }
        $this->data = [];
        $this->started = false;
    }

    /**
     * No operation for transient session.
     *
     * @param Bootstrap $bootstrap
     * @return void
     */
    public static function destroyAll(Bootstrap $bootstrap)
    {
    }

    /**
     * No operation for transient session.
     *
     * @return void
     */
    public function collectGarbage()
    {
    }

    /**
     * Returns the unix time stamp marking the last point in time this session has
     * been in use.
     *
     * @return integer unix timestamp
     */
    public function getLastActivityTimestamp()
    {
        if ($this->lastActivityTimestamp === null) {
            $this->touch();
        }
        return $this->lastActivityTimestamp;
    }

    /**
     * Updates the last activity time to "now".
     *
     * @return void
     */
    public function touch()
    {
        $this->lastActivityTimestamp = time();
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
            throw new Exception\SessionNotStartedException('The session has not been started yet.', 1422551048);
        }
        $this->tags[$tag] = true;
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
            throw new Exception\SessionNotStartedException('The session has not been started yet.', 1422551049);
        }
        if (isset($this->tags[$tag])) {
            unset($this->tags[$tag]);
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
            throw new Exception\SessionNotStartedException('The session has not been started yet.', 1422551050);
        }
        return array_keys($this->tags);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Session;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Implementation of a transient session.
 * 
 * This session behaves like any other session except that it only stores the
 * data during one request.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class TransientSession extends TransientSession_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\Aop\AdvicesTrait, \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;

    private $Flow_Aop_Proxy_targetMethodsAndGroupedAdvices = array();

    private $Flow_Aop_Proxy_groupedAdviceChains = array();

    private $Flow_Aop_Proxy_methodIsInAdviceMode = array();


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {

        $this->Flow_Aop_Proxy_buildMethodsAndAdvicesArray();
        if (get_class($this) === 'Neos\Flow\Session\TransientSession') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Session\TransientSession', $this);
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
        if (get_class($this) === 'Neos\Flow\Session\TransientSession') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Session\TransientSession', $this);

        $this->Flow_setRelatedEntities();
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

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\TransientSession', 'start', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['start']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['start']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\TransientSession', 'start', $methodArguments, NULL, $result);
                    $afterAdviceInvoked = TRUE;
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {

                if (!$afterAdviceInvoked && isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['start']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['start']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\TransientSession', 'start', $methodArguments, NULL, NULL, $exception);
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
     * @return void
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

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\TransientSession', 'resume', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['resume']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['resume']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\TransientSession', 'resume', $methodArguments, NULL, $result);
                    $afterAdviceInvoked = TRUE;
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {

                if (!$afterAdviceInvoked && isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['resume']['Neos\Flow\Aop\Advice\AfterAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['resume']['Neos\Flow\Aop\Advice\AfterAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\TransientSession', 'resume', $methodArguments, NULL, NULL, $exception);
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
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\TransientSession', 'destroy', $methodArguments);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\TransientSession', 'destroy', $methodArguments);
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
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\TransientSession', 'renewId', $methodArguments, $adviceChain);
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
     * @return void
     */
    public function collectGarbage()
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['collectGarbage'])) {
            $result = parent::collectGarbage();

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['collectGarbage'] = TRUE;
            try {
            
                $methodArguments = [];

                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\TransientSession', 'collectGarbage', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['collectGarbage']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['collectGarbage']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Session\TransientSession', 'collectGarbage', $methodArguments, NULL, $result);
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
  'sessionId' => 'string',
  'started' => 'boolean',
  'data' => 'array',
  'lastActivityTimestamp' => 'integer',
  'tags' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Session/TransientSession.php
#