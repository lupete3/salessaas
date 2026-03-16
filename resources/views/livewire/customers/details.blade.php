<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ $customer->name }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">{{ __('customers.title') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('app.customer_details_title') }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('app.back') }}
        </a>
    </div>

    <!-- Info Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                            <i class="bi bi-person-badge-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">{{ __('app.customer_info') }}</h5>
                            <span class="text-muted small">ID: #{{ str_pad($customer->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-0 ms-1">
                        <li class="mb-2"><i class="bi bi-telephone text-muted me-2"></i> {{ $customer->phone ?? 'Non spécifié' }}</li>
                        <li class="mb-2"><i class="bi bi-envelope text-muted me-2"></i> {{ $customer->email ?? 'Non spécifié' }}</li>
                        <li class="mb-2"><i class="bi bi-geo-alt text-muted me-2"></i> {{ $customer->address ?? 'Non spécifié' }}</li>
                        <li><i class="bi bi-calendar3 text-muted me-2"></i> {{ __('customers.created_at') ?? 'Ajouté le' }} {{ $customer->created_at->format('d/m/Y') }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle">
                            <i class="bi bi-bank fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ __('app.financial_situation') }}</h5>
                    </div>
                    <div class="mt-4 text-center">
                        <h6 class="text-muted mb-1">{{ __('app.total_debt_current') }}</h6>
                        <h2 class="fw-bold {{ $customer->total_debt > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($customer->total_debt, 2, ',', ' ') }} {{ $currency }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom bg-white p-0">
            <ul class="nav nav-tabs nav-justified px-0 m-0 border-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold border-0 border-bottom border-3 {{ $tab === 'sales' ? 'active text-primary border-primary' : 'text-muted border-transparent' }}" 
                        wire:click="setTab('sales')">
                        <i class="bi bi-cart3 me-2"></i> {{ __('app.sales_history') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold border-0 border-bottom border-3 {{ $tab === 'payments' ? 'active text-primary border-primary' : 'text-muted border-transparent' }}" 
                        wire:click="setTab('payments')">
                        <i class="bi bi-cash-stack me-2"></i> {{ __('app.payment_history') }}
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
            @if($tab === 'sales')
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('app.sale_number') }}</th>
                                <th>{{ __('app.date') }}</th>
                                <th class="text-right">{{ __('app.amount') }}</th>
                                <th class="text-center">{{ __('app.payment') }}</th>
                                <th>{{ __('app.seller') }}</th>
                                <th class="pe-4 text-end">{{ __('app.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                <tr>
                                    <td class="ps-4 fw-bold">#{{ $sale->sale_number }}</td>
                                    <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-right fw-bold">{{ number_format($sale->final_amount, 2, ',', ' ') }}</td>
                                    <td class="text-center">
                                        @if($sale->payment_method === 'cash')
                                            <span class="badge bg-success-subtle text-success">Espèces</span>
                                        @elseif($sale->payment_method === 'credit')
                                            <span class="badge bg-danger-subtle text-danger">Crédit</span>
                                        @elseif($sale->payment_method === 'mobile_money')
                                            <span class="badge bg-primary-subtle text-primary">Mobile</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($sale->payment_method) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $sale->user?->name }}</td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('pos.receipt', $sale) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">{{ __('app.no_sales_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($sales->hasPages())
                    <div class="card-footer border-top py-3">
                        {{ $sales->links() }}
                    </div>
                @endif
            @elseif($tab === 'payments')
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('app.date') }}</th>
                                <th class="text-right">{{ __('app.amount_paid') }}</th>
                                <th>{{ __('app.payment_method') }}</th>
                                <th>{{ __('app.reference') }}</th>
                                <th class="pe-4">{{ __('app.agent') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td class="ps-4">{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-right fw-bold text-success">+{{ number_format($payment->amount, 2, ',', ' ') }}</td>
                                    <td>{{ ucfirst($payment->payment_method) }}</td>
                                    <td>{{ $payment->reference ?? '-' }}</td>
                                    <td class="pe-4">{{ $payment->user?->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">{{ __('app.no_payments_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($payments->hasPages())
                    <div class="card-footer border-top py-3">
                        {{ $payments->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
