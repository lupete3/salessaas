<?php

namespace App\Livewire\Admin;

use App\Models\Store;
use App\Models\User;
use App\Models\Sale;
use App\Models\Alert;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('app.master_dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        // Global Stats
        $totalStores = Store::count();
        $activeStores = Store::where('subscription_status', 'active')->count();
        $expiredStores = Store::where('subscription_status', 'expired')->count();
        $trialStores = Store::where('subscription_status', 'trial')->count();

        $totalUsers = User::count();
        $totalSalesCount = Sale::completed()->count();
        $totalSalesAmount = Sale::completed()->sum('final_amount');

        // Recent Tenants
        $recentStores = Store::latest()->take(5)->get();

        // Subscription Expirations (Next 30 days)
        $expiringSoon = Store::where('subscription_ends_at', '<=', now()->addDays(30))
            ->where('subscription_ends_at', '>=', now())
            ->get();

        // Platform Alerts
        $unreadAlerts = Alert::unread()->latest()->take(5)->get();

        return view('livewire.admin.dashboard', [
            'totalStores' => $totalStores,
            'activeStores' => $activeStores,
            'expiredStores' => $expiredStores,
            'trialStores' => $trialStores,
            'totalUsers' => $totalUsers,
            'totalSalesCount' => $totalSalesCount,
            'totalSalesAmount' => $totalSalesAmount,
            'recentStores' => $recentStores,
            'expiringSoon' => $expiringSoon,
            'unreadAlerts' => $unreadAlerts,
        ]);
    }
}
