<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('customers.debt_followup') ?? 'Suivi des Dettes' }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">{{ __('customers.title') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ __('customers.debts') ?? 'Dettes' }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header border-bottom py-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label
                        class="form-label small fw-bold">{{ __('customers.search_placeholder') ?? 'Rechercher un client' }}</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0"
                            placeholder="{{ __('customers.search_customer_name') }}" wire:model.live="search">
                    </div>
                </div>
                <div class="col-md-4">
                    <label
                        class="form-label small fw-bold">{{ __('customers.filter_by_customer') ?? 'Filtrer par client' }}</label>
                    <select class="form-select" wire:model.live="customerIdFilter">
                        <option value="">{{ __('customers.all_with_debts') ?? 'Tous les clients avec dettes' }}</option>
                        @foreach($customers as $c)
                            @if($c->total_debt > 0)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ number_format($c->total_debt, 2) }}
                                    {{ $currency }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="row g-3 p-3">
            <div class="col-md-4">
                <div class="card p-3 border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-primary-subtle text-primary p-2 me-3"><i
                                class="bi bi-currency-dollar fs-4"></i></div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ number_format($customers->sum('total_debt'), 2) }}
                                {{ $currency }}
                            </h5>
                            <small class="text-muted">{{ __('app.total') }} {{ __('customers.debts') }}</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-success-subtle text-success p-2 me-3"><i
                                class="bi bi-check-circle fs-4"></i></div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $customers->where('total_debt', 0)->count() }}</h5>
                            <small class="text-muted">{{ __('app.paid') }}</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-danger-subtle text-danger p-2 me-3"><i
                                class="bi bi-exclamation-circle fs-4"></i></div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $customers->where('total_debt', '>', 0)->count() }}</h5>
                            <small class="text-muted">{{ __('app.balance') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ __('pos.sale_number') ?? 'Vente #' }}</th>
                        <th>{{ __('customers.customer_single') ?? 'Client' }}</th>
                        <th>{{ __('finances.date') }}</th>
                        <th class="text-end">{{ __('finances.amount') }}</th>
                        <th class="text-end">{{ __('app.paid') }}</th>
                        <th class="text-end text-danger">{{ __('app.balance') }}</th>
                        <th class="text-end pe-4">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dettes as $sale)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $sale->sale_number }}</td>
                            <td>
                                <div class="fw-bold text-primary">{{ $sale->customer->name }}</div>
                            </td>
                            <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end fw-bold">{{ number_format($sale->final_amount, 2) }}</td>
                            <td class="text-end text-success">{{ number_format($sale->amount_paid, 2) }}</td>
                            <td class="text-end fw-bold text-danger">
                                {{ number_format($sale->final_amount - $sale->amount_paid, 2) }} {{ $currency }}
                            </td>
                            <td class="text-end pe-4">
                                <button wire:click="openPaymentModal({{ $sale->id }})" class="btn btn-sm btn-success">
                                    <i class="bi bi-cash-stack me-1"></i>
                                    {{ __('customers.record_payment') ?? 'Encaisser' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">{{ __('app.no_results') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dettes->hasPages())
            <div class="card-footer border-top py-3">
                {{ $dettes->links() }}
            </div>
        @endif
    </div>

    <!-- Payment Modal -->
    <div class="modal fade @if($showPaymentModal) show @endif"
        style="display: @if($showPaymentModal) block @else none @endif;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        {{ __('customers.record_payment_title') ?? 'Enregistrer un paiement' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showPaymentModal', false)"></button>
                </div>
                <div class="modal-body">
                    @if($selectedSale)
                        {!! __('customers.repayment_for_sale', ['sale' => $selectedSale->sale_number, 'customer' => $selectedSale->customer->name]) !!}
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('customers.payment_amount') ?? 'Montant du versement' }}
                                ({{ $currency }})</label>
                            <input type="number" step="0.01" class="form-control form-control-lg text-center fw-bold"
                                wire:model="paymentAmount"
                                max="{{ $selectedSale->final_amount - $selectedSale->amount_paid }}">
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary"
                        wire:click="$set('showPaymentModal', false)">{{ __('app.cancel') }}</button>
                    <button type="button" class="btn btn-success px-4" wire:click="recordPayment"
                        wire:loading.attr="disabled">
                        <span
                            wire:loading.remove>{{ __('customers.confirm_payment') ?? 'Confirmer le paiement' }}</span>
                        <span wire:loading><span class="spinner-border spinner-border-sm me-2"></span>...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @if($showPaymentModal)
    <div class="modal-backdrop fade show"></div> @endif
</div>