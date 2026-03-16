<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ $product->name }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">{{ __('app.products') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ __('app.product_details_title') }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('app.back') }}
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-muted fw-bold mb-3">{{ __('app.general_info') }}</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><strong>{{ __('products.category') }} :</strong> {{ $product->category }}</li>
                        <li class="mb-2"><strong>{{ __('products.supplier') }} :</strong>
                            {{ $product->supplier?->name ?? '-' }}</li>
                        <li class="mb-2"><strong>{{ __('products.unit') ?? 'Unité' }} :</strong>
                            {{ $product->unit ?? '-' }}</li>
                        @if(auth()->user()->canViewFinancials())
                            <li class="mb-2">
                                <strong>{{ __('products.purchase_price') ?? 'Prix d\'Achat' }} :</strong>
                                {{ number_format($product->purchase_price, 2, ',', ' ') }} {{ $currency }}
                            </li>
                        @endif
                        <li>
                            <strong>{{ __('products.selling_price') ?? 'Prix de Vente' }} :</strong>
                            {{ number_format($product->selling_price, 2, ',', ' ') }} {{ $currency }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-muted fw-bold mb-3">{{ __('app.stock_state') }}</h6>
                    <div class="d-flex align-items-center mb-3">
                        <div
                            class="fs-1 fw-bold me-3 {{ $product->stock_quantity <= $product->alert_quantity ? 'text-danger' : 'text-success' }}">
                            {{ $product->stock_quantity }}
                        </div>
                        <div class="text-muted small">
                            {{ __('app.current_quantity') }} <br>
                            ({{ __('products.alert') ?? "Seuil d'alerte" }}: {{ $product->alert_quantity }})
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-3">
            <h5 class="mb-0 fw-bold">{{ __('app.movement_history') }}</h5>
        </div>
        <div class="card-body border-bottom py-3">
            <div class="row g-2">
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
                <div class="col-md-3">
                    <input type="date" class="form-control" wire:model.live="dateFrom" placeholder="Date de début">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" wire:model.live="dateTo" placeholder="Date de fin">
                </div>
                <div class="col-md-3 d-flex align-items-center">
                    <button class="btn btn-outline-secondary w-100"
                        wire:click="$set('type', ''); $set('dateFrom', ''); $set('dateTo', '');">
                        Effacer Filtres
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ __('app.date') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-center">{{ __('purchases.quantity') }}</th>
                        <th class="text-center">{{ __('stock.previous') }}</th>
                        <th class="text-center">{{ __('stock.new') }}</th>
                        <th class="pe-4">{{ __('stock.reason_notes') }}</th>
                        <th>{{ __('app.agent') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td class="ps-4 small text-muted">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $m->typeBadgeClass() }}">
                                    {{ $m->typeLabel() }}
                                </span>
                            </td>
                            <td
                                class="text-center fw-bold {{ $m->quantity_after < $m->quantity_before ? 'text-danger' : 'text-success' }}">
                                {{ $m->quantity_after < $m->quantity_before ? '-' : '+' }}{{ $m->quantity }}
                            </td>
                            <td class="text-center">{{ $m->quantity_before }}</td>
                            <td class="text-center fw-bold">{{ $m->quantity_after }}</td>
                            <td class="pe-4 small">{{ $m->reason ?? '-' }}</td>
                            <td>{{ $m->user?->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                {{ __('app.no_movements_found') }}
                            </td>
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