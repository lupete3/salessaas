<?php

namespace App\Livewire\Finances;

use App\Models\Expense;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Expenses extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public bool $showModal = false;
    public ?int $editingId = null;
    public string $category = 'autre';
    public string $description = '';
    public float $amount = 0;
    public string $expense_date = '';
    public string $payment_method = 'cash';
    public string $receipt_number = '';

    protected function rules(): array
    {
        return [
            'category' => 'required|string',
            'description' => 'required|string|min:3',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string',
        ];
    }

    public function openModal(?int $id = null): void
    {
        $this->resetValidation();
        if ($id) {
            $expense = Expense::findOrFail($id);
            $this->editingId = $id;
            $this->category = $expense->category;
            $this->description = $expense->description;
            $this->amount = (float) $expense->amount;
            $this->expense_date = optional($expense->expense_date)->format('Y-m-d');
            $this->payment_method = $expense->payment_method;
            $this->receipt_number = $expense->receipt_number ?? '';
        } else {
            $this->editingId = null;
            $this->category = 'autre';
            $this->description = '';
            $this->amount = 0;
            $this->expense_date = now()->toDateString();
            $this->payment_method = 'cash';
            $this->receipt_number = '';
        }
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'store_id' => auth()->user()->store_id,
            'user_id' => auth()->id(),
            'category' => $this->category,
            'description' => $this->description,
            'amount' => $this->amount,
            'expense_date' => $this->expense_date,
            'payment_method' => $this->payment_method,
            'receipt_number' => $this->receipt_number ?: null,
        ];

        if ($this->editingId) {
            Expense::findOrFail($this->editingId)->update($data);
        } else {
            Expense::create($data);
        }

        $this->showModal = false;
        session()->flash('success', __('finances.expense_saved'));
    }

    public function delete(int $id): void
    {
        Expense::forStore(auth()->user()->store_id)->findOrFail($id)->delete();
        session()->flash('success', __('finances.expense_deleted'));
    }

    public function render()
    {
        $expenses = Expense::forStore(auth()->user()->store_id)
            ->latest('expense_date')
            ->paginate(15);

        $totalMonth = Expense::forStore(auth()->user()->store_id)
            ->whereMonth('expense_date', now()->month)
            ->sum('amount');

        $categories = ['loyer', 'salaires', 'electricite', 'eau', 'transport', 'fournitures', 'autre'];

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('livewire.finances.expenses', compact('expenses', 'categories'))
            ->with([
                'currency' => $currency,
                'currentStore' => $store,
            ])
            ->title(__('finances.expenses'));
    }
}
