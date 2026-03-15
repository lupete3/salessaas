<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?Product $product = null;

    // Product fields
    public string $name = '';
    public ?string $generic_name = '';
    public ?string $category = '';
    public ?string $form = '';
    public ?string $dosage = '';
    public ?string $barcode = '';
    public string $unit = 'pièce';
    public float $purchase_price = 0;
    public float $selling_price = 0;
    public int $min_stock_alert = 10;
    public ?int $supplier_id = null;
    public bool $is_active = true;
    public ?string $description = '';

    // Batch fields
    public ?string $batch_number = '';
    public ?string $expiry_date = '';
    public int $batch_quantity = 0;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'generic_name' => 'nullable|string|max:200',
            'category' => 'nullable|string|max:100',
            'form' => 'nullable|string|max:100',
            'dosage' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock_alert' => 'required|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'batch_quantity' => 'nullable|integer|min:0',
        ];
    }

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->product = $product;
            $this->fill($product->toArray());
            $this->supplier_id = $product->supplier_id;
        }
    }

    public function save(): void
    {
        $this->validate();

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $storeId = $user->store_id;

        $data = [
            'store_id' => $storeId,
            'supplier_id' => $this->supplier_id ?: null,
            'name' => $this->name,
            'generic_name' => $this->generic_name,
            'category' => $this->category,
            'form' => $this->form,
            'dosage' => $this->dosage,
            'barcode' => $this->barcode ?: null,
            'unit' => $this->unit,
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'min_stock_alert' => $this->min_stock_alert,
            'is_active' => $this->is_active,
            'description' => $this->description,
        ];

        if ($this->product && $this->product->exists) {
            $this->product->update($data);
            $product = $this->product;
        } else {
            $product = Product::create($data);
        }

        // Create a batch if batch info was provided
        if ($this->batch_number && $this->batch_quantity > 0) {
            ProductBatch::create([
                'store_id' => $storeId,
                'product_id' => $product->id,
                'batch_number' => $this->batch_number,
                'quantity' => $this->batch_quantity,
                'quantity_remaining' => $this->batch_quantity,
                'expiry_date' => $this->expiry_date ?: now()->addYears(2)->toDateString(),
                'purchase_price' => $this->purchase_price,
                'selling_price' => $this->selling_price,
            ]);

            // Update stock quantity
            $product->increment('stock_quantity', $this->batch_quantity);

            \App\Models\StockMovement::create([
                'store_id' => $storeId,
                'product_id' => $product->id,
                'user_id' => $user->id,
                'type' => 'in',
                'quantity' => $this->batch_quantity,
                'quantity_before' => 0,
                'quantity_after' => $this->batch_quantity,
                'reason' => 'Stock initial',
                'reference' => $this->batch_number ?: 'INITIAL',
                'reference_type' => 'initial',
            ]);
        }

        session()->flash('success', __('products.saved_success'));
        $this->redirect(route('products.index'));
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $suppliers = Supplier::forStore($user->store_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = array_keys(__('products.categories'));
        $forms = array_keys(__('products.forms'));
        $locales = ['fr' => '🇫🇷 Français', 'en' => '🇬🇧 English', 'sw' => '🇹🇿 Kiswahili'];

        $title = $this->product && $this->product->exists ? __('products.edit') : __('products.add_new');

        return view('livewire.products.form', compact('suppliers', 'categories', 'forms', 'locales'))
            ->title($title);
    }
}
