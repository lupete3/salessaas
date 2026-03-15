<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'store_id',
        'user_id',
        'customer_id',
        'uuid',
        'sale_number',
        'total_amount',
        'discount',
        'final_amount',
        'amount_paid',
        'change_given',
        'payment_method',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_given' => 'decimal:2',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeForStore($query, ?int $storeId)
    {
        if (is_null($storeId)) {
            return $query;
        }
        return $query->where('store_id', $storeId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /** Génère le prochain numéro de vente */
    public static function generateSaleNumber(int $storeId): string
    {
        $count = static::where('store_id', $storeId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return 'VTE-' . now()->format('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
