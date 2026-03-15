<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ $customer && $customer->exists ? __('app.edit') : __('app.add') }}
                {{ __('customers.customer_single') ?? 'Client' }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">{{ __('customers.title') }}</a>
                    </li>
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
                        <label class="form-label fw-bold small text-uppercase">{{ __('customers.name') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name"
                            placeholder="ex: Jean Dupont">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase">{{ __('customers.phone') }}</label>
                        <input type="text" class="form-control" wire:model="phone" placeholder="ex: +243...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase">{{ __('customers.email') }}</label>
                        <input type="email" class="form-control" wire:model="email" placeholder="client@email.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase">{{ __('customers.address') }}</label>
                        <input type="text" class="form-control" wire:model="address" placeholder="Adresse complète">
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ __('app.save') }}</span>
                        <span wire:loading><span class="spinner-border spinner-border-sm me-2"></span>...</span>
                    </button>
                    <a href="{{ route('customers.index') }}"
                        class="btn btn-outline-secondary ms-2">{{ __('app.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>