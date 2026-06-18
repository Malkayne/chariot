<?php

namespace App\Http\Controllers\Api;

use App\Events\DriverLocationUpdated;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $user    = $request->user();
        $profile = $user->driverProfile;

        if (! $profile) {
            return response()->json(['error' => 'Driver profile not found.'], 404);
        }

        $profile->updateLocation($request->lat, $request->lng);

        // Broadcast to all riders listening on the 'drivers' public channel
        broadcast(new DriverLocationUpdated($profile))->toOthers();

        return response()->json([
            'success'            => true,
            'location_updated_at'=> $profile->location_updated_at->toISOString(),
        ]);
    }
}
