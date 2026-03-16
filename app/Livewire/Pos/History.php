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
    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function cancelSale(int $saleId): void
    {
        $user = auth()->user();
        $sale = Sale::forStore($user->store_id)->findOrFail($saleId);

        if ($sale->status === 'cancelled') {
            return;
        }

        // Restriction for Sellers
        if ($user->isSeller()) {
            if ($sale->created_at->diffInHours(now()) > 24) {
                session()->flash('error', "Le délai d'annulation (24h) est dépassé.");
                return;
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($sale, $user) {
            $sale->update(['status' => 'cancelled']);

            foreach ($sale->items as $item) {
                $product = \App\Models\Product::find($item->product_id);
                if ($product) {
                    $before = $product->stock_quantity;
                    $product->increment('stock_quantity', $item->quantity);
                    $after = $before + $item->quantity;

                    \App\Models\StockMovement::create([
                        'store_id' => $user->store_id,
                        'product_id' => $item->product_id,
                        'user_id' => $user->id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                        'reason' => 'Annulation Vente',
                        'reference' => $sale->sale_number,
                        'reference_type' => 'sale',
                    ]);
                }
            }
        });

        session()->flash('success', "La vente a été annulée et le stock a été rétabli.");
    }

    public function render()
    {
        $storeId = auth()->user()->store_id;

        $query = Sale::forStore($storeId)
            ->with(['customer', 'items.product'])
            ->when($this->search, function ($q) {
                $q->where('sale_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            });

        $user = auth()->user();
        // Add filter for sellers to only see their own sales
        if ($user->isSeller()) {
            $query->where('user_id', $user->id);
        }

        $sales = $query->latest()
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
