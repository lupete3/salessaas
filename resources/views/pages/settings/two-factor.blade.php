<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

new class extends Component {
    #[Locked]
    public bool $twoFactorEnabled;

    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showModal = false;

    public bool $showVerificationStep = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        abort_unless(Features::enabled(Features::twoFactorAuthentication()), Response::HTTP_FORBIDDEN);

        if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication(auth()->user());
        }

        $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enable(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $enableTwoFactorAuthentication(auth()->user());

        if (!$this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        }

        $this->loadSetupData();

        $this->showModal = true;
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = auth()->user();

        try {
            $this->qrCodeSvg = $user?->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(auth()->user(), $this->code);

        $this->closeModal();

        $this->twoFactorEnabled = true;
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showModal',
            'showVerificationStep',
        );

        $this->resetErrorBag();

        if (!$this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        }
    }

    /**
     * Get the current modal configuration state.
     */
    public function getModalConfigProperty(): array
    {
        if ($this->twoFactorEnabled) {
            return [
                'title' => __('Double authentification activée'),
                'description' => __('La double authentification est maintenant activée. Scannez le code QR ou entrez la clé de configuration dans votre application d\'authentification.'),
                'buttonText' => __('Fermer'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Vérifier le code d\'authentification'),
                'description' => __('Entrez le code à 6 chiffres de votre application d\'authentification.'),
                'buttonText' => __('Continuer'),
            ];
        }

        return [
            'title' => __('Activer la double authentification'),
            'description' => __('Pour terminer l\'activation de la double authentification, scannez le code QR ou entrez la clé de configuration dans votre application d\'authentification.'),
            'buttonText' => __('Continuer'),
        ];
    }
} ?>

<section class="w-full pb-5">
    <x-pages::settings.layout :heading="__('Sécurité Avancée')" :subheading="__('Protégez votre compte avec une couche de sécurité supplémentaire')">
        
        <!-- Status Card -->
        <div class="card shadow-sm border-0 overflow-hidden mb-4 rounded-4" wire:cloak>
            <div class="p-4 d-flex align-items-center justify-content-between flex-wrap gap-3 {{ $twoFactorEnabled ? 'bg-success-subtle border-start border-4 border-success' : 'bg-warning-subtle border-start border-4 border-warning' }}">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-circle shadow-sm {{ $twoFactorEnabled ? 'bg-success text-white' : 'bg-warning text-dark' }}">
                        <i class="bi {{ $twoFactorEnabled ? 'bi-shield-fill-check' : 'bi-shield-fill-exclamation' }} fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">
                            {{ $twoFactorEnabled ? __('Authentification 2FA Activée') : __('Authentification 2FA Désactivée') }}
                        </h5>
                        <p class="mb-0 small opacity-75">
                            {{ $twoFactorEnabled ? __('Votre compte est hautement sécurisé.') : __('Votre compte est vulnérable. Activez la 2FA dès maintenant.') }}
                        </p>
                    </div>
                </div>
                
                @if (!$twoFactorEnabled)
                    <button class="btn btn-warning shadow-sm px-4 fw-bold" wire:click="enable">
                        <i class="bi bi-shield-plus me-2"></i> {{ __('Activer maintenant') }}
                    </button>
                @endif
            </div>

            <div class="card-body p-4">
                @if ($twoFactorEnabled)
                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle text-primary"></i> {{ __('Comment ça marche ?') }}
                            </h6>
                            <p class="text-muted small">
                                {{ __('À chaque fois que vous vous connecterez, vous devrez fournir un code unique généré par votre application d\'authentification (Google Authenticator, Microsoft Authenticator, etc.).') }}
                            </p>
                            <div class="d-flex gap-2 mt-4">
                                <button class="btn btn-outline-danger btn-sm shadow-sm" wire:click="disable">
                                    <i class="bi bi-shield-slash me-2"></i> {{ __('Désactiver la protection') }}
                                </button>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="p-3 bg-body-tertiary rounded-4 border border-light-subtle">
                                <div class="d-flex align-items-center gap-3 mb-2 text-success">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                    <span class="fw-bold small">{{ __('Protection active') }}</span>
                                </div>
                                <p class="text-muted small mb-0">{{ __('Les codes de récupération sont votre dernier recours si vous perdez votre appareil.') }}</p>
                            </div>
                        </div>
                    </div>

                    <livewire:pages::settings.two-factor.recovery-codes :$requiresConfirmation />
                @else
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="text-muted mb-0">
                                {{ __('La double authentification ajoute une barrière impénétrable entre votre compte et les attaquants. Même si quelqu\'un possède votre mot de passe, il ne pourra pas accéder à votre compte sans votre téléphone.') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-center d-none d-md-block">
                            <i class="bi bi-shield-lock text-warning opacity-25" style="font-size: 5rem;"></i>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </x-pages::settings.layout>

    <!-- Enhanced Modal 2FA (Alpine.js) -->
    <div x-data="{ open: @entangle('showModal') }" 
         x-show="open" 
         class="modal fade" 
         :class="{ 'show d-block': open }"
         tabindex="-1" 
         style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);"
         @keydown.escape.window="open = false"
         x-cloak>
        <div class="modal-dialog modal-dialog-centered shadow">
            <div class="modal-content border-0 rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-primary text-white p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-25 p-2 rounded-circle">
                            <i class="bi bi-shield-lock-fill fs-4"></i>
                        </div>
                        <h5 class="modal-title fw-bold">{{ $this->modalConfig['title'] }}</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" @click="open = false"></button>
                </div>

                <div class="modal-body p-4 text-center">
                    <p class="text-muted mb-4">{{ $this->modalConfig['description'] }}</p>

                    @if ($showVerificationStep)
                        <div class="mb-4 mx-auto" style="max-width: 300px;">
                            <label class="form-label fw-bold small text-uppercase text-muted mb-3">{{ __('Saisir le code de vérification') }}</label>
                            <input type="text" 
                                   wire:model="code" 
                                   class="form-control form-control-lg text-center fw-bold shadow-sm rounded-3 border-2" 
                                   style="font-size: 1.8rem; letter-spacing: 0.8rem; height: 70px;"
                                   placeholder="000000" 
                                   maxlength="6"
                                   autofocus>
                            @error('code') <div class="text-danger small mt-2 fw-bold"><i class="bi bi-exclamation-circle-fill me-1"></i> {{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex gap-3 justify-content-center">
                            <button class="btn btn-outline-secondary px-4 py-2 rounded-3" wire:click="resetVerification">
                                <i class="bi bi-arrow-left me-1"></i> {{ __('Retour') }}
                            </button>
                            <button class="btn btn-primary px-5 py-2 rounded-3 shadow-sm fw-bold" 
                                    wire:click="confirmTwoFactor" 
                                    :disabled="$wire.code.length < 6">
                                {{ __('Confirmer l\'activation') }}
                            </button>
                        </div>
                    @else
                        @error('setupData')
                            <div class="alert alert-danger mb-4 shadow-sm py-2"><i class="bi bi-x-circle-fill me-2"></i> {{ $message }}</div>
                        @enderror

                        @if($qrCodeSvg)
                            <div class="p-2 bg-white bg-opacity-10 border-2 border-primary border-dashed rounded-4 d-inline-block mb-4 shadow-sm">
                                <div class="p-3 bg-white rounded-3 shadow-inner">
                                    {!! $qrCodeSvg !!}
                                </div>
                            </div>
                        @else
                            <div class="py-5 text-muted">
                                <div class="spinner-border text-primary me-2" role="status"></div> 
                                <span>{{ __('Génération sécurisée du code QR...') }}</span>
                            </div>
                        @endif

                        <div class="mb-4 text-start bg-body-tertiary p-3 rounded-4 border border-light-subtle shadow-inner">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold small text-uppercase text-muted mb-0">{{ __('Clé de configuration') }}</label>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">{{ __('MANUEL') }}</span>
                            </div>
                            <div class="input-group">
                                <input type="text" readonly value="{{ $manualSetupKey }}" class="form-control form-control-sm border-end-0 font-monospace text-center py-2">
                                <button class="btn btn-primary btn-sm" x-data="{ copied: false }" @click="navigator.clipboard.writeText('{{ $manualSetupKey }}'); copied = true; setTimeout(() => copied = false, 2000)">
                                    <i class="bi" :class="copied ? 'bi-check-lg' : 'bi-clipboard'"></i>
                                </button>
                            </div>
                        </div>

                        <button class="btn btn-primary w-100 shadow-sm py-3 rounded-3 fw-bold fs-5" 
                                wire:click="showVerificationIfNecessary" 
                                :disabled="$errors->has('setupData')">
                            {{ $this->modalConfig['buttonText'] }} <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>