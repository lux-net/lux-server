<?php

namespace AgzHack\Lux\Service;

use AgzHack\Auth\Service\AuthService;
use AgzHack\Lux\Domain\Model\LightMarker;
use AgzHack\Lux\Domain\Repository\LightMarkerDoctrineRepository;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class LightMarkerService
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
