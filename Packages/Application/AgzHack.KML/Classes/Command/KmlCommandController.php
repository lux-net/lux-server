<?php

namespace AgzHack\KML\Command;

use AgzHack\KML\Domain\Builder\KMLBuilder;
use AgzHack\Lux\Domain\Model\LightMarker;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * @Flow\Scope("singleton")
 */
class KmlCommandController extends CommandController
{
    public function testCommand()
    {
        $kmlBuilder = new KMLBuilder();
        $kmlBuilder->addMarker("ernesto", new Coordinate(1000, 2000));
        echo $kmlBuilder->getKMLXml();
    }
}
