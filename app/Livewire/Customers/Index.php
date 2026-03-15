<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $showDeleteModal = false;
    public $customerIdBeingDeleted;

    protected $queryString = ['search'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->customerIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $customer = Customer::forStore(auth()->user()->store_id)->findOrFail($this->customerIdBeingDeleted);
        $customer->delete();
        $this->showDeleteModal = false;
        $this->dispatch('notify', ['message' => 'Client supprimé avec succès.']);
    }

    public function render()
    {
        $customers = Customer::forStore(auth()->user()->store_id)
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(15);

        return view('livewire.customers.index', compact('customers'))
            ->title(__('app.customers'));
    }
}
