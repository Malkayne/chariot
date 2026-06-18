<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * Toggle the driver's availability status.
     * offline → available → offline (cycles)
     */
    public function toggleAvailability(Request $request): JsonResponse
    {
        $user    = $request->user();
        $profile = $user->driverProfile;

        if (! $profile) {
            return response()->json(['error' => 'Driver profile not found.'], 404);
        }

        $profile->toggleAvailability();

        return response()->json([
            'status'       => $profile->status,
            'is_available' => $profile->is_available,
            'label'        => $profile->statusLabel(),
        ]);
    }
}
