<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ride extends Model
{
    protected $fillable = [
        'driver_id',
        'from_zone_id',
        'to_zone_id',
        'pickup_lat',
        'pickup_lng',
        'total_seats',
        'available_seats',
        'status',
        'departing_at',
        'notes',
    ];

    protected $casts = [
        'pickup_lat'   => 'float',
        'pickup_lng'   => 'float',
        'total_seats'  => 'integer',
        'available_seats' => 'integer',
        'departing_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function fromZone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'from_zone_id');
    }

    public function toZone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'to_zone_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(RideRequest::class);
    }

    public function acceptedRiders(): HasMany
    {
        return $this->hasMany(RideRequest::class)->where('status', 'accepted');
    }

    public function pendingRequests(): HasMany
    {
        return $this->hasMany(RideRequest::class)->where('status', 'pending');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'full']);
    }

    public function scopeNearby($query, float $lat, float $lng, int $radiusKm = 3)
    {
        // Rough bounding box for DB-level pre-filter (Haversine applied in PHP)
        $latDelta = $radiusKm / 111.0;
        $lngDelta = $radiusKm / (111.0 * cos(deg2rad($lat)));

        return $query->whereBetween('pickup_lat', [$lat - $latDelta, $lat + $latDelta])
                     ->whereBetween('pickup_lng', [$lng - $lngDelta, $lng + $lngDelta]);
    }

    // ── Methods ──────────────────────────────────────────────────────

    public function hasAvailableSeats(): bool
    {
        return $this->available_seats > 0;
    }

    public function decrementSeats(): void
    {
        $this->decrement('available_seats');
        if ($this->available_seats <= 0) {
            $this->update(['status' => 'full']);
        }
    }

    public function isFull(): bool
    {
        return $this->available_seats <= 0 || $this->status === 'full';
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'active'    => 'Active',
            'full'      => 'Full',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    public function statusClass(): string
    {
        return match($this->status) {
            'active'    => 'success',
            'full'      => 'warning',
            'completed' => 'secondary',
            'cancelled' => 'danger',
            default     => 'secondary',
        };
    }
}
