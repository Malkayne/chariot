<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\RideRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyDriverOfRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly RideRequest $rideRequest) {}

    public function handle(): void
    {
        $driver = $this->rideRequest->ride->driver;
        $rider  = $this->rideRequest->rider;

        Notification::create([
            'user_id' => $driver->id,
            'type'    => 'ride_request',
            'title'   => 'New Ride Request',
            'body'    => "{$rider->name} wants to join your ride.",
            'data'    => [
                'request_id' => $this->rideRequest->id,
                'ride_id'    => $this->rideRequest->ride_id,
                'rider_name' => $rider->name,
            ],
        ]);

        // Send email alert to driver
        if ($driver->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($driver->email)->send(new \App\Mail\RideRequestMail($this->rideRequest));
            } catch (\Exception $e) {
                logger()->error("Failed to send ride request email to driver {$driver->email}: " . $e->getMessage());
            }
        }
    }
}
