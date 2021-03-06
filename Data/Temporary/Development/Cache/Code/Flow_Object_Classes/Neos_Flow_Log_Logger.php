<?php 
namespace Neos\Flow\Log;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * The Flow enabled logger currently used as default for logging.
 */
class Logger_Original extends DefaultLogger implements SystemLoggerInterface, SecurityLoggerInterface, ThrowableLoggerInterface
{
    /**
     * @var ThrowableStorageInterface
     */
    protected $throwableStorage;

    /**
     * @param ThrowableStorageInterface $throwableStorage
     */
    public function injectThrowableStorage(ThrowableStorageInterface $throwableStorage)
    {
        $this->throwableStorage = $throwableStorage;
    }

    /**
     * Writes information about the given exception into the log.
     *
     * @param \Exception $exception The exception to log
     * @param array $additionalData Additional data to log
     * @return void
     * @deprecated
     */
    public function logException(\Exception $exception, array $additionalData = [])
    {
        $this->logThrowable($exception, $additionalData);
    }

    /**
     * @param \Throwable $throwable The throwable to log
     * @param array $additionalData Additional data to log
     * @return void
     * @api
     */
    public function logThrowable(\Throwable $throwable, array $additionalData = [])
    {
        $message = $this->throwableStorage->logThrowable($throwable, $additionalData);
        $this->log($message, LOG_ERR);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Log;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * The Flow enabled logger currently used as default for logging.
 */
class Logger extends Logger_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        parent::__construct();
        if ('Neos\Flow\Log\Logger' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Flow\Log\Logger';
        if ($isSameClass) {
        \Neos\Flow\Core\Bootstrap::$staticObjectManager->registerShutdownObject($this, 'shutdownObject');
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
  'throwableStorage' => 'Neos\\Flow\\Log\\ThrowableStorageInterface',
  'backends' => '\\SplObjectStorage',
  'requestInfoCallback' => '\\closure',
  'renderBacktraceCallback' => '\\closure',
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
            $result = NULL;

        $isSameClass = get_class($this) === 'Neos\Flow\Log\Logger';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\Log\Logger', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

        if ($isSameClass || $isClassProxy) {
        \Neos\Flow\Core\Bootstrap::$staticObjectManager->registerShutdownObject($this, 'shutdownObject');
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->injectThrowableStorage(\Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Log\ThrowableStorageInterface'));
        $this->Flow_Injected_Properties = array (
  0 => 'throwableStorage',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Log/Logger.php
#