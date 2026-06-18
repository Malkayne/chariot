<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RiderController extends Controller
{
    /**
     * Rider home / map view — passes active zones for map pins.
     */
    public function home(): View
    {
        $zones = Zone::active()->get();

        // Count pending requests this rider has (for nav badge)
        $pendingCount = auth()->user()
            ->rideRequests()
            ->where('status', 'pending')
            ->count();

        return view('rider.home', compact('zones', 'pendingCount'));
    }

    /**
     * Find a ride — zone selection + results page.
     */
    public function findRide(): View
    {
        $zones = Zone::active()->orderBy('name')->get();

        return view('rider.find-ride', compact('zones'));
    }

    /**
     * My rides — rider's own ride request history.
     */
    public function myRides(): View
    {
        $base = auth()->user()
            ->rideRequests()
            ->with(['ride.driver.driverProfile', 'ride.fromZone', 'ride.toZone']);

        $pendingRequests  = (clone $base)->where('status', 'pending')->latest()->get();
        $acceptedRequests = (clone $base)->where('status', 'accepted')->latest()->get();
        $historyRequests  = (clone $base)
            ->whereIn('status', ['completed', 'declined', 'cancelled'])
            ->latest()
            ->get();

        return view('rider.my-rides', compact('pendingRequests', 'acceptedRequests', 'historyRequests'));
    }

    /**
     * Profile view.
     */
    public function profile(): View
    {
        $user = auth()->user();

        $totalRides  = $user->rideRequests()->where('status', 'completed')->count();
        $activeRides = $user->rideRequests()->where('status', 'accepted')->count();

        return view('rider.profile', compact('user', 'totalRides', 'activeRides'));
    }

    /**
     * Update profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'email'         => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'rccg_member_id'=> ['nullable', 'string', 'max:60'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')
                ->store('profile-photos', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }
}
