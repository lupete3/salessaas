<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                {{ $supplier && $supplier->exists ? __('suppliers.edit') : __('suppliers.add') }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">{{ __('suppliers.title') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ __('app.form') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form wire:submit="save">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase">{{ __('suppliers.name') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name"
                            placeholder="ex: Wholesale Pharma">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label
                            class="form-label fw-bold small text-uppercase">{{ __('suppliers.contact_name') }}</label>
                        <input type="text" class="form-control" wire:model="contact_person" placeholder="ex: Mr. Jean">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase">{{ __('suppliers.phone') }}</label>
                        <input type="text" class="form-control" wire:model="phone" placeholder="ex: +243...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase">{{ __('suppliers.email') }}</label>
                        <input type="email" class="form-control" wire:model="email"
                            placeholder="contact@fournisseur.cd">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small text-uppercase">{{ __('suppliers.address') }}</label>
                        <textarea class="form-control" wire:model="address" rows="2"></textarea>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-2"></span>...
                        </span>
                    </button>
                    <a href="{{ route('suppliers.index') }}"
                        class="btn btn-outline-secondary ms-2">{{ __('app.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>