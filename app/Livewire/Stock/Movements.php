<?php

namespace App\Livewire\Stock;

use App\Models\Product;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Movements extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $type = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public ?int $productId = null;

    protected $queryString = ['search', 'type', 'productId'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingProductId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $movements = StockMovement::forStore(auth()->user()->store_id)
            ->with(['product', 'user'])
            ->when(
                $this->search,
                fn($q) =>
                $q->whereHas('product', fn($m) => $m->where('name', 'like', "%{$this->search}%"))
            )
            ->when($this->productId, fn($q) => $q->where('product_id', $this->productId))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(15);

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.stock.movements', compact('movements'))
            ->with([
                'currency' => $currency,
                'currentStore' => $store,
            ])
            ->title(__('stock.history'));
    }
}
