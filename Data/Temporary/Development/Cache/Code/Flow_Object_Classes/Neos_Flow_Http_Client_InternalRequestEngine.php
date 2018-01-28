<?php 
namespace Neos\Flow\Http\Client;

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
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Error\Debugger;
use Neos\Flow\Exception;
use Neos\Flow\Http\Component\ComponentChain;
use Neos\Flow\Http\Component\ComponentContext;
use Neos\Flow\Http;
use Neos\Flow\Mvc\Dispatcher;
use Neos\Flow\Mvc\Routing\Router;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Security\Context;
use Neos\Flow\Session\SessionInterface;
use Neos\Flow\Tests\FunctionalTestRequestHandler;
use Neos\Flow\Validation\ValidatorResolver;

/**
 * A Request Engine which uses Flow's request dispatcher directly for processing
 * HTTP requests internally.
 *
 * This engine is particularly useful in functional test scenarios.
 */
class InternalRequestEngine_Original implements RequestEngineInterface
{
    /**
     * @Flow\Inject(lazy = false)
     * @var Bootstrap
     */
    protected $bootstrap;

    /**
     * @Flow\Inject(lazy = false)
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @Flow\Inject(lazy = false)
     * @var Router
     */
    protected $router;

    /**
     * @Flow\Inject(lazy = false)
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @Flow\Inject
     * @var ValidatorResolver
     */
    protected $validatorResolver;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Sends the given HTTP request
     *
     * @param Http\Request $httpRequest
     * @return Http\Response
     * @throws Http\Exception
     * @api
     */
    public function sendRequest(Http\Request $httpRequest)
    {
        $requestHandler = $this->bootstrap->getActiveRequestHandler();
        if (!$requestHandler instanceof FunctionalTestRequestHandler) {
            throw new Http\Exception('The browser\'s internal request engine has only been designed for use within functional tests.', 1335523749);
        }

        $this->securityContext->clearContext();
        $this->validatorResolver->reset();

        $response = new Http\Response();
        $componentContext = new ComponentContext($httpRequest, $response);
        $requestHandler->setComponentContext($componentContext);

        $objectManager = $this->bootstrap->getObjectManager();
        $baseComponentChain = $objectManager->get(\Neos\Flow\Http\Component\ComponentChain::class);
        $componentContext = new ComponentContext($httpRequest, $response);

        try {
            $baseComponentChain->handle($componentContext);
        } catch (\Throwable $throwable) {
            $this->prepareErrorResponse($throwable, $componentContext->getHttpResponse());
        } catch (\Exception $exception) {
            $this->prepareErrorResponse($exception, $componentContext->getHttpResponse());
        }
        $session = $this->bootstrap->getObjectManager()->get(SessionInterface::class);
        if ($session->isStarted()) {
            $session->close();
        }
        $this->persistenceManager->clearState();
        return $componentContext->getHttpResponse();
    }

    /**
     * Returns the router used by this internal request engine
     *
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Prepare a response in case an error occurred.
     *
     * @param object $exception \Exception or \Throwable
     * @param Http\Response $response
     * @return void
     */
    protected function prepareErrorResponse($exception, Http\Response $response)
    {
        $pathPosition = strpos($exception->getFile(), 'Packages/');
        $filePathAndName = ($pathPosition !== false) ? substr($exception->getFile(), $pathPosition) : $exception->getFile();
        $exceptionCodeNumber = ($exception->getCode() > 0) ? '#' . $exception->getCode() . ': ' : '';
        $content = PHP_EOL . 'Uncaught Exception in Flow ' . $exceptionCodeNumber . $exception->getMessage() . PHP_EOL;
        $content .= 'thrown in file ' . $filePathAndName . PHP_EOL;
        $content .= 'in line ' . $exception->getLine() . PHP_EOL . PHP_EOL;
        $content .= Debugger::getBacktraceCode($exception->getTrace(), false, true) . PHP_EOL;

        if ($exception instanceof Exception) {
            $statusCode = $exception->getStatusCode();
        } else {
            $statusCode = 500;
        }
        $response->setStatus($statusCode);
        $response->setContent($content);
        $response->setHeader('X-Flow-ExceptionCode', $exception->getCode());
        $response->setHeader('X-Flow-ExceptionMessage', $exception->getMessage());
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Http\Client;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * A Request Engine which uses Flow's request dispatcher directly for processing
 * HTTP requests internally.
 * 
 * This engine is particularly useful in functional test scenarios.
 */
class InternalRequestEngine extends InternalRequestEngine_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if ('Neos\Flow\Http\Client\InternalRequestEngine' === get_class($this)) {
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
  'bootstrap' => 'Neos\\Flow\\Core\\Bootstrap',
  'dispatcher' => 'Neos\\Flow\\Mvc\\Dispatcher',
  'router' => 'Neos\\Flow\\Mvc\\Routing\\Router',
  'securityContext' => 'Neos\\Flow\\Security\\Context',
  'configurationManager' => 'Neos\\Flow\\Configuration\\ConfigurationManager',
  'validatorResolver' => 'Neos\\Flow\\Validation\\ValidatorResolver',
  'settings' => 'array',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
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
        $this->injectSettings(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get(\Neos\Flow\Configuration\ConfigurationManager::class)->getConfiguration('Settings', 'Neos.Flow'));
        $this->bootstrap = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Core\Bootstrap');
        $this->dispatcher = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Dispatcher');
        $this->router = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Mvc\Routing\Router');
        $this->securityContext = \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Security\Context');
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Configuration\ConfigurationManager', 'Neos\Flow\Configuration\ConfigurationManager', 'configurationManager', 'f559bc775c41b957515dc1c69b91d8b1', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Configuration\ConfigurationManager'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Validation\ValidatorResolver', 'Neos\Flow\Validation\ValidatorResolver', 'validatorResolver', 'e992f50de62d81bfe770d5c5f1242621', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Validation\ValidatorResolver'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'settings',
  1 => 'bootstrap',
  2 => 'dispatcher',
  3 => 'router',
  4 => 'securityContext',
  5 => 'configurationManager',
  6 => 'validatorResolver',
  7 => 'persistenceManager',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Http/Client/InternalRequestEngine.php
#