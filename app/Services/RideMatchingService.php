<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Support\Collection;

class RideMatchingService
{
    public function __construct(private LocationService $location) {}

    /**
     * Find all active rides going to $toZoneId, optionally filtered by
     * proximity to the rider's current GPS coordinates.
     *
     * Results are sorted by distance (nearest first).
     */
    public function findMatches(
        User $rider,
        int $toZoneId,
        ?float $lat = null,
        ?float $lng = null,
        int $radiusKm = 3
    ): Collection {
        $query = Ride::with(['driver.driverProfile', 'fromZone', 'toZone'])
            ->active()
            ->where('to_zone_id', $toZoneId);

        $rides = $query->get()->filter(function (Ride $ride) use ($lat, $lng, $radiusKm, $rider) {
            // Exclude rides the rider has already requested
            $alreadyRequested = $ride->requests()
                ->where('rider_id', $rider->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->exists();

            if ($alreadyRequested) {
                return false;
            }

            // Must have seats
            if (! $ride->hasAvailableSeats()) {
                return false;
            }

            // If caller provided coordinates, filter by driver proximity
            if ($lat !== null && $lng !== null) {
                $profile = $ride->driver?->driverProfile;
                if (! $profile?->hasLocation()) {
                    return false;
                }
                return $this->location->withinRadius(
                    $lat, $lng,
                    $profile->current_lat, $profile->current_lng,
                    $radiusKm
                );
            }

            return true;
        });

        // Sort by distance if coordinates provided
        if ($lat !== null && $lng !== null) {
            $rides = $rides->sortBy(function (Ride $ride) use ($lat, $lng) {
                $profile = $ride->driver?->driverProfile;
                if (! $profile?->hasLocation()) {
                    return PHP_FLOAT_MAX;
                }
                return $this->location->distanceKm(
                    $lat, $lng,
                    $profile->current_lat, $profile->current_lng
                );
            });
        }

        return $rides->values();
    }
}
