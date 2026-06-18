<?php

namespace App\Http\Controllers\Api;

use App\Events\RideRequested;
use App\Events\RideStatusChanged;
use App\Http\Controllers\Controller;
use App\Jobs\NotifyDriverOfRequest;
use App\Models\Notification;
use App\Models\Ride;
use App\Models\RideRequest;
use App\Models\Zone;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RideController extends Controller
{
    public function __construct(private LocationService $location) {}

    // ── DRIVER: Create a ride ─────────────────────────────────────────

    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'from_zone_id'    => ['required', 'exists:zones,id'],
            'to_zone_id'      => ['required', 'exists:zones,id', 'different:from_zone_id'],
            'available_seats' => ['required', 'integer', 'min:1', 'max:30'],
            'departing_at'    => ['nullable', 'date'],
            'notes'           => ['nullable', 'string', 'max:500'],
            'pickup_lat'      => ['nullable', 'numeric'],
            'pickup_lng'      => ['nullable', 'numeric'],
        ]);

        $user = $request->user();

        // Cancel any existing active ride first
        Ride::where('driver_id', $user->id)
            ->whereIn('status', ['active', 'full'])
            ->update(['status' => 'cancelled']);

        $ride = Ride::create([
            'driver_id'       => $user->id,
            'from_zone_id'    => $request->from_zone_id,
            'to_zone_id'      => $request->to_zone_id,
            'total_seats'     => $request->available_seats,
            'available_seats' => $request->available_seats,
            'pickup_lat'      => $request->pickup_lat ?? $user->driverProfile?->current_lat,
            'pickup_lng'      => $request->pickup_lng ?? $user->driverProfile?->current_lng,
            'departing_at'    => $request->departing_at,
            'notes'           => $request->notes,
            'status'          => 'active',
        ]);

        return response()->json(
            $ride->load(['fromZone', 'toZone']),
            201
        );
    }

    // ── DRIVER: Cancel current ride ───────────────────────────────────

    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();

        $ride = Ride::where('driver_id', $user->id)
            ->whereIn('status', ['active', 'full'])
            ->first();

        if (! $ride) {
            return response()->json(['error' => 'No active ride found.'], 404);
        }

        $ride->update(['status' => 'cancelled']);

        // Notify all pending/accepted riders
        $ride->requests()->whereIn('status', ['pending', 'accepted'])->each(function ($rq) {
            Notification::create([
                'user_id' => $rq->rider_id,
                'type'    => 'ride_cancelled',
                'title'   => 'Ride Cancelled',
                'body'    => 'The driver has cancelled the ride.',
                'data'    => ['ride_id' => $rq->ride_id],
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Ride cancelled.']);
    }

    // ── DRIVER: Accept a request ──────────────────────────────────────

    public function acceptRequest(Request $request, RideRequest $rideRequest): JsonResponse
    {
        $driver = $request->user();

        if ($rideRequest->ride->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorised.'], 403);
        }

        if ($rideRequest->status !== 'pending') {
            return response()->json(['error' => 'Request is no longer pending.'], 409);
        }

        if ($rideRequest->ride->isFull()) {
            return response()->json(['error' => 'Ride is full.'], 409);
        }

        $rideRequest->accept();

        // Notify rider
        Notification::create([
            'user_id' => $rideRequest->rider_id,
            'type'    => 'ride_accepted',
            'title'   => 'Ride Accepted 🎉',
            'body'    => 'Your ride request has been accepted! Head to the pickup point.',
            'data'    => ['request_id' => $rideRequest->id, 'ride_id' => $rideRequest->ride_id],
        ]);

        broadcast(new RideStatusChanged($rideRequest))->toOthers();

        return response()->json(['success' => true, 'status' => 'accepted']);
    }

    // ── DRIVER: Decline a request ─────────────────────────────────────

    public function declineRequest(Request $request, RideRequest $rideRequest): JsonResponse
    {
        $driver = $request->user();

        if ($rideRequest->ride->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorised.'], 403);
        }

        if ($rideRequest->status !== 'pending') {
            return response()->json(['error' => 'Request is no longer pending.'], 409);
        }

        $rideRequest->decline();

        // Notify rider
        Notification::create([
            'user_id' => $rideRequest->rider_id,
            'type'    => 'ride_declined',
            'title'   => 'Ride Declined',
            'body'    => 'Your ride request was not accepted this time. Try another ride.',
            'data'    => ['request_id' => $rideRequest->id],
        ]);

        broadcast(new RideStatusChanged($rideRequest))->toOthers();

        return response()->json(['success' => true, 'status' => 'declined']);
    }

    // ── DRIVER: Complete a ride ───────────────────────────────────────

    public function complete(Request $request, Ride $ride): JsonResponse
    {
        $driver = $request->user();

        if ($ride->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorised.'], 403);
        }

        $ride->update(['status' => 'completed']);

        // Mark all accepted requests as completed
        $ride->acceptedRiders()->update(['status' => 'completed']);

        // Set driver back to available
        $driver->driverProfile?->update(['status' => 'available', 'is_available' => true]);

        return response()->json(['success' => true, 'message' => 'Ride completed.']);
    }

    // ── RIDER: Find nearby rides ──────────────────────────────────────

    public function nearby(Request $request): JsonResponse
    {
        $lat  = $request->float('lat', 0);
        $lng  = $request->float('lng', 0);
        $toId = $request->integer('to_zone_id', 0);

        $query = Ride::with(['driver.driverProfile', 'fromZone', 'toZone'])
            ->active();

        if ($toId) {
            $query->where('to_zone_id', $toId);
        }

        $rides = $query->get()->filter(function (Ride $ride) use ($lat, $lng) {
            if ($lat == 0 && $lng == 0) {
                return true; // No GPS — return all
            }

            $profile = $ride->driver?->driverProfile;
            if (! $profile?->hasLocation()) {
                return true; // Include rides without location
            }

            return $this->location->withinRadius(
                $lat, $lng,
                $profile->current_lat, $profile->current_lng,
                3
            );
        })->values();

        return response()->json($rides);
    }

    // ── RIDER: Request a ride ─────────────────────────────────────────

    public function requestRide(Request $request, Ride $ride): JsonResponse
    {
        $request->validate([
            'pickup_note' => ['nullable', 'string', 'max:300'],
        ]);

        $rider = $request->user();

        if (! $ride->hasAvailableSeats()) {
            return response()->json(['error' => 'This ride is full.'], 409);
        }

        // Check if rider already has a pending/accepted request on this ride
        $exists = RideRequest::where('ride_id', $ride->id)
            ->where('rider_id', $rider->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'You already have a request for this ride.'], 409);
        }

        $rideRequest = RideRequest::create([
            'ride_id'     => $ride->id,
            'rider_id'    => $rider->id,
            'status'      => 'pending',
            'pickup_note' => $request->pickup_note,
        ]);

        // Notify driver (queued)
        NotifyDriverOfRequest::dispatch($rideRequest);

        // Broadcast to driver in real-time
        broadcast(new RideRequested($rideRequest))->toOthers();

        return response()->json($rideRequest->load('rider'), 201);
    }

    // ── RIDER: Cancel own request ─────────────────────────────────────

    public function cancelRequest(Request $request, RideRequest $rideRequest): JsonResponse
    {
        if ($rideRequest->rider_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorised.'], 403);
        }

        if (! in_array($rideRequest->status, ['pending', 'accepted'])) {
            return response()->json(['error' => 'Cannot cancel a completed or already-cancelled request.'], 409);
        }

        // If was accepted, free up the seat
        if ($rideRequest->status === 'accepted') {
            $rideRequest->ride->increment('available_seats');
            if ($rideRequest->ride->status === 'full') {
                $rideRequest->ride->update(['status' => 'active']);
            }
        }

        $rideRequest->update(['status' => 'cancelled']);

        return response()->json(['success' => true]);
    }
}
