<?php

namespace App\Livewire\Pos;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('app.sales_history')]
class History extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $storeId = auth()->user()->store_id;

        $sales = Sale::forStore($storeId)
            ->with(['customer', 'user'])
            ->when($this->search, function ($query) {
                $query->where('sale_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->paginate(15);

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.pos.history', compact('sales'))
            ->with([
                'currency' => $currency,
                'currentStore' => $store,
            ]);
    }
}
