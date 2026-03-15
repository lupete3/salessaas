<?php

namespace App\Livewire\Stock;

use App\Models\Product;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Ajustement de Stock')]
class Adjust extends Component
{
    public string $search = '';
    public array $results = [];
    public ?int $productId = null;
    public ?string $productName = null;
    public int $currentQty = 0;
    public int $newQty = 0;
    public string $reason = '';

    public function selectProduct(int $id): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $product = Product::forStore($user->store_id)->findOrFail($id);
        $this->productId = $product->id;
        $this->productName = $product->name;
        $this->currentQty = $product->stock_quantity;
        $this->newQty = $product->stock_quantity;
        $this->search = '';
        $this->results = [];
    }

    public function updatedSearch(): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $this->results = strlen($this->search) >= 2
            ? Product::forStore($user->store_id)
                ->active()
                ->where('name', 'like', "%{$this->search}%")
                ->take(8)
                ->get(['id', 'name', 'stock_quantity'])
                ->toArray()
            : [];
    }

    public function save(): void
    {
        $this->validate([
            'productId' => 'required|exists:products,id',
            'newQty' => 'required|integer|min:0',
            'reason' => 'required|string|min:3',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $product = Product::forStore($user->store_id)->findOrFail($this->productId);
        $before = $product->stock_quantity;
        $diff = $this->newQty - $before;

        $product->update(['stock_quantity' => $this->newQty]);

        StockMovement::create([
            'store_id' => auth()->user()->store_id,
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => 'adjust',
            'quantity' => abs($diff),
            'quantity_before' => $before,
            'quantity_after' => $this->newQty,
            'reason' => $this->reason,
            'reference_type' => 'manual',
        ]);

        session()->flash('success', __('stock.adjusted_success'));
        $this->reset(['productId', 'productName', 'currentQty', 'newQty', 'reason', 'search', 'results']);
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $store = $user->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.stock.adjust')
            ->with([
                'currency' => $currency,
                'currentStore' => $store,
            ]);
    }
}
