<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('layouts.app')]
class Daily extends Component
{
    public string $date = '';

    public function mount(): void
    {
        $this->date = today()->toDateString();
    }

    public function exportPdf(): void
    {
        $data = $this->getReportData();
        $pdf = Pdf::loadView('pdf.daily-report', $data)
            ->setPaper('a4', 'portrait');

        $this->dispatch('download-pdf', base64_encode($pdf->output()), __('reports.daily_report_file') . '-' . $this->date . '.pdf');
    }

    private function getReportData(): array
    {
        $storeId = auth()->user()->store_id;

        $sales = Sale::forStore($storeId)
            ->completed()
            ->whereDate('created_at', $this->date)
            ->with('items.product', 'user')
            ->get();

        $expenses = Expense::forStore($storeId)
            ->whereDate('expense_date', $this->date)
            ->get();

        $topProducts = SaleItem::whereHas(
            'sale',
            fn($q) =>
            $q->forStore($storeId)->completed()->whereDate('created_at', $this->date)
        )
            ->with('product')
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        $date = $this->date;
        return compact('sales', 'expenses', 'topProducts', 'date', 'currency') + ['currentStore' => $store];
    }

    public function render()
    {
        $data = $this->getReportData();
        $sales = $data['sales'];
        $expenses = $data['expenses'];
        $topProducts = $data['topProducts'];

        $totalSales = $sales->sum('final_amount');
        $totalExpenses = $expenses->sum('amount');
        $netProfit = $totalSales - $totalExpenses;

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.reports.daily', compact('sales', 'expenses', 'topProducts', 'totalSales', 'totalExpenses', 'netProfit'))
            ->with([
                'currency' => $currency,
                'currentStore' => $store,
            ])
            ->title(__('reports.daily_report'));
    }
}
