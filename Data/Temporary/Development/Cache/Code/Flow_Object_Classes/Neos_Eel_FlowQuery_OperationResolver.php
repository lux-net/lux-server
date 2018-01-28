<?php 
namespace Neos\Eel\FlowQuery;

/*
 * This file is part of the Neos.Eel package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Reflection\ReflectionService;

/**
 * FlowQuery Operation Resolver
 *
 * @Flow\Scope("singleton")
 */
class OperationResolver_Original implements OperationResolverInterface
{
    /**
     * @var ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @var ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * 2-dimensional array of registered operations:
     * shortOperationName => priority => operation class name
     *
     * @var array
     */
    protected $operations = [];

    /**
     * associative array of registered final operations:
     * shortOperationName => shortOperationName
     *
     * @var array
     */
    protected $finalOperationNames = [];

    /**
     * Initializer, building up $this->operations and $this->finalOperationNames
     */
    public function initializeObject()
    {
        $operationsAndFinalOperationNames = static::buildOperationsAndFinalOperationNames($this->objectManager);
        $this->operations = $operationsAndFinalOperationNames[0];
        $this->finalOperationNames = $operationsAndFinalOperationNames[1];
    }

    /**
     * @param ObjectManagerInterface $objectManager
     * @return array Array of sorted operations and array of final operation names
     * @throws FlowQueryException
     * @Flow\CompileStatic
     */
    public static function buildOperationsAndFinalOperationNames($objectManager)
    {
        $operations = [];
        $finalOperationNames = [];

        $reflectionService = $objectManager->get(ReflectionService::class);
        $operationClassNames = $reflectionService->getAllImplementationClassNamesForInterface(OperationInterface::class);
        /** @var $operationClassName OperationInterface */
        foreach ($operationClassNames as $operationClassName) {
            $shortOperationName = $operationClassName::getShortName();
            $operationPriority = $operationClassName::getPriority();
            $isFinalOperation = $operationClassName::isFinal();

            if (!isset($operations[$shortOperationName])) {
                $operations[$shortOperationName] = [];
            }

            if (isset($operations[$shortOperationName][$operationPriority])) {
                throw new FlowQueryException(sprintf('Operation with name "%s" and priority %s is already defined in class %s, and the class %s has the same priority and name.', $shortOperationName, $operationPriority, $operations[$shortOperationName][$operationPriority], $operationClassName), 1332491678);
            }
            $operations[$shortOperationName][$operationPriority] = $operationClassName;

            if ($isFinalOperation) {
                $finalOperationNames[$shortOperationName] = $shortOperationName;
            }
        }

        foreach ($operations as &$operation) {
            krsort($operation, SORT_NUMERIC);
        }

        return [$operations, $finalOperationNames];
    }

    /**
     * @param string $operationName
     * @return boolean TRUE if $operationName is final
     */
    public function isFinalOperation($operationName)
    {
        return isset($this->finalOperationNames[$operationName]);
    }

    /**
     * Resolve an operation, taking runtime constraints into account.
     *
     * @param string      $operationName
     * @param array|mixed $context
     * @throws FlowQueryException
     * @return OperationInterface the resolved operation
     */
    public function resolveOperation($operationName, $context)
    {
        if (!isset($this->operations[$operationName])) {
            throw new FlowQueryException('Operation "' . $operationName . '" not found.', 1332491837);
        }

        foreach ($this->operations[$operationName] as $operationClassName) {
            /** @var OperationInterface $operation */
            $operation = $this->objectManager->get($operationClassName);
            if ($operation->canEvaluate($context)) {
                return $operation;
            }
        }
        throw new FlowQueryException('No operation which satisfies the runtime constraints found for "' . $operationName . '".', 1332491864);
    }

    /**
     * @param string $operationName
     * @return boolean
     */
    public function hasOperation($operationName)
    {
        return isset($this->operations[$operationName]);
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Eel\FlowQuery;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * FlowQuery Operation Resolver
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class OperationResolver extends OperationResolver_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Eel\FlowQuery\OperationResolver') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Eel\FlowQuery\OperationResolver', $this);
        if (get_class($this) === 'Neos\Eel\FlowQuery\OperationResolver') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Eel\FlowQuery\OperationResolverInterface', $this);
        if ('Neos\Eel\FlowQuery\OperationResolver' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Eel\FlowQuery\OperationResolver';
        if ($isSameClass) {
            $this->initializeObject(1);
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
  'objectManager' => 'Neos\\Flow\\ObjectManagement\\ObjectManagerInterface',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'operations' => 'array',
  'finalOperationNames' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Eel\FlowQuery\OperationResolver') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Eel\FlowQuery\OperationResolver', $this);
        if (get_class($this) === 'Neos\Eel\FlowQuery\OperationResolver') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Eel\FlowQuery\OperationResolverInterface', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;

        $isSameClass = get_class($this) === 'Neos\Eel\FlowQuery\OperationResolver';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Eel\FlowQuery\OperationResolver', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

        if ($isSameClass || $isClassProxy) {
            $this->initializeObject(2);
        }
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\ObjectManagement\ObjectManagerInterface', 'Neos\Flow\ObjectManagement\ObjectManager', 'objectManager', '9524ff5e5332c1890aa361e5d186b7b6', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\ObjectManagement\ObjectManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'objectManager',
  1 => 'reflectionService',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Eel/Classes/FlowQuery/OperationResolver.php
#