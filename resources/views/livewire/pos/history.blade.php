<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('app.sales_history') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('app.sales_history') }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-start-0" placeholder="{{ __('app.search') }}..."
                    wire:model.live.debounce.300ms="search">
            </div>
            <a href="{{ route('pos.sale') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i> <span class="d-none d-md-inline">{{ __('pos.quick_sale') }}</span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ __('app.date') }}</th>
                        <th>{{ __('pos.sale_number') }}</th>
                        <th>{{ __('app.customers') }}</th>
                        <th class="text-end">{{ __('app.total') }}</th>
                        <th class="text-center">{{ __('app.status') }}</th>
                        <th class="text-end pe-4">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold small">{{ $sale->created_at->format('d/m/Y') }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $sale->created_at->format('H:i') }}
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $sale->sale_number }}</span></td>
                            <td>
                                @if($sale->customer)
                                    <div class="fw-bold small">{{ $sale->customer->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $sale->customer->phone }}</div>
                                @else
                                    <span class="text-muted small"><em>Passant</em></span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">
                                {{ number_format($sale->final_amount, 2) }} {{ $currency }}
                            </td>
                            <td class="text-center">
                                @if($sale->status === 'completed')
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i>{{ __('app.success') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded-pill">
                                        {{ $sale->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('pos.receipt', $sale->id) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary" title="{{ __('app.print') }}">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                                {{ __('app.no_results') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>