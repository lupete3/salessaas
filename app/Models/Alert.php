<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Alert extends Model
{
    protected $fillable = [
        'store_id',
        'type',
        'title',
        'message',
        'alertable_id',
        'alertable_type',
        'is_read',
        'severity',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /** Polymorphic: peut pointer vers Product, Supplier, Purchase */
    public function alertable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForStore($query, ?int $storeId)
    {
        if (is_null($storeId)) {
            return $query;
        }
        return $query->where('store_id', $storeId);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function severityClass(): string
    {
        return match ($this->severity) {
            'danger' => 'alert-danger',
            'warning' => 'alert-warning',
            default => 'alert-info',
        };
    }

    public function badgeClass(): string
    {
        return match ($this->severity) {
            'danger' => 'bg-danger',
            'warning' => 'bg-warning text-dark',
            default => 'bg-info',
        };
    }
}
