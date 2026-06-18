<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\RideRequest;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Admin dashboard with platform stats.
     */
    public function dashboard(): View
    {
        $stats = [
            'totalUsers'    => User::whereIn('role', ['rider', 'driver'])->count(),
            'totalDrivers'  => User::drivers()->count(),
            'activeRides'   => Ride::whereIn('status', ['active', 'full'])->count(),
            'todayRequests' => RideRequest::whereDate('created_at', today())->count(),
            'pendingVerify' => User::where('is_verified', false)->count(),
            'totalZones'    => Zone::count(),
        ];

        $recentRides = Ride::with(['driver', 'fromZone', 'toZone'])
            ->latest()
            ->limit(10)
            ->get();

        $recentUsers = User::whereIn('role', ['rider', 'driver'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentRides', 'recentUsers'));
    }

    /**
     * User management — paginated list with search.
     */
    public function users(Request $request): View
    {
        $query = User::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        if ($request->get('unverified')) {
            $query->where('is_verified', false);
        }

        $users = $query->with('driverProfile')->latest()->paginate(20)->withQueryString();

        return view('admin.users', compact('users'));
    }

    /**
     * Rides management — all rides with filter.
     */
    public function rides(Request $request): View
    {
        $query = Ride::with(['driver', 'fromZone', 'toZone']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $rides = $query->latest()->paginate(20)->withQueryString();

        return view('admin.rides', compact('rides'));
    }

    /**
     * Zone management — list and add zones.
     */
    public function zones(): View
    {
        $zones = Zone::orderBy('name')->get();
        return view('admin.zones', compact('zones'));
    }

    /**
     * Create a new zone.
     */
    public function storeZone(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'short_code'  => ['required', 'string', 'max:20', 'unique:zones,short_code'],
            'lat'         => ['required', 'numeric', 'between:-90,90'],
            'lng'         => ['required', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Zone::create($validated);

        return back()->with('success', "Zone \"{$validated['name']}\" added successfully.");
    }

    /**
     * Toggle a user's active status.
     */
    public function toggleUser(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "{$user->name} has been {$status}.");
    }

    /**
     * Manually verify a user's membership.
     */
    public function verifyUser(User $user): RedirectResponse
    {
        $user->update(['is_verified' => true]);

        return back()->with('success', "{$user->name} has been verified.");
    }

    /**
     * Change a user's role.
     */
    public function changeRole(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:rider,driver,admin'],
        ]);

        $user->update(['role' => $request->role]);

        // If promoted to driver, create a blank driver profile if none exists
        if ($request->role === 'driver' && ! $user->driverProfile) {
            $user->driverProfile()->create([
                'vehicle_type'  => 'car',
                'vehicle_model' => 'Not set',
                'vehicle_color' => 'Not set',
                'plate_number'  => 'PENDING-' . $user->id,
                'total_seats'   => 4,
            ]);
        }

        return back()->with('success', "{$user->name}'s role changed to {$request->role}.");
    }

    /**
     * Cancel a ride (admin action).
     */
    public function cancelRide(Ride $ride): RedirectResponse
    {
        $ride->update(['status' => 'cancelled']);
        return back()->with('success', "Ride #{$ride->id} has been cancelled.");
    }
}
