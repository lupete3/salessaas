<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ $user && $user->exists ? __('app.edit') : __('app.add') }}
                {{ __('Utilisateur') }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('users.title') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('app.form') ?? 'Formulaire' }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form wire:submit="save">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase">{{ __('users.full_name') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase">{{ __('users.email') }} <span
                                class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                            wire:model="email">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase">{{ __('users.phone') }}</label>
                        <input type="text" class="form-control" wire:model="phone">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase">{{ __('users.role') }} <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('role_id') is-invalid @enderror" wire:model="role_id">
                            <option value="">{{ __('users.choose_role') ?? 'Choisir un rôle' }}...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">
                                    {{ __("users.roles." . \Illuminate\Support\Str::slug($role->name, '_')) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label
                            class="form-label fw-bold small text-uppercase">{{ __('users.language') ?? 'Langue' }}</label>
                        <select class="form-select" wire:model="locale">
                            <option value="fr">Français</option>
                            <option value="en">English</option>
                            <option value="sw">Kiswahili</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase">{{ __('users.password') }}
                            @if(!$user || !$user->exists) <span class="text-danger">*</span> @else ({{
                            __('users.leave_blank') ?? 'laisser vide pour ne pas changer' }}) @endif</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            wire:model="password">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-2"></span>...
                        </span>
                    </button>
                    <a href="{{ route('users.index') }}"
                        class="btn btn-outline-secondary ms-2">{{ __('app.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>