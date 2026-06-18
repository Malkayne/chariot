<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_model',
        'vehicle_color',
        'plate_number',
        'total_seats',
        'is_available',
        'current_lat',
        'current_lng',
        'location_updated_at',
        'status',
    ];

    protected $casts = [
        'is_available'        => 'boolean',
        'current_lat'         => 'float',
        'current_lng'         => 'float',
        'location_updated_at' => 'datetime',
        'total_seats'         => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activeRide(): HasOne
    {
        return $this->hasOne(Ride::class, 'driver_id', 'user_id')
                    ->whereIn('status', ['active', 'full']);
    }

    // ── Methods ──────────────────────────────────────────────────────

    public function updateLocation(float $lat, float $lng): void
    {
        $this->update([
            'current_lat'         => $lat,
            'current_lng'         => $lng,
            'location_updated_at' => now(),
        ]);
    }

    public function toggleAvailability(): void
    {
        if ($this->status === 'offline') {
            $this->update(['status' => 'available', 'is_available' => true]);
        } else {
            $this->update(['status' => 'offline', 'is_available' => false]);
        }
    }

    public function goOnTrip(): void
    {
        $this->update(['status' => 'on_trip', 'is_available' => false]);
    }

    public function hasLocation(): bool
    {
        return $this->current_lat !== null && $this->current_lng !== null;
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'available' => 'Available',
            'on_trip'   => 'On Trip',
            default     => 'Offline',
        };
    }

    public function statusClass(): string
    {
        return match($this->status) {
            'available' => 'success',
            'on_trip'   => 'warning',
            default     => 'secondary',
        };
    }
}
