<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Rapport mensuel</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Rapports</li>
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

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm text-center">
                <h5 class="fw-bold mb-0 text-primary">{{ number_format($totalSales, 0) }} {{ $currency }}</h5>
                <small class="text-muted">Chiffre d'Affaires</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm text-center">
                <h5 class="fw-bold mb-0 text-success">{{ $salesCount }}</h5>
                <small class="text-muted">Ventes réalisées</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm text-center">
                <h5 class="fw-bold mb-0 text-danger">{{ number_format($totalExpenses, 0) }} {{ $currency }}
                </h5>
                <small class="text-muted">Total Dépenses</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm text-center">
                <h5 class="fw-bold mb-0 text-info">{{ number_format($netProfit, 0) }} {{ $currency }}</h5>
                <small class="text-muted">Bénéfice Estimé</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Top 10 Produits par CA</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Produit</th>
                                <th class="text-center">Qté Totale</th>
                                <th class="text-end pe-3">Revenu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $item)
                                <tr>
                                    <td class="ps-3 fw-bold">{{ $item->product->name }}</td>
                                    <td class="text-center">{{ $item->total_qty }}</td>
                                    <td class="text-end pe-3 fw-bold text-primary">
                                        {{ number_format($item->total_revenue, 2) }} {{ $currency }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">Pas de données ce mois</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
                <div class="card-body text-center py-5">
                    <div class="display-3 mb-2"><i class="bi bi-award"></i></div>
                    <h5>Objectif Mensuel</h5>
                    <p class="small opacity-75">Continuez ainsi pour atteindre vos objectifs de vente ce mois-ci !</p>
                </div>
            </div>
        </div>
    </div>
</div>