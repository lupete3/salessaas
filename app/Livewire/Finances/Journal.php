<?php

namespace App\Livewire\Finances;

use App\Models\Expense;
use App\Models\Sale;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Journal extends Component
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

        $sales = Sale::forStore($storeId)
            ->completed()
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->with('user')
            ->latest()
            ->get();

        $expenses = Expense::forStore($storeId)
            ->whereMonth('expense_date', $this->month)
            ->whereYear('expense_date', $this->year)
            ->with('user')
            ->latest('expense_date')
            ->get();

        $totalSales = $sales->sum('final_amount');
        $totalExpenses = $expenses->sum('amount');
        $netProfit = $totalSales - $totalExpenses;

        return view('livewire.finances.journal', compact('sales', 'expenses', 'totalSales', 'totalExpenses', 'netProfit'))
            ->title(__('finances.journal'));
    }
}
