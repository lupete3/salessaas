<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('stock.history') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('app.stock') }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('stock.adjust') }}" class="btn btn-warning">
            <i class="bi bi-sliders me-1"></i> {{ __('stock.adjust') }}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-3">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="{{ __('stock.search_placeholder') }}"
                        wire:model.live="search">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="type">
                        <option value="">{{ __('stock.all_types') }}</option>
                        <option value="in">{{ __('stock.entries') }}</option>
                        <option value="out">{{ __('stock.exits') }}</option>
                        <option value="sale">{{ __('stock.sales') }}</option>
                        <option value="purchase">{{ __('stock.purchases') }}</option>
                        <option value="adjust_in">{{ __('stock.movement_adjust_in') }}</option>
                        <option value="adjust_out">{{ __('stock.movement_adjust_out') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ __('app.date') }}</th>
                        <th>{{ __('products.product') }}</th>
                        <th>{{ __('app.status') ?? 'Type' }}</th>
                        <th class="text-center">{{ __('purchases.quantity') }}</th>
                        <th class="text-center">{{ __('stock.previous') }}</th>
                        <th class="text-center">{{ __('stock.new') }}</th>
                        <th class="pe-4">{{ __('stock.reason_notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td class="ps-4 small text-muted">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="fw-bold">{{ $m->product->name }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $m->typeBadgeClass() }}">
                                    {{ $m->typeLabel() }}
                                </span>
                            </td>
                            <td
                                class="text-center fw-bold {{ in_array($m->type, ['sale', 'adjust_out', 'expired']) ? 'text-danger' : 'text-success' }}">
                                {{ in_array($m->type, ['sale', 'adjust_out', 'expired']) ? '-' : '+' }}{{ $m->quantity }}
                            </td>
                            <td class="text-center">{{ $m->quantity_before }}</td>
                            <td class="text-center fw-bold">{{ $m->quantity_after }}</td>
                            <td class="pe-4 small">{{ $m->reason }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">{{ __('stock.no_movement') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="card-footer border-top py-3">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>