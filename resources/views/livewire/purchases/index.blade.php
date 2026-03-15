<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('purchases.title') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('purchases.title') }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <i class="bi bi-bag-plus me-1"></i> {{ __('purchases.add') }}
        </a>
    </div>

    <!-- Debt Summary -->
    <div class="card mb-4 border-0 shadow-sm bg-danger-subtle text-danger">
        <div class="card-body d-flex align-items-center">
            <div class="avatar bg-danger text-white p-2 rounded me-3">
                <i class="bi bi-exclamation-octagon fs-3"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">{{ __('purchases.total_debt') }}: {{ number_format($totalDebt, 2) }}
                    {{ $currency }}
                </h5>
                <small>{{ __('purchases.debt_desc') }}</small>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ __('purchases.date') }}</th>
                        <th>{{ __('purchases.purchase_number') }}</th>
                        <th>{{ __('purchases.supplier') }}</th>
                        <th class="text-end">{{ __('purchases.total_amount') }}</th>
                        <th class="text-end text-success">{{ __('app.paid') ?? 'Payé' }}</th>
                        <th class="text-end text-danger">{{ __('app.balance') ?? 'Reste' }}</th>
                        <th class="text-center">{{ __('purchases.status') }}</th>
                        <th class="text-end pe-4">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                        <tr>
                            <td class="ps-4">{{ $purchase->created_at->format('d/m/Y') }}</td>
                            <td class="fw-bold">{{ $purchase->purchase_number }}</td>
                            <td>{{ $purchase->supplier->name }}</td>
                            <td class="text-end fw-bold">{{ number_format($purchase->total_amount, 2) }}
                                {{ $currency }}
                            </td>
                            <td class="text-end text-success">{{ number_format($purchase->amount_paid, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($purchase->balance_due, 2) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $purchase->statusBadgeClass() }}">
                                    {{ __('app.' . $purchase->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-sm btn-icon"><i
                                        class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">{{ __('app.no_results') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>