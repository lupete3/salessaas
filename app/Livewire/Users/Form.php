<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?User $user = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public int $role_id = 0;
    public string $password = '';
    public string $locale = 'fr';
    public bool $is_active = true;

    protected function rules(): array
    {
        $emailRule = $this->user && $this->user->exists
            ? 'required|email|unique:users,email,' . $this->user->id
            : 'required|email|unique:users,email';

        return [
            'name' => 'required|string|max:100',
            'email' => $emailRule,
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'password' => $this->user && $this->user->exists ? 'nullable|min:8' : 'required|min:8',
            'locale' => 'required|in:fr,en,sw',
            'is_active' => 'boolean',
        ];
    }

    public function mount(?User $user = null): void
    {
        if ($user && $user->exists) {
            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->role_id = $user->role_id ?? 0;
            $this->locale = $user->locale ?? 'fr';
            $this->is_active = $user->is_active;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'store_id' => auth()->user()->store_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role_id' => $this->role_id,
            'locale' => $this->locale,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->user && $this->user->exists) {
            $this->user->update($data);
        } else {
            User::create($data);
        }

        session()->flash('success', __('users.saved_success'));
        $this->redirect(route('users.index'));
    }

    public function render()
    {
        $roles = Role::all();
        $locales = ['fr' => '🇫🇷 Français', 'en' => '🇬🇧 English', 'sw' => '🇹🇿 Kiswahili'];
        return view('livewire.users.form', compact('roles', 'locales'))
            ->title($this->user && $this->user->exists ? __('users.edit') : __('users.add'));
    }
}
