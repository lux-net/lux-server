<?php

namespace AgzHack\KML\Tests\Unit\Domain\Model;

/*
 * This file is part of the AgzHack.KML package.
 */

use AgzHack\KML\Domain\Builder\KMLBuilder;
use AgzHack\KML\Domain\Model\Coordinate;

/**
 * Testcase for Coordinate
 */
class KMLBuilderTest extends \Neos\Flow\Tests\UnitTestCase
{

    /**
     * @test
     */
    public function testIfAddMarkerWorksProperly()
    {
        $kmlBuilder = new KMLBuilder();
        $kmlBuilder->addMarker("ernesto", new Coordinate(1000, 2000));
        echo $kmlBuilder->getKMLXml();
        die;
    }
}
