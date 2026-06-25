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
        $totalUsers = User::whereIn('role', ['rider', 'driver'])->count();
        $totalDrivers = User::drivers()->count();
        $totalRiders = User::riders()->count();
        $activeRides = Ride::whereIn('status', ['active', 'full'])->count();
        $todayRequests = RideRequest::whereDate('created_at', today())->count();
        $pendingVerify = User::where('is_verified', false)->count();

        $recentRides = Ride::with(['driver.driverProfile', 'fromZone', 'toZone'])
            ->latest()
            ->limit(10)
            ->get();

        $recentRequests = RideRequest::with(['rider', 'ride.toZone'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalDrivers',
            'totalRiders',
            'activeRides',
            'todayRequests',
            'pendingVerify',
            'recentRides',
            'recentRequests'
        ));
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

        $total = $query->count();
        $users = $query->with('driverProfile')->latest()->paginate(20)->withQueryString();

        return view('admin.users', compact('users', 'total'));
    }

    /**
     * Rides management — all rides with filter.
     */
    public function rides(Request $request): View
    {
        $query = Ride::with(['driver.driverProfile', 'fromZone', 'toZone', 'requests']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $rides = $query->latest()->paginate(20)->withQueryString();

        $statusCounts = [
            'active'    => Ride::where('status', 'active')->count(),
            'full'      => Ride::where('status', 'full')->count(),
            'completed' => Ride::where('status', 'completed')->count(),
            'cancelled' => Ride::where('status', 'cancelled')->count(),
        ];

        return view('admin.rides', compact('rides', 'statusCounts'));
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
    public function storeZone(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'short_code'  => ['required', 'string', 'max:20', 'unique:zones,short_code'],
            'lat'         => ['required', 'numeric', 'between:-90,90'],
            'lng'         => ['required', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $zone = Zone::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'zone'    => $zone,
                'message' => "Zone \"{$validated['name']}\" added successfully."
            ]);
        }

        return back()->with('success', "Zone \"{$validated['name']}\" added successfully.");
    }

    /**
     * Update an existing zone.
     */
    public function updateZone(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'short_code'  => ['required', 'string', 'max:20', 'unique:zones,short_code,' . $zone->id],
            'lat'         => ['required', 'numeric', 'between:-90,90'],
            'lng'         => ['required', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $zone->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'zone'    => $zone,
                'message' => "Zone \"{$validated['name']}\" updated successfully."
            ]);
        }

        return back()->with('success', "Zone \"{$validated['name']}\" updated successfully.");
    }

    /**
     * Delete a zone.
     */
    public function deleteZone(Zone $zone)
    {
        $zoneName = $zone->name;
        $zone->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Zone \"{$zoneName}\" deleted successfully."
            ]);
        }

        return back()->with('success', "Zone \"{$zoneName}\" deleted successfully.");
    }

    /**
     * Toggle a user's active status.
     */
    public function toggleUser(User $user)
    {
        if ($user->id === auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You cannot deactivate yourself.'], 403);
            }
            return back()->with('error', "You cannot deactivate yourself.");
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        if (request()->expectsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => $user->is_active,
                'message'   => "{$user->name} has been {$status}."
            ]);
        }

        return back()->with('success', "{$user->name} has been {$status}.");
    }

    /**
     * Manually verify a user's membership.
     */
    public function verifyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You cannot verify yourself.'], 403);
            }
            return back()->with('error', "You cannot verify yourself.");
        }

        $user->update(['is_verified' => true]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$user->name} has been verified."
            ]);
        }

        return back()->with('success', "{$user->name} has been verified.");
    }

    /**
     * Change a user's role.
     */
    public function changeRole(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You cannot change your own role.'], 403);
            }
            return back()->with('error', "You cannot change your own role.");
        }

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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$user->name}'s role changed to {$request->role}."
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
