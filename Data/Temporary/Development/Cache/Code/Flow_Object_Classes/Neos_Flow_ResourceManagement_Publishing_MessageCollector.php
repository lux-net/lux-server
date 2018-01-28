<?php 
namespace Neos\Flow\ResourceManagement\Publishing;

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
use Neos\Error\Messages\Error;
use Neos\Error\Messages\Message;
use Neos\Error\Messages\Notice;
use Neos\Error\Messages\Warning;
use Neos\Flow\Exception;
use Neos\Flow\Log\SystemLoggerInterface;

/**
 * Message Collector
 *
 * @Flow\Scope("singleton")
 */
class MessageCollector_Original
{
    /**
     * @var \SplObjectStorage
     */
    protected $messages;

    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * Message Collector Constructor
     */
    public function __construct()
    {
        $this->messages = new \SplObjectStorage();
    }

    /**
     * @param string $message The message to log
     * @param string $severity An integer value, one of the Error::SEVERITY_* constants
     * @param integer $code A unique error code
     * @return void
     * @throws Exception
     * @api
     */
    public function append($message, $severity = Error::SEVERITY_ERROR, $code = null)
    {
        switch ($severity) {
            case Error::SEVERITY_ERROR:
                $notification = new Error($message, $code);
                break;
            case Error::SEVERITY_WARNING:
                $notification = new Warning($message, $code);
                break;
            case Error::SEVERITY_NOTICE:
                $notification = new Notice($message, $code);
                break;
            case Error::SEVERITY_OK:
                $notification = new Message($message, $code);
                break;
            default:
                throw new Exception('Invalid severity', 1455819761);
        }
        $this->messages->attach($notification);
    }

    /**
     * @return boolean
     * @api
     */
    public function hasMessages()
    {
        return $this->messages->count() > 0;
    }

    /**
     * @param callable $callback a callback function to process every notification
     * @return void
     * @api
     */
    public function flush(callable $callback = null)
    {
        foreach ($this->messages as $message) {
            /** @var Message $message */
            $this->messages->detach($message);
            $this->systemLogger->log('ResourcePublishingMessage: ' . $message->getMessage(), $message->getSeverity());
            if ($callback !== null) {
                $callback($message);
            }
        }
    }

    /**
     * Flush all notification during the object lifecycle
     *
     * @return void
     */
    public function __destruct()
    {
        $this->flush();
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\ResourceManagement\Publishing;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Message Collector
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class MessageCollector extends MessageCollector_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Flow\ResourceManagement\Publishing\MessageCollector') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\ResourceManagement\Publishing\MessageCollector', $this);
        parent::__construct();
        if ('Neos\Flow\ResourceManagement\Publishing\MessageCollector' === get_class($this)) {
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
  'messages' => '\\SplObjectStorage',
  'systemLogger' => 'Neos\\Flow\\Log\\SystemLoggerInterface',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\ResourceManagement\Publishing\MessageCollector') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\ResourceManagement\Publishing\MessageCollector', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Log\SystemLoggerInterface', 'Neos\Flow\Log\Logger', 'systemLogger', '717e9de4d0309f4f47c821b9257eb5c2', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\SystemLoggerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'systemLogger',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/ResourceManagement/Publishing/MessageCollector.php
#