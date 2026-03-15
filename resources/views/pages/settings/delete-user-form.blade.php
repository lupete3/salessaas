<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div x-data="{ open: false }" class="mt-2">
    <div class="mb-4">
        <h6 class="fw-bold mb-1">{{ __('app.delete_account') }}</h6>
        <p class="text-muted small mb-0">
            {{ __('app.delete_account_description') }}
        </p>
    </div>

    <button type="button" class="btn btn-outline-danger" @click="open = true" data-test="delete-user-button">
        <i class="bi bi-trash3 me-2"></i> {{ __('app.delete_account_button') }}
    </button>

    <!-- Modal Bootstrap simulate (using Alpine for reactivity while preserving Bootstrap look) -->
    <div x-show="open" class="modal fade" :class="{ 'show d-block': open }" tabindex="-1"
        style="background: rgba(0,0,0,0.5);" @keydown.escape.window="open = false" x-cloak>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header border-bottom bg-body-tertiary">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-exclamation-octagon me-2"></i> {{ __('app.delete_account_confirm_title') }}
                    </h5>
                    <button type="button" class="btn-close" @click="open = false"></button>
                </div>

                <form method="POST" wire:submit="deleteUser">
                    <div class="modal-body p-4">
                        <p class="mb-4">
                            {{ __('app.delete_account_confirm_text') }}
                        </p>

                        <div class="mb-3">
                            <label
                                class="form-label fw-bold small text-uppercase text-muted">{{ __('app.current_password') }}</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"><i
                                        class="bi bi-shield-lock text-muted"></i></span>
                                <input type="password" wire:model="password"
                                    class="form-control border-start-0 py-2 @error('password') is-invalid @enderror"
                                    required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top bg-body-tertiary p-3">
                        <button type="button" class="btn btn-outline-secondary px-4" @click="open = false">
                            {{ __('app.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-danger px-4 shadow-sm"
                            data-test="confirm-delete-user-button">
                            {{ __('app.permanently_delete') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>