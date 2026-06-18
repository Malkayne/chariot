<?php

namespace App\Services;

class LocationService
{
    private const EARTH_RADIUS_KM = 6371;

    /**
     * Haversine formula — returns true if the two points are within radiusKm.
     */
    public function withinRadius(
        ?float $lat1,
        ?float $lng1,
        ?float $lat2,
        ?float $lng2,
        int $radiusKm = 3
    ): bool {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return false;
        }

        return $this->distanceKm($lat1, $lng1, $lat2, $lng2) <= $radiusKm;
    }

    /**
     * Return the distance in km between two GPS coordinates.
     */
    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Return a human-readable distance string.
     */
    public function distanceLabel(float $lat1, float $lng1, float $lat2, float $lng2): string
    {
        $km = $this->distanceKm($lat1, $lng1, $lat2, $lng2);

        if ($km < 1) {
            return round($km * 1000) . ' m away';
        }

        return round($km, 1) . ' km away';
    }
}
