<div class="container-fluid pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('stock.new_inventory') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('stock.inventory.index') }}">{{ __('stock.inventory') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('app.add') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4 overflow-visible">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">{{ __('stock.search_placeholder') }}</h6>
                    <div class="position-relative">
                        <div class="input-group shadow-sm">
                            <span class="input-group-text border-end-0 bg-white"><i
                                    class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0"
                                placeholder="{{ __('stock.search_placeholder') }}"
                                wire:model.live.debounce.250ms="search">
                        </div>

                        @if(!empty($results))
                            <ul class="list-group position-absolute w-100 shadow-lg z-3 mt-1">
                                @foreach($results as $res)
                                    <li class="list-group-item list-group-item-action cursor-pointer d-flex justify-content-between align-items-center"
                                        wire:click="addProduct({{ $res['id'] }})">
                                        <div>
                                            <span class="fw-bold">{{ $res['name'] }}</span>
                                        </div>
                                        <span class="badge bg-primary-subtle text-primary">{{ $res['stock_quantity'] }} in
                                            stock</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">{{ __('stock.inventory_items') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ __('purchases.product') }}</th>
                                    <th class="text-center">{{ __('stock.theoretical_qty') }}</th>
                                    <th class="text-center" style="width: 150px;">{{ __('stock.physical_qty') }}</th>
                                    <th class="text-center">{{ __('stock.difference') }}</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $index => $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold">{{ $item['name'] }}</div>
                                        </td>
                                        <td class="text-center text-muted">{{ $item['theoretical'] }}</td>
                                        <td class="text-center">
                                            <input type="number" class="form-control form-control-sm text-center"
                                                wire:model.live="items.{{ $index }}.physical">
                                        </td>
                                        <td
                                            class="text-center fw-bold {{ $item['difference'] < 0 ? 'text-danger' : ($item['difference'] > 0 ? 'text-success' : '') }}">
                                            {{ $item['difference'] > 0 ? '+' : '' }}{{ $item['difference'] }}
                                        </td>
                                        <td>
                                            <button wire:click="removeItem({{ $index }})"
                                                class="btn btn-sm text-danger p-0"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            {{ __('app.no_results') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 2rem;">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">{{ __('stock.inventory') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">{{ __('stock.inventory_date') }}</label>
                        <input type="date" class="form-control" wire:model="date">
                        @error('date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase">{{ __('stock.inventory_notes') }}</label>
                        <textarea class="form-control" wire:model="notes" rows="3"
                            placeholder="Notes optionnelles..."></textarea>
                    </div>

                    <button class="btn btn-primary btn-lg w-100 shadow-sm" wire:click="save"
                        wire:loading.attr="disabled" @if(empty($items)) disabled @endif>
                        <span wire:loading.remove wire:target="save">
                            <i class="bi bi-check2-circle me-1"></i> {{ __('app.save') }}
                        </span>
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-2"></span>...
                        </span>
                    </button>

                    @error('items') <div class="text-danger small mt-2 text-center">{{ $message }}</div> @enderror

                    <a href="{{ route('stock.inventory.index') }}"
                        class="btn btn-link w-100 mt-2 text-muted small">{{ __('app.back') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>