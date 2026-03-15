<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?Customer $customer = null;
    public $name, $phone, $email, $address;

    public function mount(Customer $customer = null)
    {
        if ($customer && $customer->exists) {
            $this->customer = $customer;
            $this->name = $customer->name;
            $this->phone = $customer->phone;
            $this->email = $customer->email;
            $this->address = $customer->address;
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'store_id' => auth()->user()->store_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
        ];

        if ($this->customer) {
            $this->customer->update($data);
        } else {
            Customer::create($data);
        }

        session()->flash('success', __('customers.saved_success'));
        $this->redirect(route('customers.index'));
    }

    public function render()
    {
        return view('livewire.customers.form')
            ->title(__('customers.customer_single'));
    }
}
