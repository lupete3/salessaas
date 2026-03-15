<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'store_id',
        'supplier_id',
        'user_id',
        'purchase_number',
        'total_amount',
        'amount_paid',
        'balance_due',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function scopeForStore($query, ?int $storeId)
    {
        if (is_null($storeId)) {
            return $query;
        }
        return $query->where('store_id', $storeId);
    }

    public static function generatePurchaseNumber(int $storeId): string
    {
        $count = static::where('store_id', $storeId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return 'ACH-' . now()->format('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => __('purchases.status_paid'),
            'partial' => __('purchases.status_partial'),
            'pending' => __('purchases.status_pending'),
            'cancelled' => __('purchases.status_cancelled'),
            default => $this->status,
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'paid' => 'bg-success',
            'partial' => 'bg-warning text-dark',
            'pending' => 'bg-danger',
            'cancelled' => 'bg-secondary',
            default => 'bg-light text-dark',
        };
    }
}
