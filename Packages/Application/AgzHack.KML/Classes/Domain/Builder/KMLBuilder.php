<?php

namespace AgzHack\KML\Domain\Builder;


use AgzHack\Geo\Domain\Model\Coordinate;

class KMLBuilder
{

    /**
     * @var \DOMDocument
     */
    private $document;

    /**
     * @var \DOMNode
     */
    private $kmlNode;

    public function __construct()
    {
        $this->initDomDocument();
    }

    /**
     * <kml xmlns="http://www.opengis.net/kml/2.2">
     */
    private function initDomDocument()
    {
        $this->document = new \DOMDocument();

        $this->kmlNode = $this->createElement('kml');
        $this->kmlNode->setAttribute('xmlns', 'http://www.opengis.net/kml/2.2');

        $this->document->appendChild($this->kmlNode);
    }

    /**
     * @param $elementName
     * @return \DOMElement
     */
    private function createElement($elementName)
    {
        return $this->document->createElement($elementName);
    }

    /**
     *<Placemark>
     *<name>Meu ponto</name>
     *<styleUrl>#icon-1899-0288D1-nodesc</styleUrl>
     *<Point>
     *<coordinates>
     *      -61.2927246,-8.874217,0
     *</coordinates>
     *</Point>
     *</Placemark>
     *
     * @param string $markerName
     * @param Coordinate $coordinate
     */
    public function addMarker($markerName, Coordinate $coordinate)
    {
        $placeMarkerNode = $this->createElement('Placemark');

        $nameNode = $this->createElement('name');
        $nameNode->appendChild($this->document->createTextNode($markerName));

        $styleUrlNode = $this->createElement('styleUrl');
        $styleUrlNode->appendChild($this->document->createTextNode('#icon-1899-0288D1-nodesc'));

        $pointNode = $this->createElement('Point');
        $coordinateNode = $this->createElement('coordinates');
        $coordinateNode->appendChild($this->document->createTextNode($coordinate->getLatitudeLongitudeString()));

        $pointNode->appendChild($coordinateNode);

        $placeMarkerNode->appendChild($nameNode);
        $placeMarkerNode->appendChild($styleUrlNode);
        $placeMarkerNode->appendChild($pointNode);

        $this->kmlNode->appendChild($placeMarkerNode);
    }

    /**
     * @return string
     */
    public function getKMLXml()
    {
        return $this->document->saveXML();
    }
}
