<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'store_id',
        'product_id',
        'user_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reason',
        'reference',
        'reference_type',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeForStore($query, ?int $storeId)
    {
        if (is_null($storeId)) {
            return $query;
        }
        return $query->where('store_id', $storeId);
    }

    /** Labels traduits pour le type de mouvement */
    public function typeLabel(): string
    {
        return match ($this->type) {
            'in' => __('stock.movement_in'),
            'out' => __('stock.movement_out'),
            'adjust' => __('stock.movement_adjust'),
            'adjust_in' => __('stock.movement_adjust_in'),
            'adjust_out' => __('stock.movement_adjust_out'),
            'expired' => __('stock.movement_expired'),
            'return' => __('stock.movement_return'),
            'sale' => __('stock.movement_sale'),
            'purchase' => __('stock.movement_purchase'),
            default => $this->type,
        };
    }

    public function typeBadgeClass(): string
    {
        return match ($this->type) {
            'in', 'purchase' => 'bg-success-subtle text-success',
            'out', 'sale' => 'bg-primary-subtle text-primary',
            'adjust_in', 'adjust_out' => 'bg-warning-subtle text-warning',
            'expired' => 'bg-danger-subtle text-danger',
            'return' => 'bg-info-subtle text-info',
            default => 'bg-secondary-subtle text-secondary',
        };
    }

    public function typeBadgeColor(): string
    {
        return match ($this->type) {
            'in', 'purchase' => '#e8fadf',
            'out', 'sale' => '#e7e7ff',
            'adjust_in', 'adjust_out' => '#fff2d6',
            'expired' => '#ffe0db',
            'return' => '#d7f5fc',
            default => '#f1f0f2',
        };
    }

    public function typeTextColor(): string
    {
        return match ($this->type) {
            'in', 'purchase' => '#71dd37',
            'out', 'sale' => '#696cff',
            'adjust_in', 'adjust_out' => '#ffab00',
            'expired' => '#ff3e1d',
            'return' => '#03c3ec',
            default => '#8592a3',
        };
    }
}
