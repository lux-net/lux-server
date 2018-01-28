<?php 
namespace Neos\Flow\Mvc;

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
use Neos\Flow\Cli\Request as CliRequest;
use Neos\Flow\Configuration\Exception\NoSuchOptionException;
use Neos\Flow\Http\Response as HttpResponse;
use Neos\Flow\Log\SecurityLoggerInterface;
use Neos\Flow\Mvc\Controller\ControllerInterface;
use Neos\Flow\Mvc\Controller\Exception\InvalidControllerException;
use Neos\Flow\Mvc\Exception\InfiniteLoopException;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Mvc\Exception\ForwardException;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Security\Authentication\EntryPoint\WebRedirect;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Authorization\FirewallInterface;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Exception\AccessDeniedException;
use Neos\Flow\Security\Exception\AuthenticationRequiredException;

/**
 * Dispatches requests to the controller which was specified by the request and
 * returns the response the controller generated.
 *
 * @Flow\Scope("singleton")
 * @api
 */
class Dispatcher_Original
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * Inject the Object Manager through setter injection because property injection
     * is not available during compile time.
     *
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Injects the Flow settings
     *
     * @param array $settings The Flow settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Dispatches a request to a controller
     *
     * @param RequestInterface $request The request to dispatch
     * @param ResponseInterface $response The response, to be modified by the controller
     * @return void
     * @throws AuthenticationRequiredException|AccessDeniedException
     * @api
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response)
    {
        if ($request instanceof CliRequest) {
            $this->initiateDispatchLoop($request, $response);
            return;
        }

        // NOTE: The dispatcher is used for both Action- and CLI-Requests. For the latter case dispatching might happen during compile-time, that's why we can't inject the following dependencies

        /** @var Context $securityContext */
        $securityContext = $this->objectManager->get(Context::class);
        if ($securityContext->areAuthorizationChecksDisabled()) {
            $this->initiateDispatchLoop($request, $response);
            return;
        }

        /** @var FirewallInterface $firewall */
        $firewall = $this->objectManager->get(FirewallInterface::class);
        /** @var SecurityLoggerInterface $securityLogger */
        $securityLogger = $this->objectManager->get(SecurityLoggerInterface::class);

        try {
            /** @var ActionRequest $request */
            $firewall->blockIllegalRequests($request);
            $this->initiateDispatchLoop($request, $response);
        } catch (AuthenticationRequiredException $exception) {
            $entryPointFound = false;
            /** @var $token TokenInterface */
            foreach ($securityContext->getAuthenticationTokens() as $token) {
                $entryPoint = $token->getAuthenticationEntryPoint();
                if ($entryPoint === null) {
                    continue;
                }
                $entryPointFound = true;
                if ($entryPoint instanceof WebRedirect) {
                    $securityLogger->log('Redirecting to authentication entry point', LOG_INFO, $entryPoint->getOptions());
                } else {
                    $securityLogger->log(sprintf('Starting authentication with entry point of type "%s"', get_class($entryPoint)), LOG_INFO);
                }
                $securityContext->setInterceptedRequest($request->getMainRequest());
                /** @var HttpResponse $response */
                $entryPoint->startAuthentication($request->getHttpRequest(), $response);
            }
            if ($entryPointFound === false) {
                $securityLogger->log('No authentication entry point found for active tokens, therefore cannot authenticate or redirect to authentication automatically.', LOG_NOTICE);
                throw $exception;
            }
        } catch (AccessDeniedException $exception) {
            $securityLogger->log('Access denied', LOG_WARNING);
            throw $exception;
        }
    }

    /**
     * Try processing the request until it is successfully marked "dispatched"
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws InvalidControllerException|InfiniteLoopException|NoSuchOptionException
     */
    protected function initiateDispatchLoop(RequestInterface $request, ResponseInterface $response)
    {
        $dispatchLoopCount = 0;
        /** @var ActionRequest $request */
        while (!$request->isDispatched()) {
            if ($dispatchLoopCount++ > 99) {
                throw new Exception\InfiniteLoopException(sprintf('Could not ultimately dispatch the request after %d iterations.', $dispatchLoopCount), 1217839467);
            }
            $controller = $this->resolveController($request);
            try {
                $this->emitBeforeControllerInvocation($request, $response, $controller);
                $controller->processRequest($request, $response);
                $this->emitAfterControllerInvocation($request, $response, $controller);
            } catch (StopActionException $exception) {
                $this->emitAfterControllerInvocation($request, $response, $controller);
                if ($exception instanceof ForwardException) {
                    $request = $exception->getNextRequest();
                } elseif (!$request->isMainRequest()) {
                    $request = $request->getParentRequest();
                }
            }
        }
    }

    /**
     * This signal is emitted directly before the request is been dispatched to a controller.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param ControllerInterface $controller
     * @return void
     * @Flow\Signal
     */
    protected function emitBeforeControllerInvocation(RequestInterface $request, ResponseInterface $response, ControllerInterface $controller)
    {
    }

    /**
     * This signal is emitted directly after the request has been dispatched to a controller and the controller
     * returned control back to the dispatcher.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param ControllerInterface $controller
     * @return void
     * @Flow\Signal
     */
    protected function emitAfterControllerInvocation(RequestInterface $request, ResponseInterface $response, ControllerInterface $controller)
    {
    }

    /**
     * Finds and instantiates a controller that matches the current request.
     * If no controller can be found, an instance of NotFoundControllerInterface is returned.
     *
     * @param RequestInterface $request The request to dispatch
     * @return ControllerInterface
     * @throws NoSuchOptionException
     * @throws Controller\Exception\InvalidControllerException
     */
    protected function resolveController(RequestInterface $request)
    {
        /** @var ActionRequest $request */
        $controllerObjectName = $request->getControllerObjectName();
        if ($controllerObjectName === '') {
            $exceptionMessage = 'No controller could be resolved which would match your request';
            if ($request instanceof ActionRequest) {
                $exceptionMessage .= sprintf('. Package key: "%s", controller name: "%s"', $request->getControllerPackageKey(), $request->getControllerName());
                if ($request->getControllerSubpackageKey() !== null) {
                    $exceptionMessage .= sprintf(', SubPackage key: "%s"', $request->getControllerSubpackageKey());
                }
                $exceptionMessage .= sprintf('. (%s %s)', $request->getHttpRequest()->getMethod(), $request->getHttpRequest()->getUri());
            }
            throw new Controller\Exception\InvalidControllerException($exceptionMessage, 1303209195, null, $request);
        }

        $controller = $this->objectManager->get($controllerObjectName);
        if (!$controller instanceof ControllerInterface) {
            throw new Controller\Exception\InvalidControllerException('Invalid controller "' . $request->getControllerObjectName() . '". The controller must be a valid request handling controller, ' . (is_object($controller) ? get_class($controller) : gettype($controller)) . ' given.', 1202921619, null, $request);
        }
        return $controller;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Mvc;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Dispatches requests to the controller which was specified by the request and
 * returns the response the controller generated.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class Dispatcher extends Dispatcher_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

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
        if (get_class($this) === 'Neos\Flow\Mvc\Dispatcher') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Mvc\Dispatcher', $this);
        if ('Neos\Flow\Mvc\Dispatcher' === get_class($this)) {
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
            'emitBeforeControllerInvocation' => array(
                'Neos\Flow\Aop\Advice\AfterReturningAdvice' => array(
                    new \Neos\Flow\Aop\Advice\AfterReturningAdvice('Neos\Flow\SignalSlot\SignalAspect', 'forwardSignalToDispatcher', $objectManager, NULL),
                ),
            ),
            'emitAfterControllerInvocation' => array(
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
        if (get_class($this) === 'Neos\Flow\Mvc\Dispatcher') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Mvc\Dispatcher', $this);

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
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param ControllerInterface $controller
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitBeforeControllerInvocation(\Neos\Flow\Mvc\RequestInterface $request, \Neos\Flow\Mvc\ResponseInterface $response, \Neos\Flow\Mvc\Controller\ControllerInterface $controller)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitBeforeControllerInvocation'])) {
            $result = parent::emitBeforeControllerInvocation($request, $response, $controller);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitBeforeControllerInvocation'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['request'] = $request;
                $methodArguments['response'] = $response;
                $methodArguments['controller'] = $controller;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Mvc\Dispatcher', 'emitBeforeControllerInvocation', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitBeforeControllerInvocation']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitBeforeControllerInvocation']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Mvc\Dispatcher', 'emitBeforeControllerInvocation', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitBeforeControllerInvocation']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitBeforeControllerInvocation']);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param ControllerInterface $controller
     * @return void
     * @\Neos\Flow\Annotations\Signal
     */
    protected function emitAfterControllerInvocation(\Neos\Flow\Mvc\RequestInterface $request, \Neos\Flow\Mvc\ResponseInterface $response, \Neos\Flow\Mvc\Controller\ControllerInterface $controller)
    {

        if (isset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterControllerInvocation'])) {
            $result = parent::emitAfterControllerInvocation($request, $response, $controller);

        } else {
            $this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterControllerInvocation'] = TRUE;
            try {
            
                $methodArguments = [];

                $methodArguments['request'] = $request;
                $methodArguments['response'] = $response;
                $methodArguments['controller'] = $controller;
            
                $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Mvc\Dispatcher', 'emitAfterControllerInvocation', $methodArguments);
                $result = $this->Flow_Aop_Proxy_invokeJoinPoint($joinPoint);
                $methodArguments = $joinPoint->getMethodArguments();

                if (isset($this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAfterControllerInvocation']['Neos\Flow\Aop\Advice\AfterReturningAdvice'])) {
                    $advices = $this->Flow_Aop_Proxy_targetMethodsAndGroupedAdvices['emitAfterControllerInvocation']['Neos\Flow\Aop\Advice\AfterReturningAdvice'];
                    $joinPoint = new \Neos\Flow\Aop\JoinPoint($this, 'Neos\Flow\Mvc\Dispatcher', 'emitAfterControllerInvocation', $methodArguments, NULL, $result);
                    foreach ($advices as $advice) {
                        $advice->invoke($joinPoint);
                    }

                    $methodArguments = $joinPoint->getMethodArguments();
                }

            } catch (\Exception $exception) {
                unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterControllerInvocation']);
                throw $exception;
            }
            unset($this->Flow_Aop_Proxy_methodIsInAdviceMode['emitAfterControllerInvocation']);
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
  'settings' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectObjectManager(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'));
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow'));
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'settings',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Mvc/Dispatcher.php
#