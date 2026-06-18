<?php

namespace App\Notifications;

use App\Models\RideRequest;
use Illuminate\Notifications\Notification;

class RideAcceptedNotification extends Notification
{
    public function __construct(public readonly RideRequest $rideRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Ride Accepted! 🎉',
            'body'  => "Your ride request has been accepted. Get to the pickup point.",
            'type'  => 'ride_accepted',
            'data'  => [
                'request_id' => $this->rideRequest->id,
                'ride_id'    => $this->rideRequest->ride_id,
            ],
        ];
    }
}
