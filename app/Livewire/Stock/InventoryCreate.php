<?php

namespace App\Livewire\Stock;

use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Inventaire Physique')]
class InventoryCreate extends Component
{
    public $date;
    public $notes;
    public $items = [];
    public $search = '';
    public $results = [];

    public function mount()
    {
        $this->date = date('Y-m-d');
    }

    public function updatedSearch()
    {
        if (strlen($this->search) < 2) {
            $this->results = [];
            return;
        }

        $this->results = Product::where('store_id', auth()->user()->store_id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->active()
            ->take(10)
            ->get()
            ->toArray();
    }

    public function addProduct($productId)
    {
        $product = Product::find($productId);

        // Check if already in items
        foreach ($this->items as $item) {
            if ($item['product_id'] == $productId) {
                $this->search = '';
                $this->results = [];
                return;
            }
        }

        $this->items[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'theoretical' => $product->stock_quantity,
            'physical' => $product->stock_quantity,
            'difference' => 0,
        ];

        $this->search = '';
        $this->results = [];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key)
    {
        // $key will be something like "0.physical"
        if (str_contains($key, '.physical')) {
            $parts = explode('.', $key);
            $index = $parts[0];
            $this->items[$index]['difference'] = (int) $this->items[$index]['physical'] - (int) $this->items[$index]['theoretical'];
        }
    }

    public function save()
    {
        $this->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.physical' => 'required|integer|min:0',
        ]);

        DB::transaction(function () {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $inventory = Inventory::create([
                'store_id' => $user->store_id,
                'user_id' => $user->id,
                'date' => $this->date,
                'notes' => $this->notes,
                'status' => 'completed',
            ]);

            foreach ($this->items as $item) {
                InventoryItem::create([
                    'inventory_id' => $inventory->id,
                    'product_id' => $item['product_id'],
                    'quantity_theoretical' => $item['theoretical'],
                    'quantity_physical' => $item['physical'],
                    'quantity_difference' => $item['difference'],
                ]);

                if ($item['difference'] != 0) {
                    $product = Product::find($item['product_id']);
                    $product->stock_quantity = $item['physical'];
                    $product->save();

                    StockMovement::create([
                        'store_id' => auth()->user()->store_id,
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'adjust',
                        'quantity' => abs($item['difference']),
                        'quantity_before' => $item['theoretical'],
                        'quantity_after' => $item['physical'],
                        'reason' => 'Inventaire du ' . $this->date,
                        'reference_type' => 'inventory',
                    ]);
                }
            }
        });

        session()->flash('success', __('stock.inventory_saved'));
        return redirect()->route('stock.inventory.index');
    }

    public function render()
    {
        return view('livewire.stock.inventory-create');
    }
}
