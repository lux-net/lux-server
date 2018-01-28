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
class LightMarkerRepository extends Repository
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
