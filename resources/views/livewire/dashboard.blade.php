<div>
    <div class="row g-4 mb-4">
        <!-- Sales Today -->
        <div class="col-md-3">
            <div class="stat-card bg-primary">
                <i class="bi bi-cash-coin stat-icon"></i>
                <p>{{ __('pos.sales_today') }}</p>
                <h3>{{ number_format($todaySales, 2) }} {{ $currency }}</h3>
                <small>{{ $todaySalesCount }} {{ strtolower(__('pos.sales')) }}</small>
            </div>
        </div>
        <!-- Low Stock -->
        <div class="col-md-3">
            <div class="stat-card" style="background: #ffab00;">
                <i class="bi bi-exclamation-triangle stat-icon"></i>
                <p>{{ __('app.stock_alerts') }}</p>
                <h3>{{ $lowStockCount + $outOfStockCount }}</h3>
                <small>
                    <span class="badge bg-body-tertiary text-warning">{{ $lowStockCount }}
                        {{ __('app.low_count') }}</span>
                    <span class="badge bg-danger text-white">{{ $outOfStockCount }} {{ __('app.out_count') }}</span>
                </small>
            </div>
        </div>
        <!-- Expiring Soon -->
        <div class="col-md-3">
            <div class="stat-card" style="background: #ff3e1d;">
                <i class="bi bi-clock-history stat-icon"></i>
                <p>{{ __('products.expiring_soon') }}</p>
                <h3>{{ $expiringSoonCount }}</h3>
                <small>{{ __('app.next_30_days') }}</small>
            </div>
        </div>
        <!-- Monthly Profit -->
        <div class="col-md-3">
            <div class="stat-card" style="background: #71dd37;">
                <i class="bi bi-graph-up-arrow stat-icon"></i>
                <p>{{ __('app.monthly_profit') }}</p>
                <h3>{{ number_format($estimatedProfit, 2) }} {{ $currency }}</h3>
                <small>CA: {{ number_format($monthSales, 0) }} | Exp: {{ number_format($monthExpenses, 0) }}</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Chart -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">{{ __('app.sales_overview') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Alerts & Actions -->
        <div class="col-lg-4">
            <!-- Low stock table -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">{{ __('app.critical_stock') }}</h5>
                    <a href="{{ route('products.index') }}" class="btn btn-xs btn-outline-primary py-0 px-2"
                        style="font-size: .75rem">{{ __('app.view_all') }}</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <tbody>
                                @forelse($lowStockProducts as $med)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold" style="font-size: .85rem">{{ $med->name }}</div>
                                            <small class="text-muted">{{ $med->category }}</small>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span @class(['badge rounded-pill', 'bg-danger-subtle text-danger' => $med->stock_quantity == 0, 'bg-warning-subtle text-warning' => $med->stock_quantity > 0])
                                                style="font-size: .75rem; padding: .25rem .5rem;">
                                                {{ $med->stock_quantity }} {{ $med->unit }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted small">
                                            {{ __('app.no_low_stock') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Alerts -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title m-0">{{ __('app.recent_alerts') }}</h5>
                </div>
                <div class="card-body py-2">
                    <ul class="list-unstyled mb-0">
                        @forelse($unreadAlerts as $alert)
                            <li class="d-flex mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <span
                                        class="badge p-2 {{ $alert->severity == 'danger' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning' }}">
                                        <i
                                            class="bi {{ $alert->type == 'expiry' ? 'bi-clock' : 'bi-exclamation-triangle' }}"></i>
                                    </span>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0" style="font-size: .85rem">{{ $alert->title }}</h6>
                                        <small class="text-muted" style="font-size: .75rem">{{ $alert->message }}</small>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center py-3 text-muted small">{{ __('app.no_recent_alerts') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:navigated', () => {
            const ctx = document.getElementById('salesChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($salesChart->pluck('label')) !!},
                    datasets: [{
                        label: '{{ __('app.sales_label') }}',
                        data: {!! json_encode($salesChart->pluck('value')) !!},
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#198754'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [5, 5] },
                            ticks: { font: { size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            });
        });
    </script>
@endpush