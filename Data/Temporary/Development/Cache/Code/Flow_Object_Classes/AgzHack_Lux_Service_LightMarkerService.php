<?php 

namespace AgzHack\Lux\Service;

use AgzHack\Auth\Service\AuthService;
use AgzHack\Lux\Domain\Model\LightMarker;
use AgzHack\Lux\Domain\Repository\LightMarkerDoctrineRepository;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class LightMarkerService_Original
{

    /**
     * @var LightMarkerDoctrineRepository
     * @Flow\Inject
     */
    protected $lightMarkerDoctrineRepository;

    /**
     * @var AuthService
     * @Flow\Inject
     */
    protected $authService;

    /**
     * @param LightMarker $lightMarker
     * @return LightMarker
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     * @throws \Neos\Flow\Persistence\Exception\InvalidQueryException
     */
    public function addDiscreteMarker(LightMarker $lightMarker)
    {
        try {
            $lightMarker->setUserAccount(
                $this->authService->getAuthenticatedUserAccount()
            );
        } catch (\Exception $e) {
        }

        $existentLightMarker = $this->lightMarkerDoctrineRepository->findNearestMarker($lightMarker);
        /** @var LightMarker $existentLightMarker */
        if ($existentLightMarker !== null) {
            $existentLightMarker = $existentLightMarker[0];
            $existentLightMarker->addSubMarker($lightMarker);
            $this->lightMarkerDoctrineRepository->update($existentLightMarker);
            return $existentLightMarker;
        }

        $this->lightMarkerDoctrineRepository->add($lightMarker);

        return $lightMarker;
    }
}

#
# Start of Flow generated Proxy code
#
namespace AgzHack\Lux\Service;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * 
 * @\Neos\Flow\Annotations\Scope("singleton")
 */
class LightMarkerService extends LightMarkerService_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait, \Neos\Flow\ObjectManagement\DependencyInjection\PropertyInjectionTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __construct()
    {
        if (get_class($this) === 'AgzHack\Lux\Service\LightMarkerService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('AgzHack\Lux\Service\LightMarkerService', $this);
        if ('AgzHack\Lux\Service\LightMarkerService' === get_class($this)) {
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
  'lightMarkerDoctrineRepository' => 'AgzHack\\Lux\\Domain\\Repository\\LightMarkerDoctrineRepository',
  'authService' => 'AgzHack\\Auth\\Service\\AuthService',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {
        if (get_class($this) === 'AgzHack\Lux\Service\LightMarkerService') \Neos\Flow\Core\Bootstrap::$staticObjectManager->setInstance('AgzHack\Lux\Service\LightMarkerService', $this);

        $this->Flow_setRelatedEntities();
        $this->Flow_Proxy_injectProperties();
    }

    /**
     * Autogenerated Proxy Method
     */
    private function Flow_Proxy_injectProperties()
    {
        $this->Flow_Proxy_LazyPropertyInjection('AgzHack\Lux\Domain\Repository\LightMarkerDoctrineRepository', 'AgzHack\Lux\Domain\Repository\LightMarkerDoctrineRepository', 'lightMarkerDoctrineRepository', '2a2deb19c4005cbb765b9eda8d97e760', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('AgzHack\Lux\Domain\Repository\LightMarkerDoctrineRepository'); });
        $this->Flow_Proxy_LazyPropertyInjection('AgzHack\Auth\Service\AuthService', 'AgzHack\Auth\Service\AuthService', 'authService', 'aa1a6ca5bcacadf92e43f2c40e7c3c2e', function() { return \Neos\Flow\Core\Bootstrap::$staticObjectManager->get('AgzHack\Auth\Service\AuthService'); });
        $this->Flow_Injected_Properties = array (
  0 => 'lightMarkerDoctrineRepository',
  1 => 'authService',
);
    }
}
# PathAndFilename: /var/www/lux/Packages/Application/AgzHack.Lux/Classes/Service/LightMarkerService.php
#