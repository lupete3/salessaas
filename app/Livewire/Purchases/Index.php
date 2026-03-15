<?php

namespace App\Livewire\Purchases;

use App\Models\Purchase;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $status = '';

    public function render()
    {
        $purchases = Purchase::forStore(auth()->user()->store_id)
            ->with(['supplier', 'user'])
            ->when(
                $this->search,
                fn($q) =>
                $q->whereHas('supplier', fn($s) => $s->where('name', 'like', "%{$this->search}%"))
                    ->orWhere('purchase_number', 'like', "%{$this->search}%")
            )
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        $totalDebt = Purchase::forStore(auth()->user()->store_id)
            ->whereIn('status', ['pending', 'partial'])
            ->sum('balance_due');

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.purchases.index', compact('purchases', 'totalDebt'))
            ->with([
                'currency' => $currency,
                'currentStore' => $store,
            ])
            ->title(__('purchases.title'));
    }
}
