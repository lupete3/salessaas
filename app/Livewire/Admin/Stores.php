<?php

namespace App\Livewire\Admin;

use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('stores.title')]
class Stores extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showingModal = false;
    public bool $isEditing = false;

    // Form fields
    public ?int $storeId = null;
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public string $subscription_status = 'active';
    public $subscription_ends_at;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showingModal = true;
    }

    public function edit(Store $store): void
    {
        $this->resetForm();
        $this->storeId = $store->id;
        $this->name = $store->name;
        $this->phone = $store->phone ?? '';
        $this->email = $store->email ?? '';
        $this->address = $store->address ?? '';
        $this->subscription_status = $store->subscription_status;
        $this->subscription_ends_at = $store->subscription_ends_at?->format('Y-m-d');

        $this->isEditing = true;
        $this->showingModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'subscription_status' => 'required|in:active,inactive,expired,trial',
            'subscription_ends_at' => 'nullable|date',
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'subscription_status' => $this->subscription_status,
            'subscription_ends_at' => $this->subscription_ends_at,
        ];

        if ($this->isEditing && $this->storeId) {
            Store::find($this->storeId)->update($data);
            session()->flash('success', __('stores.updated_success'));
        } else {
            Store::create($data);
            session()->flash('success', __('stores.saved_success'));
        }

        $this->showingModal = false;
        $this->resetForm();
    }

    public function delete(Store $store): void
    {
        // Add safety check: don't delete if it has users? or just confirmed delete
        $store->delete();
        session()->flash('success', __('stores.deleted_success'));
    }

    private function resetForm(): void
    {
        $this->storeId = null;
        $this->name = '';
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->subscription_status = 'active';
        $this->subscription_ends_at = null;
    }

    public function render()
    {
        $stores = Store::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.stores', [
            'stores' => $stores,
        ]);
    }
}
