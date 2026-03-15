<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full pb-5">
    <x-pages::settings.layout :heading="__('app.security_title')" :subheading="__('app.security_subtitle')">

        <div class="card shadow-sm border-0 overflow-hidden mb-4">
            <div class="card-header bg-body-tertiary border-bottom d-flex align-items-center gap-3 py-3">
                <div class="p-2 rounded shadow-sm" style="background:#e7f3ed; color:#198754">
                    <i class="bi bi-shield-lock-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">{{ __('app.change_password') }}</h6>
                    <small class="text-muted">{{ __('app.password_update_advised') }}</small>
                </div>
            </div>

            <div class="card-body p-4">
                <form method="POST" wire:submit="updatePassword">
                    <div class="mb-4">
                        <label
                            class="form-label fw-bold small text-uppercase text-muted">{{ __('app.current_password') }}</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text border-end-0"><i
                                    class="bi bi-lock-fill text-muted"></i></span>
                            <input type="password" wire:model="current_password"
                                class="form-control border-start-0 py-2" required autocomplete="current-password">
                        </div>
                        @error('current_password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label
                            class="form-label fw-bold small text-uppercase text-muted">{{ __('app.new_password') }}</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text border-end-0"><i class="bi bi-key-fill text-muted"></i></span>
                            <input type="password" wire:model="password" class="form-control border-start-0 py-2"
                                required autocomplete="new-password">
                        </div>
                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label
                            class="form-label fw-bold small text-uppercase text-muted">{{ __('app.confirm_new_password') }}</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text border-end-0"><i
                                    class="bi bi-check-circle-fill text-muted"></i></span>
                            <input type="password" wire:model="password_confirmation"
                                class="form-control border-start-0 py-2" required autocomplete="new-password">
                        </div>
                        @error('password_confirmation') <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center gap-3 pt-2">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-shield-check me-2"></i> {{ __('app.update') }}
                        </button>

                        <x-action-message class="text-success small fw-bold" on="password-updated">
                            <i class="bi bi-shield-check me-1"></i> {{ __('app.password_updated') }}
                        </x-action-message>
                    </div>
                </form>
            </div>
        </div>
    </x-pages::settings.layout>
</section>