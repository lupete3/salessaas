<x-layouts::auth>
    <div class="auth-content" x-cloak x-data="{
             showRecoveryInput: @js($errors->has('recovery_code')),
             code: '',
             recovery_code: '',
             toggleInput() {
                 this.showRecoveryInput = !this.showRecoveryInput;
                 this.code = '';
                 this.recovery_code = '';
             }
         }">

        <div x-show="!showRecoveryInput">
            <x-auth-header :title="__('Code d\'authentification')" :description="__('Veuillez confirmer l\'accès à votre compte en saisissant le code d\'authentification fourni par votre application.')" />
        </div>

        <div x-show="showRecoveryInput">
            <x-auth-header :title="__('Code de récupération')" :description="__('Veuillez confirmer l\'accès à votre compte en saisissant l\'un de vos codes de récupération d\'urgence.')" />
        </div>

        <form method="POST" action="{{ route('two-factor.login.store') }}">
            @csrf

            <!-- Standard OTP Input (Bootstrap Styled) -->
            <div class="mb-4 text-center" x-show="!showRecoveryInput">
                <label for="code"
                    class="form-label fw-bold small text-uppercase text-muted mb-3">{{ __('Votre code à 6 chiffres') }}</label>
                <input type="text" name="code" id="code" x-model="code"
                    class="form-control form-control-lg text-center fw-bold shadow-sm rounded-3 border-2"
                    style="font-size: 1.8rem; letter-spacing: 0.8rem; height: 70px;" placeholder="000000" maxlength="6"
                    x-bind:required="!showRecoveryInput" autofocus>
                @error('code')
                    <div class="text-danger small mt-2 fw-bold">{{ $message }}</div>
                @enderror
            </div>

            <!-- Recovery Code Input -->
            <div class="mb-4" x-show="showRecoveryInput">
                <label for="recovery_code"
                    class="form-label fw-bold small text-uppercase text-muted">{{ __('Code de récupération') }}</label>
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-key text-muted"></i></span>
                    <input type="text" name="recovery_code" id="recovery_code" x-model="recovery_code"
                        class="form-control border-start-0 py-2 @error('recovery_code') is-invalid @enderror"
                        placeholder="abcdef-123456" x-bind:required="showRecoveryInput">
                    @error('recovery_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                    {{ __('Continuer') }} <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>

            <div class="text-center mt-4 pt-2 border-top">
                <button type="button" @click="toggleInput()"
                    class="btn btn-link text-muted small text-decoration-none p-0">
                    <span x-show="!showRecoveryInput">{{ __('Utiliser un code de récupération') }}</span>
                    <span x-show="showRecoveryInput">{{ __('Utiliser un code d\'authentification') }}</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts::auth>