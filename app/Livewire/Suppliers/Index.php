<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
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

    public function render()
    {
        $suppliers = Supplier::forStore(auth()->user()->store_id)
            ->when(
                $this->search,
                fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
            )
            ->withCount('purchases')
            ->withSum('purchases', 'balance_due')
            ->orderBy('name')
            ->paginate(15);

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.suppliers.index', compact('suppliers'))
            ->with([
                'currency' => $currency,
                'currentStore' => $store,
            ])
            ->title(__('suppliers.title'));
    }
}
