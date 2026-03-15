<x-layouts::auth>
    <div class="auth-content text-center">
        <x-auth-header :title="__('auth.verify_email_title')" :description="__('auth.verify_email_description')" />

        @if (session('status') == 'verification-link-sent')
            <div
                class="alert alert-success shadow-sm py-2 px-3 small rounded-3 border-0 bg-success bg-opacity-10 text-success fw-medium mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ __('auth.verify_email_sent') }}
            </div>
        @endif

        <div class="mt-4 flex flex-col gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                    <i class="bi bi-envelope-paper me-2"></i> {{ __('auth.resend_verification') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-link text-muted small text-decoration-none">
                    <i class="bi bi-box-arrow-right me-1"></i> {{ __('app.logout') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts::auth>