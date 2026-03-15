<?php

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && !Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return !Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full pb-5">
    <x-pages::settings.layout :heading="__('app.profile_title')" :subheading="__('app.profile_subtitle')">

        <div class="card shadow-sm border-0 overflow-hidden mb-4">
            <div class="card-body p-0">
                <!-- Profile Banner -->
                <div class="p-4 bg-body-tertiary border-bottom d-flex align-items-center gap-4">
                    <div
                        style="width: 80px; height: 80px; font-size: 2rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #e7f3ed; color: #198754; font-weight: bold;">
                        {{ auth()->user()->initials() }}
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
                        <p class="text-muted mb-0 small"><i class="bi bi-envelope me-1"></i> {{ auth()->user()->email }}
                        </p>
                        <span class="badge mt-2" style="background:#e7f3ed; color:#198754">
                            {{ auth()->user()->role ? __("users.roles." . \Illuminate\Support\Str::slug(auth()->user()->role->name, '_')) : __('users.user_single') }}
                        </span>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="p-4">
                    <form wire:submit=" updateProfileInformation">
                        <div class="mb-4">
                            <label
                                class="form-label fw-bold small text-uppercase text-muted">{{ __('users.full_name') }}</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text border-end-0"><i
                                        class="bi bi-person text-muted"></i></span>
                                <input type="text" wire:model="name" class="form-control border-start-0 py-2" required
                                    autofocus>
                            </div>
                            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label
                                class="form-label fw-bold small text-uppercase text-muted">{{ __('users.email') }}</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text border-end-0"><i class="bi bi-at text-muted"></i></span>
                                <input type="email" wire:model="email" class="form-control border-start-0 py-2"
                                    required>
                            </div>
                            @error('email') <div class="text-danger small mt-1">{{ $message }}
                            </div> @enderror

                            @if ($this->hasUnverifiedEmail)
                                <div class="mt-3 p-3 rounded border shadow-sm"
                                    style="background:#fff2d6; border-color: #ffab00 !important;">
                                    <div class="text-warning-emphasis small d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        {{ __('app.email_unverified') }}
                                    </div>

                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-bold"
                                        wire:click.prevent="resendVerificationNotification">
                                        {{ __('app.resend_verification_email') }}
                                    </button>

                                    @if (session('status') === 'verification-link-sent')
                                        <div class="mt-2 text-success small">
                                            <i class="bi bi-check2-all me-1"></i>
                                            {{ __('app.verification_link_sent') }}
                                    </div> @endif
                            </div> @endif
                        </div>
                        <div class="d-flex align-items-center gap-3 pt-2">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-check-circle me-2"></i> {{ __('app.save') }}
                            </button> <x-action-message class="text-success small fw-bold" on="profile-updated">
                                <i class="bi bi-check-lg me-1"></i> {{ __('app.profile_updated') }}
                            </x-action-message>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($this->showDeleteUser)
            <div class="card border-0 shadow-sm mt-5" style="border-left: 4px solid #ff3e1d !important;">
                <div class="card-body p-4">
                    <h6 class="text-danger fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-exclamation fs-5"></i>
                        {{ __('app.danger_zone') }}
                    </h6>
                    <livewire:pages::settings.delete-user-form />
                </div>
            </div>
        @endif
    </x-pages::settings.layout>
</section>