<?php

namespace App\Livewire;

use App\Models\Alert;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function mount()
    {
        if (auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }
    }

    public function render()
    {
        $storeId = auth()->user()->store_id;

        $user = auth()->user();
        $isSeller = $user->isSeller();

        // Ventes du jour
        $todaySalesQuery = Sale::forStore($storeId)->completed()->today();
        if ($isSeller) {
            $todaySalesQuery->where('user_id', $user->id);
        }
        $todaySales = $todaySalesQuery->sum('final_amount');

        $todaySalesCountQuery = Sale::forStore($storeId)->completed()->today();
        if ($isSeller) {
            $todaySalesCountQuery->where('user_id', $user->id);
        }
        $todaySalesCount = $todaySalesCountQuery->count();

        // Stock critique
        $lowStockCount = Product::forStore($storeId)
            ->active()
            ->lowStock()
            ->count();

        $outOfStockCount = Product::forStore($storeId)
            ->active()
            ->outOfStock()
            ->count();

        // Expiration proche (30 jours)
        $expiringSoonCount = Product::forStore($storeId)
            ->active()
            ->expiringSoon(30)
            ->count();

        // Dépenses du mois
        $monthExpensesQuery = Expense::forStore($storeId)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year);
        if ($isSeller) {
            $monthExpensesQuery->where('user_id', $user->id);
        }
        $monthExpenses = $monthExpensesQuery->sum('amount');

        // CA du mois
        $monthSalesQuery = Sale::forStore($storeId)
            ->completed()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        if ($isSeller) {
            $monthSalesQuery->where('user_id', $user->id);
        }
        $monthSales = $monthSalesQuery->sum('final_amount');

        // Profit estimé du mois (ventes - achats - dépenses)
        $estimatedProfit = $monthSales - $monthExpenses;

        // Graphique : ventes 7 derniers jours
        $salesChart = collect(range(6, 0))->map(function ($daysAgo) use ($storeId, $isSeller, $user) {
            $date = now()->subDays($daysAgo);
            $query = Sale::forStore($storeId)
                ->completed()
                ->whereDate('created_at', $date);
            if ($isSeller) {
                $query->where('user_id', $user->id);
            }
            return [
                'label' => $date->translatedFormat('d/m'),
                'value' => $query->sum('final_amount'),
            ];
        });

        // Alertes non lues
        $unreadAlerts = Alert::forStore($storeId)->unread()->latest()->take(5)->get();

        // Produits en stock faible (top 5)
        $lowStockProducts = Product::forStore($storeId)
            ->active()
            ->lowStock()
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.dashboard', compact(
            'todaySales',
            'todaySalesCount',
            'lowStockCount',
            'outOfStockCount',
            'expiringSoonCount',
            'monthSales',
            'monthExpenses',
            'estimatedProfit',
            'salesChart',
            'unreadAlerts',
            'lowStockProducts'
        ))
            ->with([
                'currency' => $currency,
                'currentStore' => $store,
            ])
            ->title(__('app.dashboard'));
    }
}
