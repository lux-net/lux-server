<?php

namespace AgzHack\Geo\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\ValueObject(embedded=true)
 */
class Coordinate
{
    const EARTH_RADIUS = 6378137;

    /**
     * @var float
     */
    protected $latitude;

    /**
     * @var float
     */
    protected $longitude;

    /**
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @return string
     */
    public function getLatitudeLongitudeString()
    {
        return "{$this->latitude},$this->longitude";
    }

    /**
     * @param Coordinate $anotherCoordinate
     * @return mixed
     */
    public function distance(Coordinate $anotherCoordinate)
    {
        $lat1 = pi() * $this->latitude / 180;
        $lon1 = pi() * $this->longitude / 180;
        $lat2 = pi() * $anotherCoordinate->getLatitude() / 180;
        $lon2 = pi() * $anotherCoordinate->getLongitude() / 180;

        return self::EARTH_RADIUS * acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lon2 - $lon1));
    }

    public function __clone()
    {
        return new Coordinate($this->getLatitude(), $this->getLongitude());
    }
}
