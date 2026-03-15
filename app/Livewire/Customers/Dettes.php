<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('customers.debt_followup')]
class Dettes extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $customerIdFilter = '';
    public $showPaymentModal = false;
    public ?Sale $selectedSale = null;
    public $paymentAmount = 0;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openPaymentModal(Sale $sale)
    {
        $this->selectedSale = $sale;
        $this->paymentAmount = $sale->final_amount - $sale->amount_paid;
        $this->showPaymentModal = true;
    }

    public function recordPayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
        ]);

        $this->selectedSale->update([
            'amount_paid' => $this->selectedSale->amount_paid + $this->paymentAmount
        ]);

        $this->showPaymentModal = false;
        $this->dispatch('notify', ['message' => __('customers.payment_recorded')]);
    }

    public function render()
    {
        $storeId = auth()->user()->store_id;

        $query = Sale::forStore($storeId)
            ->whereNotNull('customer_id')
            ->whereColumn('amount_paid', '<', 'final_amount')
            ->with('customer')
            ->latest();

        if ($this->search) {
            $query->whereHas('customer', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->customerIdFilter) {
            $query->where('customer_id', $this->customerIdFilter);
        }

        $dettes = $query->paginate(15);
        $customers = Customer::forStore($storeId)->orderBy('name')->get();

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.customers.dettes', [
            'dettes' => $dettes,
            'customers' => $customers,
            'currency' => $currency,
            'currentStore' => $store,
        ])->title(__('customers.debt_followup'));
    }
}
