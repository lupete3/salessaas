<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;
use App\Models\Purchase;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('layouts.app')]
class Monthly extends Component
{
    public string $month = '';
    public string $year = '';

    public function mount(): void
    {
        $this->month = now()->format('m');
        $this->year = now()->format('Y');
    }

    public function render()
    {
        $storeId = auth()->user()->store_id;

        $totalSales = Sale::forStore($storeId)
            ->completed()
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->sum('final_amount');

        $salesCount = Sale::forStore($storeId)
            ->completed()
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->count();

        $totalExpenses = Expense::forStore($storeId)
            ->whereMonth('expense_date', $this->month)
            ->whereYear('expense_date', $this->year)
            ->sum('amount');

        $totalPurchases = Purchase::forStore($storeId)
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->sum('total_amount');

        $netProfit = $totalSales - $totalExpenses;

        $topProducts = SaleItem::whereHas(
            'sale',
            fn($q) =>
            $q->forStore($storeId)->completed()
                ->whereMonth('created_at', $this->month)
                ->whereYear('created_at', $this->year)
        )
            ->with('product')
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get();

        // Daily breakdown for the chart
        $dailySales = Sale::forStore($storeId)
            ->completed()
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->selectRaw('DATE(created_at) as day, SUM(final_amount) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('day')
            ->get();

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.reports.monthly', compact(
            'totalSales',
            'salesCount',
            'totalExpenses',
            'totalPurchases',
            'netProfit',
            'topProducts',
            'dailySales'
        ))
            ->with([
                'currency' => $currency,
                'currentStore' => $store,
            ])
            ->title(__('reports.monthly_report'));
    }
}
