<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Catalogue des Produits')]
class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $filterCategory = '';
    public string $filterStatus = '';  // low_stock, out_of_stock, expiring
    public string $sortBy = 'name';
    public string $sortDir = 'asc';

    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    protected $queryString = ['search', 'filterCategory', 'filterStatus'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }
    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        $this->sortDir = ($this->sortBy === $column && $this->sortDir === 'asc') ? 'desc' : 'asc';
        $this->sortBy = $column;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $product = Product::forStore($user->store_id)
            ->findOrFail($this->deletingId);

        $product->update(['is_active' => false]);
        $this->showDeleteModal = false;
        $this->deletingId = null;

        session()->flash('success', __('products.deleted_success'));
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $query = Product::forStore($user->store_id)
            ->with('supplier')
            ->when(
                $this->search,
                fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('generic_name', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%")
            )
            ->when(
                $this->filterCategory,
                fn($q) =>
                $q->where('category', $this->filterCategory)
            )
            ->when($this->filterStatus === 'low_stock', fn($q) => $q->lowStock())
            ->when($this->filterStatus === 'out_of_stock', fn($q) => $q->outOfStock())
            ->when($this->filterStatus === 'expiring', fn($q) => $q->expiringSoon(30))
            ->orderBy($this->sortBy, $this->sortDir);

        $products = $query->paginate(15);
        $categories = Product::forStore($user->store_id)
            ->distinct()->pluck('category')->filter()->sort()->values();

        $stats = [
            'total' => Product::forStore($user->store_id)->count(),
            'low_stock' => Product::forStore($user->store_id)->lowStock()->count(),
            'out_of_stock' => Product::forStore($user->store_id)->outOfStock()->count(),
            'expiring' => Product::forStore($user->store_id)->expiringSoon(30)->count(),
        ];

        $store = $user->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.products.index', compact('products', 'categories', 'stats'))
            ->with([
                'currency' => $currency,
                'currentStore' => $store,
            ]);
    }
}
