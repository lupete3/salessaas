<div class="row g-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom">
                <h6 class="mb-0 fw-bold">{{ __('app.settings') }}</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('settings.profile') }}" @class(['list-group-item list-group-item-action border-0 d-flex align-items-center gap-2 py-3', 'active' => request()->routeIs('settings.profile')])
                    wire:navigate>
                    <i class="bi bi-person-circle fs-5"></i> {{ __('app.profile_title') }}
                </a>
                @if (auth()->user()->role?->name === 'proprietaire')
                    <a href="{{ route('settings.company') }}" @class(['list-group-item list-group-item-action border-0 d-flex align-items-center gap-2 py-3', 'active' => request()->routeIs('settings.company')])
                        wire:navigate>
                        <i class="bi bi-building fs-5"></i> {{ __('app.company_title') }}
                    </a>
                @endif
                <a href="{{ route('settings.password') }}" @class(['list-group-item list-group-item-action border-0 d-flex align-items-center gap-2 py-3', 'active' => request()->routeIs('settings.password')])
                    wire:navigate>
                    <i class="bi bi-shield-lock-fill fs-5"></i> {{ __('app.password_tab') }}
                </a>
                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                    <a href="{{ route('settings.two-factor') }}" @class(['list-group-item list-group-item-action border-0 d-flex align-items-center gap-2 py-3', 'active' => request()->routeIs('settings.two-factor')])
                        wire:navigate>
                        <i class="bi bi-safe2-fill fs-5"></i> {{ __('app.two_factor_auth') }}
                    </a>
                @endif
                <a href="{{ route('settings.appearance') }}" @class(['list-group-item list-group-item-action border-0 d-flex align-items-center gap-2 py-3', 'active' => request()->routeIs('settings.appearance')])
                    wire:navigate>
                    <i class="bi bi-palette-fill fs-5"></i> {{ __('app.appearance_title') }}
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="mb-4">
            <h4 class="fw-bold mb-1">{{ $heading ?? '' }}</h4>
            <p class="text-muted">{{ $subheading ?? '' }}</p>
        </div>

        <div class="mt-4">
            {{ $slot }}
        </div>
    </div>
</div>