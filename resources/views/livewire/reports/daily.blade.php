<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Rapport journalier</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Rapports</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <input type="date" class="form-control" wire:model.live="date">
            <button class="btn btn-outline-danger" wire:click="exportPdf">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
            </button>
        </div>
    </div>

    <!-- Summary Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100 p-4 border-0 shadow-sm text-center">
                <i class="bi bi-cart-check fs-1 text-success mb-2"></i>
                <h3 class="fw-bold mb-0">{{ number_format($totalSales, 2) }} {{ $currency }}</h3>
                <small class="text-muted">Chiffre d'Affaires</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 p-4 border-0 shadow-sm text-center">
                <i class="bi bi-wallet2 fs-1 text-danger mb-2"></i>
                <h3 class="fw-bold mb-0">{{ number_format($totalExpenses, 2) }} {{ $currency }}</h3>
                <small class="text-muted">Dépenses du jour</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 p-4 border-0 shadow-sm text-center">
                <i class="bi bi-cash-stack fs-1 text-primary mb-2"></i>
                <h3 class="fw-bold mb-0">{{ number_format($netProfit, 2) }} {{ $currency }}</h3>
                <small class="text-muted">Résultat Net</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Top Products -->
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom py-3">
                    <h5 class="mb-0">Top 5 Produits vendus</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Article</th>
                                <th class="text-center">Quantité vendue</th>
                                <th class="text-end pe-4">Revenu généré</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $item)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $item->product->name }}</td>
                                    <td class="text-center">{{ $item->total_qty }}</td>
                                    <td class="text-end pe-4 fw-bold">{{ number_format($item->total_revenue, 2) }}
                                        {{ $currency }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">Aucune vente enregistrée</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('download-pdf', (event) => {
            const base64 = event.detail;
            const binaryString = atob(base64);
            const bytes = new Uint8Array(binaryString.length);
            for (let i = 0; i < binaryString.length; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            const blob = new Blob([bytes], { type: 'application/pdf' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'rapport_journalier.pdf';
            link.click();
        });
    </script>
@endpush