<?php

namespace App\Livewire\Stock;

use App\Models\Product;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Fiche Produit')]
class ProductDetails extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public Product $product;
    public string $type = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(Product $product)
    {
        // Add security check if necessary
        if ($product->store_id !== auth()->user()->store_id) {
            abort(403);
        }
        $this->product = $product;
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $movements = StockMovement::where('product_id', $this->product->id)
            ->with('user')
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(15);

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.stock.product-details', compact('movements'))
            ->with([
                'currency' => $currency,
            ]);
    }
}
