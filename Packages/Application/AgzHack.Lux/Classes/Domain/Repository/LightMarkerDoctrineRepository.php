<?php

namespace AgzHack\Lux\Domain\Repository;

/*
 * This file is part of the AgzHack.Lux package.
 */

use AgzHack\Geo\Domain\Model\Coordinate;
use AgzHack\Lux\Domain\Model\LightMarker;
use DoctrineExtensions\Query\Mysql\Acos;
use DoctrineExtensions\Query\Mysql\Cos;
use DoctrineExtensions\Query\Mysql\Radians;
use DoctrineExtensions\Query\Mysql\Sin;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class LightMarkerDoctrineRepository extends \Neos\Flow\Persistence\Doctrine\Repository
{
    const ENTITY_CLASSNAME = LightMarker::class;

    /**
     * @param LightMarker $marker
     * @param int $maxDistanceInMeters
     * @return LightMarker
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findNearestMarker(LightMarker $marker, $maxDistanceInMeters = 20)
    {
        $this->addNewDoctrineNumericFuncions();

        $earthRadius = Coordinate::EARTH_RADIUS;
        $newLatitude = $marker->getCoordinate()->getLatitude();
        $newLongitude = $marker->getCoordinate()->getLongitude();

        $distanceCalculation = "({$earthRadius} * acos( cos(radians($newLatitude) ) * cos( radians(lightMarker.coordinate.latitude) )
                    * cos( radians(lightMarker.coordinate.longitude) - radians($newLongitude)) + sin(radians(lightMarker.coordinate.latitude)) * sin( radians($newLatitude)))) AS distance";

        $query = $this->getEntityManager()->createQueryBuilder();
        $query
            ->select('lightMarker', $distanceCalculation)
            ->from(LightMarker::class, 'lightMarker')
            ->having('distance < :maxDistanceInMeters and lightMarker.parentMarker is null')
            ->setParameters([
                'maxDistanceInMeters' => $maxDistanceInMeters
            ])
            ->addOrderBy('distance', 'asc');

        return $query->getQuery()->getOneOrNullResult();
    }

    private function addNewDoctrineNumericFuncions()
    {
        $this->getEntityManager()->getConfiguration()->addCustomNumericFunction('COS', Cos::class);
        $this->getEntityManager()->getConfiguration()->addCustomNumericFunction('ACOS', Acos::class);
        $this->getEntityManager()->getConfiguration()->addCustomNumericFunction('RADIANS', Radians::class);
        $this->getEntityManager()->getConfiguration()->addCustomNumericFunction('SIN', Sin::class);
    }
}
