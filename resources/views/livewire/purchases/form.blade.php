<div class="container-fluid pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ $purchase && $purchase->exists ? __('purchases.edit') : __('purchases.add') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">{{ __('purchases.title') }}</a></li>
                    <li class="breadcrumb-item active">{{ $purchase && $purchase->exists ? __('app.edit') : __('app.add') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Items to Add & List -->
        <div class="col-lg-8">
            <!-- Add Item Search -->
            <div class="card shadow-sm border-0 mb-4 overflow-visible">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">{{ __('pos.search_product') ?? 'Rechercher un produit' }}</h6>
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 @error('itemProductId') is-invalid @enderror" 
                                placeholder="{{ __('products.name') }}..." 
                                wire:model.live.debounce.250ms="itemSearch">
                        </div>
                        @error('itemProductId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                        @if(!empty($itemResults))
                            <ul class="list-group position-absolute w-100 shadow-lg z-3 mt-1">
                                @foreach($itemResults as $res)
                                    <li class="list-group-item list-group-item-action cursor-pointer" 
                                        wire:click="selectItem({{ $res['id'] }}, '{{ addslashes($res['name']) }}', {{ $res['purchase_price'] }})">
                                        {{ $res['name'] }} — <span class="text-primary">{{ number_format($res['purchase_price'], 2) }} {{ $currency }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    @if($itemProductId)
                        <div class="row g-3 mt-3 p-3 bg-body-tertiary rounded border border-primary-subtle">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">{{ __('purchases.quantity') }}</label>
                                <input type="number" class="form-control @error('itemQty') is-invalid @enderror" wire:model="itemQty" min="1">
                                @error('itemQty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">{{ __('purchases.unit_price') }}</label>
                                <input type="number" step="0.01" class="form-control @error('itemPrice') is-invalid @enderror" wire:model="itemPrice">
                                @error('itemPrice') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">{{ __('purchases.batch_number') }}</label>
                                <input type="text" class="form-control" wire:model="itemBatch" placeholder="{{ __('purchases.batch_placeholder') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">{{ __('purchases.expiry_date') }}</label>
                                <input type="date" class="form-control" wire:model="itemExpiry">
                            </div>
                            <div class="col-12 text-end mt-3">
                                <button type="button" class="btn btn-primary" wire:click="addItem" 
                                    wire:loading.attr="disabled" wire:target="addItem">
                                    <span wire:loading.remove wire:target="addItem">
                                        <i class="bi bi-plus-circle me-1"></i> {{ __('purchases.add_to_invoice') }}
                                    </span>
                                    <span wire:loading wire:target="addItem">
                                        <span class="spinner-border spinner-border-sm"></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Items List -->
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom py-3">
                    <h5 class="mb-0">{{ __('purchases.items_on_invoice') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 @error('items') border border-danger @enderror">
                    @error('items') <div class="p-2 text-danger small"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div> @enderror
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ __('purchases.product') ?? 'Produit' }}</th>
                                    <th class="text-center">{{ __('purchases.batch_number') }} / Exp.</th>
                                    <th class="text-center">{{ __('purchases.quantity') }}</th>
                                    <th class="text-end">{{ __('purchases.unit_price') }}</th>
                                    <th class="text-end pe-3">{{ __('purchases.total_amount') }}</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $index => $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold">{{ $item['name'] }}</div>
                                        </td>
                                        <td class="text-center small">
                                            @if($item['batch_number']) <span class="badge bg-body-secondary text-body border">{{ $item['batch_number'] }}</span><br>@endif
                                            @if($item['expiry_date']) <span class="text-muted small">{{ \Carbon\Carbon::parse($item['expiry_date'])->format('d/m/Y') }}</span> @endif
                                        </td>
                                        <td class="text-center">{{ $item['qty'] }}</td>
                                        <td class="text-end">{{ number_format($item['unit_price'], 2) }}</td>
                                        <td class="text-end pe-3 fw-bold">{{ number_format($item['subtotal'], 2) }}</td>
                                        <td>
                                            <button wire:click="removeItem({{ $index }})" class="btn btn-sm text-danger p-0"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">{{ __('app.no_results') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Summary & Save -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">{{ __('purchases.invoice_info') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">{{ __('purchases.supplier') }}</label>
                        <select class="form-select @error('supplier_id') is-invalid @enderror" wire:model="supplier_id">
                            <option value="">{{ __('purchases.select_supplier') }}</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">{{ __('purchases.due_date_label') }}</label>
                        <input type="date" class="form-control" wire:model="due_date">
                    </div>

                    <div class="bg-body-tertiary p-3 rounded mb-3 border">
                        <div class="d-flex justify-content-between mb-2 fs-5">
                            <span>{{ __('purchases.total_invoice') }}:</span>
                                    <h4 class="fw-bold mb-0">{{ number_format($this->total_amount, 2) }} {{ $currency }}</h4>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ __('purchases.amount_paid_label') }}:</span>
                            <div class="input-group input-group-sm w-50">
                                <input type="number" step="0.01" class="form-control text-end" wire:model.live="amount_paid">
                                <span class="input-group-text">{{ $currency }}</span>
                            </div>
                        </div>
                    </div>

                    @php 
                        $balance = max(0, $this->getTotal() - ($amount_paid ?: 0));
                    @endphp

                    @if($balance > 0)
                        <div class="bg-warning-subtle p-2 rounded mb-4 text-center border border-warning-subtle text-warning">
                            <small class="text-uppercase fw-bold" style="font-size: 0.65rem;">{{ __('app.balance') ?? 'Reste' }}:</small>
                            <h4 class="mb-0 fw-bold">{{ number_format($balance, 2) }} {{ $currency }}</h4>
                        </div>
                    @else
                        <div class="bg-success-subtle p-2 rounded mb-4 text-center border border-success-subtle text-success">
                            <i class="bi bi-check-circle me-1"></i> {{ __('purchases.paid_full') ?? 'Réglé en totalité' }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase">{{ __('purchases.notes_label') }}</label>
                        <textarea class="form-control" wire:model="notes" rows="2"></textarea>
                    </div>

                    <button class="btn btn-primary btn-lg w-100 shadow-sm" 
                        wire:click="save" wire:loading.attr="disabled" wire:target="save"
                        @if(empty($items)) disabled @endif>
                        <span wire:loading.remove wire:target="save">
                            <i class="bi bi-check2-circle me-1"></i> {{ __('purchases.finalize') }}
                        </span>
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-2"></span>...
                        </span>
                    </button>
                    <a href="{{ route('purchases.index') }}" class="btn btn-link w-100 mt-2 text-muted small">{{ __('app.back') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>