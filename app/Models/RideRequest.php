<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RideRequest extends Model
{
    protected $fillable = [
        'ride_id',
        'rider_id',
        'status',
        'pickup_note',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    // ── Methods ──────────────────────────────────────────────────────

    public function accept(): void
    {
        $this->update([
            'status'       => 'accepted',
            'responded_at' => now(),
        ]);
        // Decrement available seats on the ride
        $this->ride->decrementSeats();
    }

    public function decline(): void
    {
        $this->update([
            'status'       => 'declined',
            'responded_at' => now(),
        ]);
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'   => 'Pending',
            'accepted'  => 'Accepted',
            'declined'  => 'Declined',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
            default     => ucfirst($this->status),
        };
    }

    public function statusClass(): string
    {
        return match($this->status) {
            'pending'   => 'warning',
            'accepted'  => 'success',
            'declined'  => 'danger',
            'cancelled' => 'secondary',
            'completed' => 'info',
            default     => 'secondary',
        };
    }
}
