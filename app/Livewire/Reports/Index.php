<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Expense;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $reportType = 'stock';
    public string $startDate = '';
    public string $endDate = '';
    public ?int $supplierId = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function exportStockReport(): void
    {
        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        $products = Product::forStore($store->id)
            ->with(['supplier', 'batches'])
            ->orderBy('name')
            ->get();

        $totalPurchaseValue = $products->sum(fn($m) => $m->stock_quantity * $m->purchase_price);
        $totalSellingValue = $products->sum(fn($m) => $m->stock_quantity * $m->selling_price);

        $pdf = Pdf::loadView('pdf.stock-report', [
            'products' => $products,
            'totalPurchaseValue' => $totalPurchaseValue,
            'totalSellingValue' => $totalSellingValue,
            'date' => now()->format('d/m/Y H:i'),
            'currentStore' => $store,
            'currency' => $currency,
        ])->setPaper('a4', 'landscape');

        $this->dispatch('download-pdf', base64_encode($pdf->output()), __('reports.stock_report_file') . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportSalesReport(): void
    {
        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        $sales = Sale::forStore($store->id)
            ->completed()
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->with(['items.product', 'user'])
            ->latest()
            ->get();

        $pdf = Pdf::loadView('pdf.sales-report', [
            'sales' => $sales,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalAmount' => $sales->sum('final_amount'),
            'totalProfit' => $sales->sum(fn($s) => $s->final_amount - $s->items->sum(fn($i) => $i->quantity * $i->product->purchase_price)),
            'date' => now()->format('d/m/Y H:i'),
            'currentStore' => $store,
            'currency' => $currency,
        ])->setPaper('a4', 'landscape');

        $this->dispatch('download-pdf', base64_encode($pdf->output()), __('reports.sales_report_file') . '-' . $this->startDate . '-' . __('reports.to') . '-' . $this->endDate . '.pdf');
    }

    public function exportPurchasesReport(): void
    {
        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        $query = Purchase::forStore($store->id)
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->with(['supplier', 'user', 'items.product']);

        if ($this->supplierId) {
            $query->where('supplier_id', $this->supplierId);
        }

        $purchases = $query->latest()->get();

        $pdf = Pdf::loadView('pdf.purchases-report', [
            'purchases' => $purchases,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalAmount' => $purchases->sum('total_amount'),
            'date' => now()->format('d/m/Y H:i'),
            'currentStore' => $store,
            'currency' => $currency,
        ])->setPaper('a4', 'landscape');

        $this->dispatch('download-pdf', base64_encode($pdf->output()), __('reports.purchases_report_file') . '-' . $this->startDate . '-' . __('reports.to') . '-' . $this->endDate . '.pdf');
    }

    public function exportFinanceReport(): void
    {
        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        $salesByMethod = Sale::forStore($store->id)
            ->completed()
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->select('payment_method', DB::raw('SUM(final_amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->pluck('total', 'payment_method');

        $totalSales = $salesByMethod->sum();

        $expensesByCategory = Expense::forStore($store->id)
            ->whereBetween('expense_date', [$this->startDate, $this->endDate])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category');

        $totalExpenses = $expensesByCategory->sum();

        $totalPurchases = Purchase::forStore($store->id)
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->sum('amount_paid');

        $netCashFlow = $totalSales - $totalExpenses - $totalPurchases;

        $pdf = Pdf::loadView('pdf.finance-report', [
            'totalSales' => $totalSales,
            'salesByMethod' => $salesByMethod,
            'totalExpenses' => $totalExpenses,
            'expensesByCategory' => $expensesByCategory,
            'totalPurchases' => $totalPurchases,
            'netCashFlow' => $netCashFlow,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'date' => now()->format('d/m/Y H:i'),
            'currentStore' => $store,
            'currency' => $currency,
        ])->setPaper('a4', 'portrait');

        $this->dispatch('download-pdf', base64_encode($pdf->output()), __('reports.finance_report_file') . '-' . $this->startDate . '-' . __('reports.to') . '-' . $this->endDate . '.pdf');
    }

    public function exportCustomersReport(): void
    {
        $store = auth()->user()->store;
        $customers = \App\Models\Customer::where('store_id', $store->id)->orderBy('name')->get();

        $pdf = Pdf::loadView('pdf.customers-report', [
            'customers' => $customers,
            'date' => now()->format('d/m/Y H:i'),
            'currentStore' => $store,
        ])->setPaper('a4', 'portrait');

        $this->dispatch('download-pdf', base64_encode($pdf->output()), __('reports.customers_report_file') . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportSuppliersReport(): void
    {
        $store = auth()->user()->store;
        $suppliers = \App\Models\Supplier::where('store_id', $store->id)->orderBy('name')->get();

        $pdf = Pdf::loadView('pdf.suppliers-report', [
            'suppliers' => $suppliers,
            'date' => now()->format('d/m/Y H:i'),
            'currentStore' => $store,
        ])->setPaper('a4', 'portrait');

        $this->dispatch('download-pdf', base64_encode($pdf->output()), __('reports.suppliers_report_file') . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportCustomerDebtsReport(): void
    {
        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        // total_debt is an accessor (not a real column), so we filter/sort in PHP
        $customersWithDebts = \App\Models\Customer::where('store_id', $store->id)
            ->with(['sales', 'debtPayments'])
            ->get()
            ->filter(fn($c) => $c->total_debt > 0)
            ->sortByDesc('total_debt')
            ->values();

        $pdf = Pdf::loadView('pdf.customer-debts-report', [
            'customers' => $customersWithDebts,
            'totalDebt' => $customersWithDebts->sum('total_debt'),
            'date' => now()->format('d/m/Y H:i'),
            'currentStore' => $store,
            'currency' => $currency,
        ])->setPaper('a4', 'portrait');

        $this->dispatch('download-pdf', base64_encode($pdf->output()), __('reports.customer_debts_report_file') . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportPaymentsReport(): void
    {
        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        $payments = \App\Models\DebtPayment::where('store_id', $store->id)
            ->whereBetween('paid_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->with(['customer', 'user'])
            ->latest()
            ->get();

        $pdf = Pdf::loadView('pdf.payments-report', [
            'payments' => $payments,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalAmount' => $payments->sum('amount'),
            'date' => now()->format('d/m/Y H:i'),
            'currentStore' => $store,
            'currency' => $currency,
        ])->setPaper('a4', 'portrait');

        $this->dispatch('download-pdf', base64_encode($pdf->output()), __('reports.payments_report_file') . '-' . $this->startDate . '-' . __('reports.to') . '-' . $this->endDate . '.pdf');
    }

    public function exportInventoriesReport(): void
    {
        $store = auth()->user()->store;

        $inventories = \App\Models\Inventory::where('store_id', $store->id)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->with(['user', 'items.product'])
            ->latest('date')
            ->get();

        $pdf = Pdf::loadView('pdf.inventories-report', [
            'inventories' => $inventories,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'date' => now()->format('d/m/Y H:i'),
            'currentStore' => $store,
        ])->setPaper('a4', 'landscape');

        $this->dispatch('download-pdf', base64_encode($pdf->output()), __('reports.inventories_report_file') . '-' . $this->startDate . '-' . __('reports.to') . '-' . $this->endDate . '.pdf');
    }

    public function exportStockMovementsReport(): void
    {
        $store = auth()->user()->store;

        $movements = \App\Models\StockMovement::where('store_id', $store->id)
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->with(['product', 'user'])
            ->latest()
            ->get();

        $pdf = Pdf::loadView('pdf.stock-movements-report', [
            'movements' => $movements,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'date' => now()->format('d/m/Y H:i'),
            'currentStore' => $store,
        ])->setPaper('a4', 'landscape');

        $this->dispatch('download-pdf', base64_encode($pdf->output()), __('reports.stock_movements_report_file') . '-' . $this->startDate . '-' . __('reports.to') . '-' . $this->endDate . '.pdf');
    }

    public function render()
    {
        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';
        $suppliers = \App\Models\Supplier::where('store_id', $store->id)->get();

        return view('livewire.reports.index', compact('suppliers'))
            ->with([
                'currency' => $currency,
                'currentStore' => $store,
            ])
            ->title(__('reports.title'));
    }
}
