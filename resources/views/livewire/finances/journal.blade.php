<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('finances.journal') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('finances.journal') }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select" wire:model.live="month">
                @foreach(range(1, 12) as $m)
                    <option value="{{ sprintf('%02d', $m) }}">{{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endforeach
            </select>
            <select class="form-select" wire:model.live="year">
                @foreach(range(now()->year - 2, now()->year) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm bg-success-subtle text-success">
                <small class="text-muted fw-bold text-uppercase">{{ __('finances.income') }}</small>
                <h3 class="fw-bold mb-0">+{{ number_format($totalSales, 2) }} {{ $currency }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm bg-danger-subtle text-danger">
                <small class="text-muted fw-bold text-uppercase">{{ __('finances.expenses_total') }}</small>
                <h5 class="mb-0 fw-bold text-success">{{ number_format($summary['income'], 2) }}
                    {{ $currency }}
                </h5>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm bg-primary-subtle text-primary">
                <small class="text-muted fw-bold text-uppercase">{{ __('finances.net_balance') }}</small>
                <h3 class="fw-bold mb-0">{{ number_format($netProfit, 2) }} {{ $currency }}</h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sales Log -->
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">{{ __('finances.income') }}</h5>
                </div>
                <div class="table-responsive" style="max-height: 500px">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('finances.date') }}</th>
                                <th>{{ __('purchases.purchase_number') }}</th>
                                <th class="text-end">{{ __('finances.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                <tr>
                                    <td>{{ $sale->created_at->format('d/m H:i') }}</td>
                                    <td><span class="fw-bold">{{ $sale->sale_number }}</span></td>
                                    <td class="text-end text-success fw-bold">{{ number_format($sale->final_amount, 2) }}
                                        {{ $currency }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">{{ __('app.no_results') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Expenses Log -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">{{ __('finances.expenses') }}</h5>
                </div>
                <div class="table-responsive" style="max-height: 500px">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('finances.date') }}</th>
                                <th>{{ __('finances.description') }}</th>
                                <th class="text-end">{{ __('finances.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $exp)
                                <tr>
                                    <td>{{ $exp->expense_date->format('d/m') }}</td>
                                    <td><small
                                            class="d-block fw-bold">{{ $exp->category }}</small><small>{{ $exp->description }}</small>
                                    </td>
                                    <td class="text-end text-danger fw-bold">{{ number_format($exp->amount, 2) }}
                                        {{ $currency }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">{{ __('app.no_results') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>