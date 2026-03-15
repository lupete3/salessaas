<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'store_id',
        'uuid',
        'name',
        'phone',
        'email',
        'address',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function debtPayments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function scopeForStore($query, ?int $storeId)
    {
        if (is_null($storeId)) {
            return $query;
        }
        return $query->where('store_id', $storeId);
    }

    /** Calcule la dette totale actuelle du client */
    public function getTotalDebtAttribute(): float
    {
        $salesDebt = $this->sales()
            ->where('status', 'completed')
            ->get()
            ->sum(function ($sale) {
                return max(0, $sale->final_amount - $sale->amount_paid);
            });

        $debtPayments = $this->debtPayments()->sum('amount');

        return max(0, $salesDebt - $debtPayments);
    }
}
