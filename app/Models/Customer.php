<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
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

    public function debtPayments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
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
