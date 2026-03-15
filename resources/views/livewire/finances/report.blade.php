<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Rapport Financier</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Rapport</li>
                </ol>
            </nav>
        </div>
        <div class="row g-2">
            <div class="col-auto"><input type="date" class="form-control" wire:model.live="startDate"></div>
            <div class="col-auto"><input type="date" class="form-control" wire:model.live="endDate"></div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card h-100 p-4 border-0 shadow-sm">
                <small class="text-uppercase fw-bold text-muted">Ventes Totales</small>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($totalSales, 2) }} {{ $currency }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 p-4 border-0 shadow-sm">
                <small class="text-uppercase fw-bold text-muted">Achats Réalisés</small>
                <h3 class="fw-bold mb-0 text-danger">{{ number_format($totalPurchases, 2) }} {{ $currency }}
                </h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 p-4 border-0 shadow-sm">
                <small class="text-uppercase fw-bold text-muted">Dépenses Exploitation</small>
                <h3 class="fw-bold mb-0 text-warning">{{ number_format($totalExpenses, 2) }} {{ $currency }}
                </h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 p-4 border-0 shadow-sm bg-primary text-white">
                <small class="text-uppercase fw-bold opacity-75">Flux de Trésorerie Net</small>
                <h3 class="fw-bold mb-0">{{ number_format($netCashFlow, 2) }} {{ $currency }}</h3>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-3">
            <h5 class="mb-0">Détails des opérations</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small">Ce rapport consolidé montre la santé financière globale de l'entreprise sur la
                période sélectionnée.</p>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> Les achats correspondent aux montants <strong>effectivement
                    payés</strong> aux fournisseurs.
            </div>
        </div>
    </div>
</div>