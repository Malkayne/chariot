<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\Zone;
use Illuminate\View\View;

class DriverDashboardController extends Controller
{
    /**
     * Driver main dashboard — shows current ride, pending requests, quick stats.
     */
    public function index(): View
    {
        $user    = auth()->user();
        $profile = $user->driverProfile;

        // Current active or full ride
        $activeRide = Ride::with(['fromZone', 'toZone', 'requests.rider'])
            ->where('driver_id', $user->id)
            ->whereIn('status', ['active', 'full'])
            ->latest()
            ->first();

        // Pending requests on the active ride
        $pendingRequests = $activeRide
            ? $activeRide->pendingRequests()->with('rider')->get()
            : collect();

        // Accepted riders on the active ride
        $acceptedRiders = $activeRide
            ? $activeRide->acceptedRiders()->with('rider')->get()
            : collect();

        // Stats
        $totalTrips      = Ride::where('driver_id', $user->id)->where('status', 'completed')->count();
        $totalPassengers = \App\Models\RideRequest::whereHas('ride', fn($q) => $q->where('driver_id', $user->id))
            ->where('status', 'completed')->count();

        $zones = Zone::active()->orderBy('name')->get();

        return view('driver.dashboard', compact(
            'user', 'profile', 'activeRide',
            'pendingRequests', 'acceptedRiders',
            'totalTrips', 'totalPassengers', 'zones'
        ));
    }

    /**
     * Requests page — pending and accepted requests for the driver's current ride.
     */
    public function requests(): View
    {
        $user    = auth()->user();
        $profile = $user->driverProfile;

        $activeRide = Ride::with(['fromZone', 'toZone'])
            ->where('driver_id', $user->id)
            ->whereIn('status', ['active', 'full'])
            ->latest()
            ->first();

        $pendingRequests  = $activeRide
            ? $activeRide->requests()->with('rider')->where('status', 'pending')->latest()->get()
            : collect();

        $acceptedRequests = $activeRide
            ? $activeRide->requests()->with('rider')->where('status', 'accepted')->latest()->get()
            : collect();

        return view('driver.requests', compact(
            'activeRide', 'profile', 'pendingRequests', 'acceptedRequests'
        ));
    }

    /**
     * Trip history page — completed and cancelled rides.
     */
    public function history(): View
    {
        $user = auth()->user();

        $rides = Ride::with(['fromZone', 'toZone', 'acceptedRiders.rider'])
            ->where('driver_id', $user->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->latest()
            ->paginate(15);

        return view('driver.history', compact('rides'));
    }
}
