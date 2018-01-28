<?php 
namespace Neos\Flow\Persistence\Aspect;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Utility\ObjectAccess;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Flow\Utility\Algorithms;

/**
 * Adds the aspect of persistence magic to relevant objects
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 * @Flow\Introduce("Neos\Flow\Persistence\Aspect\PersistenceMagicAspect->isEntityOrValueObject", interfaceName="Neos\Flow\Persistence\Aspect\PersistenceMagicInterface")
 */
class PersistenceMagicAspect_Original
{
    /**
     * If the extension "igbinary" is installed, use it for increased performance
     *
     * @var boolean
     */
    protected $useIgBinary;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @Flow\Pointcut("classAnnotatedWith(Neos\Flow\Annotations\Entity) || classAnnotatedWith(Doctrine\ORM\Mapping\Entity)")
     */
    public function isEntity()
    {
    }

    /**
     * @Flow\Pointcut("classAnnotatedWith(Neos\Flow\Annotations\ValueObject) && !filter(Neos\Flow\Persistence\Aspect\EmbeddedValueObjectPointcutFilter)")
     */
    public function isNonEmbeddedValueObject()
    {
    }

    /**
     * @Flow\Pointcut("Neos\Flow\Persistence\Aspect\PersistenceMagicAspect->isEntity || Neos\Flow\Persistence\Aspect\PersistenceMagicAspect->isNonEmbeddedValueObject")
     */
    public function isEntityOrValueObject()
    {
    }

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(length=40)
     * @Flow\Introduce("Neos\Flow\Persistence\Aspect\PersistenceMagicAspect->isEntityOrValueObject && filter(Neos\Flow\Persistence\Doctrine\Mapping\Driver\FlowAnnotationDriver)")
     */
    protected $Persistence_Object_Identifier;

    /**
     * Initializes this aspect
     *
     * @return void
     */
    public function initializeObject()
    {
        $this->useIgBinary = extension_loaded('igbinary');
    }

    /**
     * After returning advice, making sure we have an UUID for each and every entity.
     *
     * @param JoinPointInterface $joinPoint The current join point
     * @return void
     * @Flow\Before("Neos\Flow\Persistence\Aspect\PersistenceMagicAspect->isEntity && method(.*->(__construct|__clone)()) && filter(Neos\Flow\Persistence\Doctrine\Mapping\Driver\FlowAnnotationDriver)")
     */
    public function generateUuid(JoinPointInterface $joinPoint)
    {
        /** @var $proxy PersistenceMagicInterface */
        $proxy = $joinPoint->getProxy();
        ObjectAccess::setProperty($proxy, 'Persistence_Object_Identifier', Algorithms::generateUUID(), true);
        $this->persistenceManager->registerNewObject($proxy);
    }

    /**
     * After returning advice, generates the value hash for the object
     *
     * @param JoinPointInterface $joinPoint The current join point
     * @return void
     * @Flow\After("Neos\Flow\Persistence\Aspect\PersistenceMagicAspect->isNonEmbeddedValueObject && method(.*->__construct()) && filter(Neos\Flow\Persistence\Doctrine\Mapping\Driver\FlowAnnotationDriver)")
     */
    public function generateValueHash(JoinPointInterface $joinPoint)
    {
        $proxy = $joinPoint->getProxy();
        $proxyClassName = get_class($proxy);
        $hashSourceParts = [];

        $classSchema = $this->reflectionService->getClassSchema($proxyClassName);
        foreach ($classSchema->getProperties() as $property => $propertySchema) {
            // Currently, private properties are transient. Should this behaviour change, they need to be included
            // in the value hash generation
            if ($classSchema->isPropertyTransient($property)
                || $this->reflectionService->isPropertyPrivate($proxyClassName, $property)) {
                continue;
            }

            $propertyValue = ObjectAccess::getProperty($proxy, $property, true);

            if (is_object($propertyValue) === true) {
                // The persistence manager will return NULL if the given object is unknown to persistence
                $propertyValue = ($this->persistenceManager->getIdentifierByObject($propertyValue)) ?: $propertyValue;
            }

            $hashSourceParts[$property] = $propertyValue;
        }

        ksort($hashSourceParts);

        $hashSourceParts['__class_name__'] = $proxyClassName;
        $serializedSource = ($this->useIgBinary === true) ? igbinary_serialize($hashSourceParts) : serialize($hashSourceParts);

        $proxy = $joinPoint->getProxy();
        ObjectAccess::setProperty($proxy, 'Persistence_Object_Identifier', sha1($serializedSource), true);
    }

    /**
     * Mark object as cloned after cloning.
     *
     * Note: this is not used by anything in the Flow base distribution,
     * but might be needed by custom backends (like Neos.CouchDB).
     *
     * @param JoinPointInterface $joinPoint
     * @return void
     * @Flow\AfterReturning("Neos\Flow\Persistence\Aspect\PersistenceMagicAspect->isEntityOrValueObject && method(.*->__clone())")
     */
    public function cloneObject(JoinPointInterface $joinPoint)
    {
        $joinPoint->getProxy()->Flow_Persistence_clone = true;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Persistence\Aspect;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Adds the aspect of persistence magic to relevant objects
 * @\Neos\Flow\Annotations\Scope("singleton")
 * @\Neos\Flow\Annotations\Aspect
 * @\Neos\Flow\Annotations\Introduce(pointcutExpression="Neos\Flow\Persistence\Aspect\PersistenceMagicAspect->isEntityOrValueObject", interfaceName="Neos\Flow\Persistence\Aspect\PersistenceMagicInterface")
 */
class PersistenceMagicAspect extends PersistenceMagicAspect_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Flow\Persistence\Aspect\PersistenceMagicAspect') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', $this);
        if ('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect' === get_class($this)) {
            $this->Flow_Proxy_injectProperties();
        }

        $isSameClass = get_class($this) === 'Neos\Flow\Persistence\Aspect\PersistenceMagicAspect';
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
  'useIgBinary' => 'boolean',
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'reflectionService' => 'Neos\\Flow\\Reflection\\ReflectionService',
  'Persistence_Object_Identifier' => 'string',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'Neos\Flow\Persistence\Aspect\PersistenceMagicAspect') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
            $result = NULL;

        $isSameClass = get_class($this) === 'Neos\Flow\Persistence\Aspect\PersistenceMagicAspect';
        $classParents = class_parents($this);
        $classImplements = class_implements($this);
        $isClassProxy = array_search('Neos\Flow\Persistence\Aspect\PersistenceMagicAspect', $classParents) !== FALSE && array_search('Doctrine\ORM\Proxy\Proxy', $classImplements) !== FALSE;

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
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Reflection\ReflectionService', 'Neos\Flow\Reflection\ReflectionService', 'reflectionService', '464c26aa94c66579c050985566cbfc1f', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Reflection\ReflectionService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
  1 => 'reflectionService',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Persistence/Aspect/PersistenceMagicAspect.php
#