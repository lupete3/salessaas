<x-layouts::auth>
    <div class="auth-content">
        <x-auth-header :title="__('auth.register_title')" :description="__('auth.register_description')" />

        <!-- Session Status -->
        <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <!-- Name -->
            <div class="mb-3">
                <label for="name"
                    class="form-label fw-bold small text-uppercase text-muted">{{ __('auth.full_name') }}</label>
                <div class="input-group shadow-sm">
                    <span class="input-group-text border-end-0"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        class="form-control border-start-0 py-2 @error('name') is-invalid @enderror" required autofocus
                        placeholder="Ex: Placide Bourgeois">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Store Name -->
            <div class="mb-3">
                <label for="store_name"
                    class="form-label fw-bold small text-uppercase text-muted">{{ __('auth.store_name') }}</label>
                <div class="input-group shadow-sm">
                    <span class="input-group-text border-end-0"><i class="bi bi-shop text-muted"></i></span>
                    <input type="text" id="store_name" name="store_name" value="{{ old('store_name') }}"
                        class="form-control border-start-0 py-2 @error('store_name') is-invalid @enderror" required
                        placeholder="{{ __('auth.store_name_placeholder') }}">
                    @error('store_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Email Address -->
            <div class="mb-3">
                <label for="email"
                    class="form-label fw-bold small text-uppercase text-muted">{{ __('auth.email_address') }}</label>
                <div class="input-group shadow-sm">
                    <span class="input-group-text border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        class="form-control border-start-0 py-2 @error('email') is-invalid @enderror" required
                        placeholder="nom@exemple.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password"
                    class="form-label fw-bold small text-uppercase text-muted">{{ __('auth.password') }}</label>
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

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="password_confirmation"
                    class="form-label fw-bold small text-uppercase text-muted">{{ __('auth.confirm_password') }}</label>
                <div class="input-group shadow-sm" x-data="{ show: false }">
                    <span class="input-group-text border-end-0"><i class="bi bi-shield-check text-muted"></i></span>
                    <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation"
                        class="form-control border-start-0 py-2" required placeholder="••••••••">
                    <button type="button" class="input-group-text border-start-0" @click="show = !show">
                        <i class="bi" :class="show ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                    <i class="bi bi-person-plus me-2"></i> {{ __('auth.register_button') }}
                </button>
            </div>
        </form>

        <div class="text-center mt-4 pt-2 border-top">
            <p class="text-muted small mb-0">
                {{ __('auth.already_registered') }}
                <a href="{{ route('login') }}"
                    class="fw-bold text-primary text-decoration-none ms-1">{{ __('auth.login_button') }}</a>
            </p>
        </div>
    </div>
</x-layouts::auth>