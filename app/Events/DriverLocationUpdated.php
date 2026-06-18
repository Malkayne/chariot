<?php

namespace App\Events;

use App\Models\DriverProfile;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly DriverProfile $profile) {}

    public function broadcastOn(): array
    {
        return [new Channel('drivers')];
    }

    public function broadcastWith(): array
    {
        return [
            'driver_id' => $this->profile->user_id,
            'lat'       => $this->profile->current_lat,
            'lng'       => $this->profile->current_lng,
            'status'    => $this->profile->status,
        ];
    }

    public function broadcastAs(): string
    {
        return 'DriverLocationUpdated';
    }
}
