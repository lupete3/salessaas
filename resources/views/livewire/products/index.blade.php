<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('app.products') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('app.products') }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> {{ __('products.add_new') }}
        </a>
    </div>

    <!-- Stats Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="badge bg-primary-subtle text-primary p-2 me-3"><i class="bi bi-box-seam fs-4"></i></div>
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $stats['total'] }}</h5>
                        <small class="text-muted">{{ __('products.total_products') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm clickable" wire:click="$set('filterStatus', 'low_stock')"
                style="cursor:pointer">
                <div class="d-flex align-items-center">
                    <div class="badge bg-warning-subtle text-warning p-2 me-3"><i
                            class="bi bi-exclamation-triangle fs-4"></i></div>
                    <div>
                        <h5 class="mb-0 fw-bold text-danger">{{ $stats['low_stock'] }}</h5>
                        <small class="text-muted">{{ __('products.low_stock_short') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm clickable" wire:click="$set('filterStatus', 'out_of_stock')"
                style="cursor:pointer">
                <div class="d-flex align-items-center">
                    <div class="badge bg-danger-subtle text-danger p-2 me-3"><i class="bi bi-x-circle fs-4"></i></div>
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $stats['out_of_stock'] }}</h5>
                        <small class="text-muted">{{ __('products.out_of_stock_short') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm clickable" wire:click="$set('filterStatus', 'expiring')"
                style="cursor:pointer">
                <div class="d-flex align-items-center">
                    <div class="badge bg-info-subtle text-info p-2 me-3"><i class="bi bi-clock-history fs-4"></i></div>
                    <div>
                        <h5 class="mb-0 fw-bold text-warning">{{ $stats['expiring'] }}</h5>
                        <small class="text-muted">{{ __('products.expiring_soon_short') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="search"
                            placeholder="{{ __('products.search_placeholder') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterCategory">
                        <option value="">{{ __('products.all_categories') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">{{ __('products.all_states') }}</option>
                        <option value="low_stock">{{ __('products.low_stock') }}</option>
                        <option value="out_of_stock">{{ __('products.out_of_stock') }}</option>
                        <option value="expiring">{{ __('products.expires_soon') }}</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-secondary w-100"
                        wire:click="resetFilters">{{ __('app.cancel') }}</button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 cursor-pointer" wire:click="sort('name')">
                            {{ __('products.product') }} @if($sortBy === 'name') <i
                            class="bi bi-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }}"></i> @endif
                        </th>
                        <th>{{ __('products.category') }}</th>
                        <th class="text-center" wire:click="sort('stock_quantity')">
                            {{ __('products.stock_quantity') }} @if($sortBy === 'stock_quantity') <i
                            class="bi bi-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }}"></i> @endif
                        </th>
                        <th>{{ __('products.selling_price_short') }}</th>
                        <th>{{ __('products.supplier') }}</th>
                        <th class="text-center">{{ __('app.status') }}</th>
                        <th class="text-end pe-4">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-primary">{{ $product->name }}</div>
                                <small class="text-muted">{{ $product->generic_name }} · {{ $product->form }}
                                    ({{ $product->dosage }})</small>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $product->category }}</span></td>
                            <td class="text-center">
                                <div class="fw-bold">{{ $product->stock_quantity }} {{ $product->unit }}</div>
                                @if($product->isLowStock())
                                    <small class="text-warning" style="font-size: .7rem">{{ __('products.alert') }}:
                                        {{ $product->min_stock_alert }}</small>
                                @endif
                            </td>
                            <td>
                                {{ number_format($product->selling_price, 2) }} {{ $currency }}
                            </td>
                            <td><small>{{ $product->supplier?->name ?? '—' }}</small></td>
                            <td class="text-center">
                                @if($product->isOutOfStock())
                                    <span class="badge bg-danger">{{ __('products.out_of_stock_short') }}</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning text-dark">{{ __('products.low_stock') }}</span>
                                @else
                                    <span class="badge bg-success">{{ __('products.in_stock') }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon p-0" data-bs-toggle="dropdown"><i
                                            class="bi bi-three-dots-vertical"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if(auth()->user()->canEdit())
                                            <li><a class="dropdown-item" href="{{ route('products.edit', $product->id) }}"><i
                                                        class="bi bi-pencil me-2"></i>{{ __('app.edit') }}</a></li>
                                        @endif
                                        <li><a class="dropdown-item"
                                                href="{{ route('stock.products.details', $product->id) }}"><i
                                                    class="bi bi-eye me-2"></i>{{ __('Détails & Mouvements') }}</a>
                                        </li>
                                        @if(auth()->user()->canDelete())
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li><button type="button" class="dropdown-item text-danger"
                                                    wire:click="confirmDelete({{ $product->id }})"><i
                                                        class="bi bi-trash me-2"></i>{{ __('app.delete') }}</button></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-box-seam display-1 text-light mb-3"></i>
                                <p class="text-muted">{{ __('products.no_product_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="card-footer border-top py-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Delete Modal -->
    <div class="modal fade @if($showDeleteModal) show @endif"
        style="display: @if($showDeleteModal) block @else none @endif;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('app.confirm_delete') }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('products.deactivate_confirm') }}</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary"
                        wire:click="$set('showDeleteModal', false)">{{ __('app.cancel') }}</button>
                    <button type="button" class="btn btn-danger"
                        wire:click="delete">{{ __('products.deactivate') }}</button>
                </div>
            </div>
        </div>
    </div>
    @if($showDeleteModal)
    <div class="modal-backdrop fade show"></div> @endif
</div>