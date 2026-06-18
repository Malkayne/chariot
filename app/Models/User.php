<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'rccg_member_id',
        'otp_code',
        'otp_expires_at',
        'is_verified',
        'is_active',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at'    => 'datetime',
        'password'          => 'hashed',
        'is_verified'       => 'boolean',
        'is_active'         => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function driverProfile(): HasOne
    {
        return $this->hasOne(DriverProfile::class);
    }

    public function ridesAsDriver(): HasMany
    {
        return $this->hasMany(Ride::class, 'driver_id');
    }

    public function rideRequests(): HasMany
    {
        return $this->hasMany(RideRequest::class, 'rider_id');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest();
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeDrivers($query)
    {
        return $query->where('role', 'driver');
    }

    public function scopeRiders($query)
    {
        return $query->where('role', 'rider');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isRider(): bool
    {
        return $this->role === 'rider';
    }

    public function isVerified(): bool
    {
        return (bool) $this->is_verified;
    }

    public function avatarInitials(): string
    {
        $parts = explode(' ', trim($this->name));
        $initials = strtoupper(substr($parts[0], 0, 1));
        if (isset($parts[1])) {
            $initials .= strtoupper(substr($parts[1], 0, 1));
        }
        return $initials;
    }

    /**
     * Get or create a single web-session Sanctum token.
     * Avoids accumulating tokens on every page load.
     */
    public function getOrCreateWebToken(): string
    {
        // Revoke old web-session tokens first
        $this->tokens()->where('name', 'web-session')->delete();
        return $this->createToken('web-session')->plainTextToken;
    }
}
