<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public function toggle(int $id): void
    {
        $user = User::where('store_id', auth()->user()->store_id)->findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
    }

    public function render()
    {
        $users = User::where('store_id', auth()->user()->store_id)
            ->with('role')
            ->when(
                $this->search,
                fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->paginate(15);

        return view('livewire.users.index', compact('users'))
            ->title(__('users.title'));
    }
}
