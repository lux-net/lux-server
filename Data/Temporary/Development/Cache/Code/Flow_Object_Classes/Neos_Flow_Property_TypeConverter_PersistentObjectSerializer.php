<?php 
namespace Neos\Flow\Property\TypeConverter;

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
use Neos\Flow\Persistence\Aspect\PersistenceMagicInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;

/**
 * This converter transforms persistent objects to strings by returning their (technical) identifier.
 *
 * Unpersisted changes to an object are not serialized, because only the persistence identifier is taken into account
 * as the serialized value.
 *
 * @Flow\Scope("singleton")
 */
class PersistentObjectSerializer_Original extends AbstractTypeConverter
{
    /**
     * @var array
     */
    protected $sourceTypes = [PersistenceMagicInterface::class];

    /**
     * @var string
     */
    protected $targetType = 'string';

    /**
     * @var integer
     */
    protected $priority = 1;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * Convert an entity or valueobject to a string representation (by using the identifier)
     *
     * @param object $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface $configuration
     * @return object the target type
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        $identifier = $this->persistenceManager->getIdentifierByObject($source);
        return $identifier;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Flow\Property\TypeConverter;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This converter transforms persistent objects to strings by returning their (technical) identifier.
 * 
 * Unpersisted changes to an object are not serialized, because only the persistence identifier is taken into account
 * as the serialized value.
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class PersistentObjectSerializer extends PersistentObjectSerializer_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'Neos\Flow\Property\TypeConverter\PersistentObjectSerializer') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Property\TypeConverter\PersistentObjectSerializer', $this);
        if ('Neos\Flow\Property\TypeConverter\PersistentObjectSerializer' === get_class($this)) {
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
  'sourceTypes' => 'array',
  'targetType' => 'string',
  'priority' => 'integer',
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
        if (get_class($this) === 'Neos\Flow\Property\TypeConverter\PersistentObjectSerializer') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('Neos\Flow\Property\TypeConverter\PersistentObjectSerializer', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('Neos\Flow\Persistence\PersistenceManagerInterface', 'Neos\Flow\Persistence\Doctrine\PersistenceManager', 'persistenceManager', '8a72b773ea2cb98c2933df44c659da06', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('Neos\Flow\Persistence\PersistenceManagerInterface'); });
        $this->Flow_Injected_Properties = array (
  0 => 'persistenceManager',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Framework/Neos.Flow/Classes/Property/TypeConverter/PersistentObjectSerializer.php
#