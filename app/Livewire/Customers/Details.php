<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\DebtPayment;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Fiche Client')]
class Details extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public Customer $customer;
    public string $tab = 'sales'; // 'sales' or 'payments'

    public function mount(Customer $customer)
    {
        if ($customer->store_id !== auth()->user()->store_id) {
            abort(403);
        }
        $this->customer = $customer;
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        $sales = collect();
        $payments = collect();

        if ($this->tab === 'sales') {
            $sales = Sale::where('customer_id', $this->customer->id)
                ->with('user')
                ->latest()
                ->paginate(10);
        } elseif ($this->tab === 'payments') {
            $payments = DebtPayment::where('customer_id', $this->customer->id)
                ->with('user')
                ->latest()
                ->paginate(10);
        }

        return view('livewire.customers.details', [
            'sales' => $sales,
            'payments' => $payments,
            'currency' => $currency
        ]);
    }
}
