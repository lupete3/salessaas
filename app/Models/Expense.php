<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'store_id',
        'user_id',
        'category',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'receipt_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
        return $query->whereDate('expense_date', today());
    }

    public function categoryLabel(): string
    {
        return __('expenses.categories.' . $this->category, [], $this->store->locale ?? 'fr');
    }
}
