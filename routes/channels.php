<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
| Private channel authorization callbacks for Chariot real-time features.
|--------------------------------------------------------------------------
*/

// Default user channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Driver listens for incoming ride requests on their private channel
Broadcast::channel('driver.{driverId}', function ($user, $driverId) {
    return (int) $user->id === (int) $driverId && $user->isDriver();
});

// Rider listens for ride status updates on their private channel
Broadcast::channel('rider.{riderId}', function ($user, $riderId) {
    return (int) $user->id === (int) $riderId;
});
