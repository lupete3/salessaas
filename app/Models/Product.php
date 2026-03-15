<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    protected $fillable = [
        'store_id',
        'supplier_id',
        'name',
        'generic_name',
        'category',
        'form',
        'dosage',
        'barcode',
        'unit',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'min_stock_alert',
        'is_active',
        'description',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class)->orderBy('expiry_date');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForStore(Builder $query, ?int $storeId): Builder
    {
        if (is_null($storeId)) {
            return $query;
        }
        return $query->where('store_id', $storeId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Stock faible : stock_quantity <= min_stock_alert */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_alert')
            ->where('stock_quantity', '>', 0);
    }

    /** Hors stock */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    /** Lots expirant dans X jours */
    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereHas('batches', function ($q) use ($days) {
            $q->where('expiry_date', '<=', now()->addDays($days))
                ->where('expiry_date', '>=', now())
                ->where('quantity_remaining', '>', 0);
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_alert && $this->stock_quantity > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function profitMargin(): float
    {
        if ($this->purchase_price == 0)
            return 0;
        return round((($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100, 2);
    }
}
