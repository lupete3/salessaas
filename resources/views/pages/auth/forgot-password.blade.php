<x-layouts::auth>
    <div class="auth-content">
        <x-auth-header :title="__('auth.forgot_password_title')"
            :description="__('auth.forgot_password_description')" />

        <!-- Session Status -->
        <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-4">
                <label for="email"
                    class="form-label fw-bold small text-uppercase text-muted">{{ __('auth.email_address') }}</label>
                <div class="input-group shadow-sm">
                    <span class="input-group-text border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        class="form-control border-start-0 py-2 @error('email') is-invalid @enderror" required autofocus
                        placeholder="nom@exemple.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                    <i class="bi bi-send-fill me-2"></i> {{ __('auth.send_reset_link') }}
                </button>
            </div>
        </form>

        <div class="text-center mt-4 pt-2 border-top">
            <a href="{{ route('login') }}" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> {{ __('auth.login_button') }}
            </a>
        </div>
    </div>
</x-layouts::auth>