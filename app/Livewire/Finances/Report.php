<?php

namespace App\Livewire\Finances;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\Purchase;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Report extends Component
{
    public string $startDate = '';
    public string $endDate = '';

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function render()
    {
        $storeId = auth()->user()->store_id;

        $totalSales = Sale::forStore($storeId)
            ->completed()
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->sum('final_amount');

        $totalExpenses = Expense::forStore($storeId)
            ->whereBetween('expense_date', [$this->startDate, $this->endDate])
            ->sum('amount');

        $totalPurchases = Purchase::forStore($storeId)
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->sum('amount_paid');

        $netCashFlow = $totalSales - $totalExpenses - $totalPurchases;

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.finances.report', compact('totalSales', 'totalExpenses', 'totalPurchases', 'netCashFlow'))
            ->with([
                'currency' => $currency,
                'currentStore' => $store,
            ])
            ->title(__('finances.report'));
    }
}
