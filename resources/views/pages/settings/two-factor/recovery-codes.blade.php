<?php

use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public array $recoveryCodes = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadRecoveryCodes();
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(auth()->user());

        $this->loadRecoveryCodes();
    }

    /**
     * Load the recovery codes for the user.
     */
    private function loadRecoveryCodes(): void
    {
        $user = auth()->user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (Exception) {
                $this->addError('recoveryCodes', 'Failed to load recovery codes');

                $this->recoveryCodes = [];
            }
        }
    }
}; ?>

<div class="card border border-light-subtle rounded-4 shadow-sm overflow-hidden" wire:cloak
    x-data="{ showRecoveryCodes: false }">
    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary-subtle text-primary p-2 rounded-3">
                    <i class="bi bi-key-fill fs-5"></i>
                </div>
                <h6 class="fw-bold mb-0">{{ __('Codes de récupération d\'urgence') }}</h6>
            </div>

            <button type="button" @click="showRecoveryCodes = !showRecoveryCodes"
                class="btn btn-sm shadow-sm px-3 rounded-pill"
                :class="showRecoveryCodes ? 'btn-outline-secondary' : 'btn-primary'">
                <i class="bi" :class="showRecoveryCodes ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                <span class="ms-1"
                    x-text="showRecoveryCodes ? '{{ __('Masquer les codes') }}' : '{{ __('Afficher les codes') }}'"></span>
            </button>
        </div>
    </div>

    <div class="card-body p-4">
        <p class="text-muted small mb-0">
            {{ __('Chaque code ne peut être utilisé qu\'une seule fois. Si vous perdez l\'accès à votre application d\'authentification, ces codes sont le seul moyen de récupérer votre compte.') }}
        </p>

        <div x-show="showRecoveryCodes" x-collapse class="mt-4">

            @error('recoveryCodes')
                <div class="alert alert-danger py-2 px-3 small rounded-3"><i
                        class="bi bi-exclamation-circle me-2"></i>{{$message}}</div>
            @enderror

            @if (filled($recoveryCodes))
                <div class="bg-light p-4 rounded-4 shadow-inner border border-light-subtle position-relative">
                    <div class="row g-3">
                        @foreach($recoveryCodes as $code)
                            <div class="col-sm-6 col-md-4">
                                <div class="bg-white p-2 rounded-3 border shadow-sm text-center font-monospace user-select-all"
                                    style="font-size: 0.95rem; border-style: dashed !important; letter-spacing: 1px;"
                                    wire:loading.class="opacity-50 blur">
                                    {{ $code }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4">
                    <div class="d-flex align-items-center gap-2 text-warning fw-medium" style="font-size: 0.75rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>{{ __('Usage unique uniquement') }}</span>
                    </div>

                    <button type="button" class="btn btn-light border btn-sm shadow-sm rounded-3 px-3"
                        wire:click="regenerateRecoveryCodes" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="bi bi-arrow-clockwise me-1"></i> {{ __('Générer de nouveaux codes') }}
                        </span>
                        <span wire:loading>
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            {{ __('Génération...') }}
                        </span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>