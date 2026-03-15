<x-layouts::auth>
    <div class="auth-content">
        <x-auth-header :title="__('auth.confirm_password_title')"
            :description="__('auth.confirm_password_description')" />

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <!-- Password -->
            <div class="mb-4">
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

            <div class="mb-4">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                    <i class="bi bi-check2-circle me-2"></i> {{ __('app.confirm') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts::auth>