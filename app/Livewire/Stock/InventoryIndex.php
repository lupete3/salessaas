<?php

namespace App\Livewire\Stock;

use App\Models\Inventory;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class InventoryIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $inventories = Inventory::where('store_id', auth()->user()->store_id)
            ->with(['user'])
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('livewire.stock.inventory-index', compact('inventories'))
            ->title(__('stock.inventory_list'));
    }
}
