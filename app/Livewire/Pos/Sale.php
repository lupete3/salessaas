<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale as SaleModel;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Sale extends Component
{
    public string $search = '';
    public array $searchResults = [];
    public array $cart = [];

    public $amount_discount = 0;
    public $amount_paid = 0;
    public $paymentMethod = 'cash';

    public string $customerSearch = '';
    public array $customerResults = [];
    public ?int $selectedCustomerId = null;
    public ?string $selectedCustomerName = null;

    public bool $showReceiptModal = false;
    public ?int $lastSaleId = null;

    public string $currency = '';
    public $currentStore;

    public function mount(): void
    {
        $this->currentStore = auth()->user()->store;
        $this->currency = $this->currentStore->currency ?: 'USD';
    }

    public function updatedSearch(): void
    {
        if (strlen($this->search) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Product::forStore(auth()->user()->store_id)
            ->active()
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('barcode', $this->search)
                    ->orWhere('generic_name', 'like', "%{$this->search}%");
            })
            ->where('stock_quantity', '>', 0)
            ->take(8)
            ->get(['id', 'name', 'selling_price', 'stock_quantity', 'form', 'dosage'])
            ->toArray();
    }

    public function updatedCustomerSearch(): void
    {
        if (strlen($this->customerSearch) < 2) {
            $this->customerResults = [];
            return;
        }

        $this->customerResults = Customer::forStore(auth()->user()->store_id)
            ->where('name', 'like', "%{$this->customerSearch}%")
            ->orWhere('phone', 'like', "%{$this->customerSearch}%")
            ->take(5)
            ->get(['id', 'name', 'phone'])
            ->toArray();
    }

    public function selectCustomer(int $id, string $name): void
    {
        $this->selectedCustomerId = $id;
        $this->selectedCustomerName = $name;
        $this->customerSearch = '';
        $this->customerResults = [];
    }

    public function removeCustomer(): void
    {
        $this->selectedCustomerId = null;
        $this->selectedCustomerName = null;
    }

    public function addToCart(int $productId): void
    {
        $product = Product::forStore(auth()->user()->store_id)
            ->findOrFail($productId);

        if ($product->stock_quantity <= 0) {
            session()->flash('error', __('pos.insufficient_stock', ['name' => $product->name]));
            return;
        }

        $key = 'prod_' . $productId;

        if (isset($this->cart[$key])) {
            // Cannot exceed stock
            if ($this->cart[$key]['qty'] < $product->stock_quantity) {
                $this->cart[$key]['qty']++;
                $this->cart[$key]['subtotal'] = $this->cart[$key]['qty'] * $this->cart[$key]['price'];
            }
        } else {
            $this->cart[$key] = [
                'id' => $product->id,
                'name' => $product->name,
                'form' => $product->form,
                'price' => (float) $product->selling_price,
                'qty' => 1,
                'max_qty' => $product->stock_quantity,
                'subtotal' => (float) $product->selling_price,
                'discount' => 0,
            ];
        }

        $this->search = '';
        $this->searchResults = [];
    }

    public function updateQty(string $key, int $qty): void
    {
        if (!isset($this->cart[$key]))
            return;

        if ($qty <= 0) {
            unset($this->cart[$key]);
            return;
        }

        $qty = min($qty, $this->cart[$key]['max_qty']);
        $this->cart[$key]['qty'] = $qty;
        $this->cart[$key]['subtotal'] = $qty * $this->cart[$key]['price'];
    }

    public function removeItem(string $key): void
    {
        unset($this->cart[$key]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->amount_discount = 0;
        $this->amount_paid = 0;
        $this->selectedCustomerId = null;
        $this->selectedCustomerName = null;
    }

    public function getTotal(): float
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    public function getFinalTotal(): float
    {
        return max(0, $this->getTotal() - ($this->amount_discount ?: 0));
    }

    public function getChange(): float
    {
        return max(0, ($this->amount_paid ?: 0) - $this->getFinalTotal());
    }

    public function finalize(): void
    {
        if (empty($this->cart)) {
            session()->flash('error', __('pos.cart_empty'));
            return;
        }

        $finalTotal = $this->getFinalTotal();
        $amountPaid = (float) ($this->amount_paid ?: 0);

        // Debt validation: if paid < total, a customer is mandatory
        if ($this->paymentMethod === 'credit' && !$this->selectedCustomerId) {
            session()->flash('error', __('pos.customer_required_for_credit'));
            return;
        }

        $storeId = auth()->user()->store_id;

        $sale = DB::transaction(function () use ($storeId) {
            $total = $this->getTotal();
            $finalTotal = $this->getFinalTotal();

            $sale = SaleModel::create([
                'store_id' => $storeId,
                'user_id' => auth()->id(),
                'customer_id' => $this->selectedCustomerId,
                'sale_number' => SaleModel::generateSaleNumber($storeId),
                'total_amount' => $total,
                'discount' => $this->amount_discount ?: 0,
                'final_amount' => $finalTotal,
                'amount_paid' => $this->amount_paid ?: 0,
                'change_given' => $this->getChange(),
                'payment_method' => $this->paymentMethod,
                'status' => 'completed',
            ]);

            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);

                $product = Product::find($item['id']);
                $before = $product->stock_quantity;
                $product->decrement('stock_quantity', $item['qty']);
                $after = $before - $item['qty'];

                StockMovement::create([
                    'store_id' => $storeId,
                    'product_id' => $item['id'],
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'quantity' => $item['qty'],
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'reason' => 'Vente',
                    'reference' => $sale->sale_number,
                    'reference_type' => 'sale',
                ]);
            }

            return $sale;
        });

        $this->lastSaleId = $sale->id;
        $this->showReceiptModal = true;
        $this->clearCart();
    }

    public function render()
    {
        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.pos.sale', [
            'currency' => $currency,
            'currentStore' => $store,
        ])->title(__('pos.title'));
    }
}
