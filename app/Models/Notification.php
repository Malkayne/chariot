<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // ── Methods ──────────────────────────────────────────────────────

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function iconClass(): string
    {
        return match($this->type) {
            'ride_request'  => 'fa-bell text-warning',
            'ride_accepted' => 'fa-circle-check text-success',
            'ride_declined' => 'fa-circle-xmark text-danger',
            'ride_cancelled'=> 'fa-triangle-exclamation text-warning',
            'admin_message' => 'fa-circle-info text-info',
            default         => 'fa-bell text-muted',
        };
    }
}
