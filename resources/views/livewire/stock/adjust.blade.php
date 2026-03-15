<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('stock.adjust_title') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('stock.movements') }}">{{ __('app.stock') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('stock.adjust') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    @if(!$productId)
                        <div class="position-relative mb-4">
                            <label class="form-label fw-bold">{{ __('stock.search_product_to_adjust') }}</label>
                            <div class="input-group input-group-lg border rounded-3 overflow-hidden shadow-sm">
                                <span class="input-group-text border-0"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control border-0"
                                    placeholder="{{ __('products.search_placeholder') }}"
                                    wire:model.live.debounce.250ms="search">
                            </div>

                            @if(!empty($results))
                                <div class="pos-results mt-1 border rounded shadow" style="top: 100%">
                                    @foreach($results as $res)
                                        <div class="pos-item" wire:click="selectProduct({{ $res['id'] }})">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ $res['name'] }}</strong>
                                                <span class="text-muted">{{ __('pos.in_stock') }}
                                                    {{ $res['stock_quantity'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-search display-1 opacity-25"></i>
                            <p class="mt-3">{{ __('stock.select_product_to_start') }}</p>
                        </div>
                    @else
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <div>
                                <span
                                    class="text-muted small text-uppercase fw-bold">{{ __('stock.selected_product') }}</span>
                                <h4 class="fw-bold text-primary mb-0">{{ $productName }}</h4>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary"
                                wire:click="$set('productId', null)">{{ __('app.cancel') }}</button>
                        </div>

                        <form wire:submit="save">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label
                                        class="form-label text-muted small fw-bold text-uppercase">{{ __('products.stock_quantity') }}</label>
                                    <div class="form-control-plaintext fs-3 fw-bold">{{ $currentQty }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label text-dark small fw-bold text-uppercase">{{ __('stock.new_quantity') }}</label>
                                    <input type="number"
                                        class="form-control form-control-lg @error('newQty') is-invalid @enderror"
                                        wire:model="newQty">
                                    @error('newQty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label
                                        class="form-label text-dark small fw-bold text-uppercase">{{ __('stock.adjustment_reason') }}</label>
                                    <textarea class="form-control @error('reason') is-invalid @enderror" wire:model="reason"
                                        rows="3" placeholder="{{ __('stock.adjustment_reason_placeholder') }}"></textarea>
                                    @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 pt-3">
                                    <button type="submit"
                                        class="btn btn-primary btn-lg w-100">{{ __('stock.confirm_adjustment') }}</button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>