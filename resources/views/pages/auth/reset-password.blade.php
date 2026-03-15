<x-layouts::auth>
    <div class="auth-content">
        <x-auth-header :title="__('auth.reset_password_title')" :description="__('auth.reset_password_description')" />

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div class="mb-3">
                <label for="email"
                    class="form-label fw-bold small text-uppercase text-muted">{{ __('auth.email_address') }}</label>
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i
                            class="bi bi-envelope text-muted"></i></span>
                    <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}"
                        class="form-control border-start-0 py-2 @error('email') is-invalid @enderror" required readonly>
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
                    <span class="input-group-text bg-white border-end-0"><i
                            class="bi bi-shield-lock text-muted"></i></span>
                    <input :type="show ? 'text' : 'password'" id="password" name="password"
                        class="form-control border-start-0 py-2 @error('password') is-invalid @enderror" required
                        autofocus placeholder="••••••••">
                    <button type="button" class="input-group-text bg-white border-start-0" @click="show = !show">
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
                    <span class="input-group-text bg-white border-end-0"><i
                            class="bi bi-shield-check text-muted"></i></span>
                    <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation"
                        class="form-control border-start-0 py-2" required placeholder="••••••••">
                    <button type="button" class="input-group-text bg-white border-start-0" @click="show = !show">
                        <i class="bi" :class="show ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                    <i class="bi bi-check2-circle me-2"></i> {{ __('auth.reset_password_button') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts::auth>