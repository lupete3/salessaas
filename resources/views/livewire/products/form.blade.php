<div class="row g-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">{{ $product && $product->exists ? __('app.edit') : __('app.add') }}
                    {{ __('products.title') }}
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('products.index') }}">{{ __('products.title') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('app.form') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">{{ __('products.description') }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit="save">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold small text-uppercase text-muted">{{ __('products.name') }}
                                <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                placeholder="{{ __('products.name') }}" wire:model="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label
                                class="form-label fw-bold small text-uppercase text-muted">{{ __('products.barcode') }}</label>
                            <input type="text" class="form-control" wire:model="barcode" placeholder="Scanner ici...">
                        </div>
                        <div class="col-md-12">
                            <label
                                class="form-label fw-bold small text-uppercase text-muted">{{ __('products.generic_name') }}</label>
                            <input type="text" class="form-control" wire:model="generic_name"
                                placeholder="ex: Marque, Modèle ou Type">
                        </div>
                        <div class="col-md-4">
                            <label
                                class="form-label fw-bold small text-uppercase text-muted">{{ __('products.category') }}</label>
                            <select class="form-select" wire:model="category">
                                <option value="">{{ __('app.choose') }}...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ __("products.categories.{$cat}") }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label
                                class="form-label fw-bold small text-uppercase text-muted">{{ __('products.form') }}</label>
                            <select class="form-select" wire:model="form">
                                <option value="">Choisir...</option>
                                @foreach($forms as $f)
                                    <option value="{{ $f }}">{{ __("products.forms.{$f}") }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label
                                class="form-label fw-bold small text-uppercase text-muted">{{ __('products.dosage') }}</label>
                            <input type="text" class="form-control"
                                placeholder="{{ __('products.dosage') }} (ex: Taille, Poids)" wire:model="dosage">
                        </div>
                        <div class="col-md-6">
                            <label
                                class="form-label fw-bold small text-uppercase">{{ __('products.purchase_price') }}</label>
                            <div class="input-group">
                                <input type="number" step="0.01"
                                    class="form-control @error('purchase_price') is-invalid @enderror"
                                    wire:model="purchase_price" placeholder="0.00">
                                <span class="input-group-text">{{ $currency }}</span>
                            </div>
                            @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase">{{ __('products.selling_price') }}
                                <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01"
                                    class="form-control @error('selling_price') is-invalid @enderror"
                                    wire:model="selling_price" placeholder="0.00">
                                <span class="input-group-text">{{ $currency }}</span>
                            </div>
                            @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label
                                class="form-label fw-bold small text-uppercase text-muted">{{ __('products.stock_alert_threshold') }}</label>
                            <input type="number" class="form-control" wire:model="min_stock_alert">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-uppercase">{{ __('products.unit') }}</label>
                            <input type="text" class="form-control" placeholder="ex: pièce, boîte" wire:model="unit">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="activeSwitch"
                                    wire:model="is_active">
                                <label class="form-check-label fw-bold"
                                    for="activeSwitch">{{ __('products.is_active_label') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary btn-lg px-5" wire:loading.attr="disabled"
                            wire:target="save">
                            <span wire:loading.remove wire:target="save">
                                <i class="bi bi-save me-2"></i> {{ __('app.save') }}
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                {{ __('products.recording') }}...
                            </span>
                        </button>
                        <a href="{{ route('products.index') }}"
                            class="btn btn-outline-secondary btn-lg ms-2">{{ __('app.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Stock Initial Card (only if creating) -->
        @if(!$product || !$product->exists)
            <div class="card shadow-sm border-0 mb-4 bg-body-tertiary">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">{{ __('products.initial_stock') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">{{ __('products.batch_number') }}</label>
                        <input type="text" class="form-control form-control-sm bg-white" wire:model="batch_number"
                            placeholder="ex: LOT-2024-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">{{ __('products.stock_quantity') }}</label>
                        <input type="number" class="form-control form-control-sm bg-white" wire:model="batch_quantity">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-uppercase">{{ __('products.expiry_date') }}</label>
                        <input type="date" class="form-control form-control-sm bg-white" wire:model="expiry_date">
                    </div>
                </div>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title mb-0">{{ __('products.supplier') }} & Notes</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase">{{ __('products.usual_supplier') }}</label>
                    <select class="form-select form-select-sm" wire:model="supplier_id">
                        <option value="0">{{ __('app.no_results') }}</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small fw-bold text-uppercase">{{ __('products.description') }}</label>
                    <textarea class="form-control form-control-sm" rows="4" wire:model="description"
                        placeholder="{{ __('products.description') }}..."></textarea>
                </div>
            </div>
        </div>
    </div>
</div>