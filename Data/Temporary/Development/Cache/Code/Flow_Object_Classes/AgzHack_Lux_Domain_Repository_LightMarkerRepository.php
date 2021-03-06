<?php 

namespace AgzHack\Lux\Domain\Repository;

/*
 * This file is part of the AgzHack.Lux package.
 */

use AgzHack\Geo\Domain\Model\Coordinate;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class LightMarkerRepository_Original extends Repository
{

    /**
     * @param Coordinate $northEast
     * @param Coordinate $southWest
     * @return \Neos\Flow\Persistence\QueryResultInterface
     * @throws \Neos\Flow\Persistence\Exception\InvalidQueryException
     */
    public function findByBoundaries(Coordinate $northEast, Coordinate $southWest)
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                [
                    $query->lessThanOrEqual('coordinate.latitude', $northEast->getLatitude()),
                    $query->greaterThanOrEqual('coordinate.latitude', $southWest->getLatitude()),
                    $query->greaterThanOrEqual('coordinate.longitude', $northEast->getLongitude()),
                    $query->lessThanOrEqual('coordinate.longitude', $southWest->getLongitude())
                ]
            )
        );

        return $query->execute();
    }
}

#
# Start of Flow generated Proxy code
#
namespace AgzHack\Lux\Domain\Repository;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class LightMarkerRepository extends LightMarkerRepository_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'AgzHack\Lux\Domain\Repository\LightMarkerRepository') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('AgzHack\Lux\Domain\Repository\LightMarkerRepository', $this);
        parent::__construct();
        if ('AgzHack\Lux\Domain\Repository\LightMarkerRepository' === get_class($this)) {
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
  'persistenceManager' => 'Neos\\Flow\\Persistence\\PersistenceManagerInterface',
  'entityClassName' => 'string',
  'defaultOrderings' => 'array',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'AgzHack\Lux\Domain\Repository\LightMarkerRepository') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('AgzHack\Lux\Domain\Repository\LightMarkerRepository', $this);

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
# PathAndFilename: /var/www/lux/Packages/Application/AgzHack.Lux/Classes/Domain/Repository/LightMarkerRepository.php
#