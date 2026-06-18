<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $fillable = [
        'name',
        'short_code',
        'lat',
        'lng',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lat'       => 'float',
        'lng'       => 'float',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function ridesFrom(): HasMany
    {
        return $this->hasMany(Ride::class, 'from_zone_id');
    }

    public function ridesTo(): HasMany
    {
        return $this->hasMany(Ride::class, 'to_zone_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
