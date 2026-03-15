<x-layouts::auth>
    <div class="auth-content">
        <x-auth-header :title="__('auth.login_title')" :description="__('auth.login_description')" />

        <!-- Session Status -->
        <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}">
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

            <!-- Password -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password"
                        class="form-label fw-bold small text-uppercase text-muted mb-0">{{ __('auth.password') }}</label>
                    @if (Route::has('password.request'))
                        <a class="text-primary small text-decoration-none" href="{{ route('password.request') }}">
                            {{ __('auth.forgot_password') }}
                        </a>
                    @endif
                </div>
                <div class="input-group shadow-sm" x-data="{ show: false }">
                    <span class="input-group-text border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                    <input :type="show ? 'text' : 'password'" id="password" name="password"
                        class="form-control border-start-0 py-2 @error('password') is-invalid @enderror" required
                        placeholder="••••••••">
                    <button type="button" class="input-group-text border-start-0" @click="show = !show">
                        <i class="bi" :class="show ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Remember Me -->
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label small text-muted" for="remember">
                    {{ __('auth.remember_me') }}
                </label>
            </div>

            <div class="mb-4">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i> {{ __('auth.login_button') }}
                </button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="text-center mt-4 pt-2 border-top">
                <p class="text-muted small mb-0">
                    {{ __('auth.no_account') }}
                    <a href="{{ route('register') }}"
                        class="fw-bold text-primary text-decoration-none ms-1">{{ __('auth.create_account') }}</a>
                </p>
            </div>
        @endif
    </div>
</x-layouts::auth>