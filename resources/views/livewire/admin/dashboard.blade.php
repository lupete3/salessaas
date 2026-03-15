<div class="container-fluid py-4">
    <div class="mb-4">
        <h4 class="fw-bold mb-0">Tableau de Bord Master</h4>
        <p class="text-muted small">Vue d'ensemble de la plateforme SalesSaaS</p>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                        <h6 class="card-subtitle text-muted mb-0">Total Entreprises</h6>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $totalStores }}</h3>
                    <div class="text-success small mt-2">
                        <i class="bi bi-check-circle me-1"></i> {{ $activeStores }} Actives
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-info bg-opacity-10 text-info rounded p-2 me-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <h6 class="card-subtitle text-muted mb-0">Utilisateurs</h6>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $totalUsers }}</h3>
                    <p class="text-muted small mt-2 mb-0">Comptes enregistrés</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-warning bg-opacity-10 text-warning rounded p-2 me-3">
                            <i class="bi bi-cart-check fs-4"></i>
                        </div>
                        <h6 class="card-subtitle text-muted mb-0">Ventes Globales</h6>
                    </div>
                    <h3 class="fw-bold mb-0">{{ number_format($totalSalesCount) }}</h3>
                    <p class="text-muted small mt-2 mb-0">Tous magasins</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success bg-opacity-10 text-success rounded p-2 me-3">
                            <i class="bi bi-currency-dollar fs-4"></i>
                        </div>
                        <h6 class="card-subtitle text-muted mb-0">Volume Global</h6>
                    </div>
                    <h3 class="fw-bold mb-0">{{ number_format($totalSalesAmount, 2) }} <small
                            class="fs-6 fw-normal">{{ $currency }}</small></h3>
                    <p class="text-muted small mt-2 mb-0">Chiffre d'affaires total</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Tenants -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">Nouveaux Tenants</h6>
                        <a href="{{ route('admin.stores') }}" class="btn btn-sm btn-link">Voir tout</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 border-0">Entreprise</th>
                                <th class="border-0">Lieu</th>
                                <th class="border-0 text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentStores as $store)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold">{{ $store->name }}</div>
                                        <div class="text-muted small">{{ $store->email }}</div>
                                    </td>
                                    <td>{{ $store->address ?? 'N/A' }}</td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('admin.stores') }}"
                                            class="btn btn-sm btn-outline-primary py-0 px-2">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Expirations & Alerts -->
        <div class="col-lg-4">
            <!-- Expirations -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">Alertes Abonnements</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($expiringSoon as $ph)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold small">{{ $ph->name }}</div>
                                    <span class="badge bg-warning rounded-pill">Sous 30 j</span>
                                </div>
                                <div class="text-muted x-small">Expire le {{ $ph->subscription_ends_at->format('d/m/Y') }}
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-info-circle fs-4 d-block mb-1"></i>
                                Aucune expiration proche.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Stats Brutes -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">Répartition des Licences</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Licences Actives</span>
                        <span class="fw-bold">{{ $activeStores }}</span>
                    </div>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar"
                            style="width: {{ $totalStores > 0 ? ($activeStores / $totalStores) * 100 : 0 }}%">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Périodes d'Essai</span>
                        <span class="fw-bold text-info">{{ $trialStores }}</span>
                    </div>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar"
                            style="width: {{ $totalStores > 0 ? ($trialStores / $totalStores) * 100 : 0 }}%">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Expirées / Inactives</span>
                        <span class="fw-bold text-danger">{{ $expiredStores }}</span>
                    </div>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar bg-danger" role="progressbar"
                            style="width: {{ $totalStores > 0 ? ($expiredStores / $totalStores) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .x-small {
            font-size: 0.75rem;
        }
    </style>
</div>