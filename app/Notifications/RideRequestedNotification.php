<?php

namespace App\Notifications;

use App\Models\RideRequest;
use Illuminate\Notifications\Notification;

class RideRequestedNotification extends Notification
{
    public function __construct(public readonly RideRequest $rideRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'      => 'New Ride Request',
            'body'       => "{$this->rideRequest->rider->name} wants to join your ride.",
            'type'       => 'ride_request',
            'data'       => ['request_id' => $this->rideRequest->id],
        ];
    }
}
