<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?Supplier $supplier = null;

    public string $name = '';
    public ?string $phone = '';
    public ?string $email = '';
    public ?string $address = '';
    public ?string $contact_person = '';
    public ?string $notes = '';
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function mount(?Supplier $supplier = null): void
    {
        if ($supplier && $supplier->exists) {
            $this->supplier = $supplier;
            $this->fill($supplier->toArray());
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = array_merge($this->only(['name', 'phone', 'email', 'address', 'contact_person', 'notes', 'is_active']), [
            'store_id' => auth()->user()->store_id,
        ]);

        if ($this->supplier && $this->supplier->exists) {
            $this->supplier->update($data);
        } else {
            Supplier::create($data);
        }

        session()->flash('success', __('suppliers.saved_success'));
        $this->redirect(route('suppliers.index'));
    }

    public function render()
    {
        return view('livewire.suppliers.form')
            ->title($this->supplier && $this->supplier->exists ? __('suppliers.edit') : __('suppliers.add'));
    }
}
