<?php

namespace App\Events;

use App\Models\RideRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly RideRequest $rideRequest) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('driver.' . $this->rideRequest->ride->driver_id)];
    }

    public function broadcastWith(): array
    {
        return [
            'request_id'  => $this->rideRequest->id,
            'rider_name'  => $this->rideRequest->rider->name,
            'pickup_note' => $this->rideRequest->pickup_note,
            'ride_id'     => $this->rideRequest->ride_id,
        ];
    }

    public function broadcastAs(): string
    {
        return 'RideRequested';
    }
}
