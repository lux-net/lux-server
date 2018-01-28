<?php

namespace AgzHack\Lux\Command;

use AgzHack\Geo\Domain\Model\Coordinate;
use AgzHack\Lux\Domain\Model\LightMarker;
use AgzHack\Lux\Domain\Repository\LightMarkerDoctrineRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * @Flow\Scope("singleton")
 */
class LuxCommandController extends CommandController
{

    /**
     * @var LightMarkerDoctrineRepository
     * @Flow\Inject
     */
    protected $lightMarkerDoctrineRepository;


    public function testCommand()
    {
        $marker = new LightMarker(new Coordinate(-12.225872, -38.964673));
        $result = $this->lightMarkerDoctrineRepository->findNearestMarker($marker);
        echo \Neos\Flow\var_dump($result);
    }
}
