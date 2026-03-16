<div class="container-fluid py-4" x-data="{ 
    downloadPdf(base64, filename) {
        const link = document.createElement('a');
        link.href = 'data:application/pdf;base64,' + base64;
        link.download = filename;
        link.click();
    }
}"
    @download-pdf.window="downloadPdf($event.detail.base64 || $event.detail[0], $event.detail.filename || $event.detail[1] || 'report.pdf')">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="fw-bold mb-1"><i
                                class="bi bi-file-earmark-bar-graph me-2"></i>{{ __('reports.title') }}</h2>
                        <p class="mb-0 opacity-75">
                            {{ __('reports.subtitle') ?? 'Générez et téléchargez des rapports détaillés pour votre boutique.' }}
                        </p>
                    </div>
                    <div class="d-none d-md-block opacity-25">
                        <i class="bi bi-printer" style="font-size: 5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Stock Report Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-info transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-info bg-opacity-10 text-info rounded-4">
                            <i class="bi bi-box-seam fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ __('reports.stock_report') }}</h5>
                    </div>
                    <p class="text-muted small">
                        {{ auth()->user()->canViewFinancials() 
                            ? (__('reports.stock_report_desc') ?? 'Inventaire complet incluant les quantités actuelles, les prix d\'achat/vente et la valeur totale du stock.')
                            : 'Inventaire complet incluant les quantités actuelles. (Valeurs financières masquées)' }}
                    </p>
                    <div class="mt-4 pt-2">
                        <button wire:click="exportStockReport" wire:loading.attr="disabled"
                            class="btn btn-info text-white w-100 py-2 fw-bold shadow-sm rounded-3">
                            <span wire:loading.remove wire:target="exportStockReport">
                                <i class="bi bi-download me-2"></i> {{ __('reports.export_pdf') }}
                            </span>
                            <span wire:loading wire:target="exportStockReport">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ __('app.generating') ?? 'Génération...' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Report Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-success transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-success bg-opacity-10 text-success rounded-4">
                            <i class="bi bi-cart-check fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ __('reports.sales_report') }}</h5>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">{{ __('Période') }}</label>
                        <div class="input-group input-group-sm mb-2 shadow-sm">
                            <span class="input-group-text border-end-0"><i class="bi bi-calendar-event"></i></span>
                            <input type="date" wire:model.live="startDate" class="form-control border-start-0">
                        </div>
                        <div class="input-group input-group-sm shadow-sm">
                            <span class="input-group-text border-end-0"><i class="bi bi-calendar-check"></i></span>
                            <input type="date" wire:model.live="endDate" class="form-control border-start-0">
                        </div>
                    </div>

                    <div class="mt-auto">
                        <button wire:click="exportSalesReport" wire:loading.attr="disabled"
                            class="btn btn-success w-100 py-2 fw-bold shadow-sm rounded-3">
                            <span wire:loading.remove wire:target="exportSalesReport">
                                <i class="bi bi-download me-2"></i> {{ __('reports.generate') }}
                            </span>
                            <span wire:loading wire:target="exportSalesReport">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ __('app.processing') ?? 'En cours...' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->canViewFinancials())
        <!-- Purchases Report Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-warning transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-4">
                            <i class="bi bi-truck fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ __('reports.purchases_report') }}</h5>
                    </div>

                    <div class="mb-3">
                        <label
                            class="form-label small fw-bold text-muted">{{ __('reports.filter_supplier') ?? 'Filtrer par fournisseur' }}</label>
                        <select wire:model.live="supplierId" class="form-select form-select-sm shadow-sm">
                            <option value="">{{ __('reports.all_suppliers') ?? 'Tous les fournisseurs' }}</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">{{ __('Période') }}</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" wire:model.live="startDate"
                                    class="form-control form-control-sm shadow-sm">
                            </div>
                            <div class="col-6">
                                <input type="date" wire:model.live="endDate"
                                    class="form-control form-control-sm shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <button wire:click="exportPurchasesReport" wire:loading.attr="disabled"
                            class="btn btn-warning text-dark w-100 py-2 fw-bold shadow-sm rounded-3">
                            <span wire:loading.remove wire:target="exportPurchasesReport">
                                <i class="bi bi-download me-2"></i> {{ __('reports.generate') }}
                            </span>
                            <span wire:loading wire:target="exportPurchasesReport">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ __('app.processing') ?? 'En cours...' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->canViewFinancials())
        <!-- Finances Summary Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-danger transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-4">
                            <i class="bi bi-wallet2 fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ __('finances.report') ?? 'Bilan Financier' }}</h5>
                    </div>
                    <p class="text-muted small">
                        {{ __('reports.finance_report_desc') ?? 'Récapitulatif des recettes (ventes) et dépenses pour la période sélectionnée.' }}
                    </p>
                    <div class="mt-4 pt-2">
                        <p class="text-center text-muted small px-3">
                            <em>{{ __('reports.finance_filters_hint') }}</em>
                        </p>
                        <button wire:click="exportFinanceReport" wire:loading.attr="disabled"
                            class="btn btn-danger w-100 py-2 fw-bold shadow-sm rounded-3">
                            <span wire:loading.remove wire:target="exportFinanceReport">
                                <i class="bi bi-download me-2"></i>
                                {{ __('reports.export_finance') ?? 'Télécharger le bilan' }}
                            </span>
                            <span wire:loading wire:target="exportFinanceReport">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ __('app.generating') ?? 'Génération...' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- Customers Report Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-primary transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ __('reports.customers_report') }}</h5>
                    </div>
                    <p class="text-muted small">
                        {{ __('reports.customers_report_desc') }}
                    </p>
                    <div class="mt-4 pt-2">
                        <button wire:click="exportCustomersReport" wire:loading.attr="disabled"
                            class="btn btn-primary text-white w-100 py-2 fw-bold shadow-sm rounded-3">
                            <span wire:loading.remove wire:target="exportCustomersReport">
                                <i class="bi bi-download me-2"></i> {{ __('reports.export_pdf') }}
                            </span>
                            <span wire:loading wire:target="exportCustomersReport">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ __('app.generating') ?? 'Génération...' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->canViewFinancials())
        <!-- Suppliers Report Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-secondary transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-secondary bg-opacity-10 text-secondary rounded-4">
                            <i class="bi bi-truck-flatbed fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ __('reports.suppliers_report') }}</h5>
                    </div>
                    <p class="text-muted small">
                        {{ __('reports.suppliers_report_desc') }}
                    </p>
                    <div class="mt-4 pt-2">
                        <button wire:click="exportSuppliersReport" wire:loading.attr="disabled"
                            class="btn btn-secondary text-white w-100 py-2 fw-bold shadow-sm rounded-3">
                            <span wire:loading.remove wire:target="exportSuppliersReport">
                                <i class="bi bi-download me-2"></i> {{ __('reports.export_pdf') }}
                            </span>
                            <span wire:loading wire:target="exportSuppliersReport">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ __('app.generating') ?? 'Génération...' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->canViewFinancials())
        <!-- Customer Debts Report Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-danger transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-4">
                            <i class="bi bi-bank fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ __('reports.customer_debts_report') }}</h5>
                    </div>
                    <p class="text-muted small">
                        {{ __('reports.customer_debts_report_desc') }}
                    </p>
                    <div class="mt-4 pt-2">
                        <button wire:click="exportCustomerDebtsReport" wire:loading.attr="disabled"
                            class="btn btn-danger text-white w-100 py-2 fw-bold shadow-sm rounded-3">
                            <span wire:loading.remove wire:target="exportCustomerDebtsReport">
                                <i class="bi bi-download me-2"></i> {{ __('reports.export_pdf') }}
                            </span>
                            <span wire:loading wire:target="exportCustomerDebtsReport">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ __('app.generating') ?? 'Génération...' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Payments Report Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-success transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-success bg-opacity-10 text-success rounded-4">
                            <i class="bi bi-cash-stack fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ __('reports.payments_report') }}</h5>
                    </div>
                    <div class="mb-3">
                        <label
                            class="form-label small fw-bold text-muted">{{ __('reports.period') ?? 'Période' }}</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" wire:model.live="startDate"
                                    class="form-control form-control-sm shadow-sm">
                            </div>
                            <div class="col-6">
                                <input type="date" wire:model.live="endDate"
                                    class="form-control form-control-sm shadow-sm">
                            </div>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <button wire:click="exportPaymentsReport" wire:loading.attr="disabled"
                            class="btn btn-success text-white w-100 py-2 fw-bold shadow-sm rounded-3">
                            <span wire:loading.remove wire:target="exportPaymentsReport">
                                <i class="bi bi-download me-2"></i> {{ __('reports.export_pdf') }}
                            </span>
                            <span wire:loading wire:target="exportPaymentsReport">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ __('app.generating') ?? 'Génération...' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Movements Report Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-warning transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-4">
                            <i class="bi bi-boxes fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ __('reports.stock_movements_report') }}</h5>
                    </div>
                    <div class="mb-3">
                        <label
                            class="form-label small fw-bold text-muted">{{ __('reports.period') ?? 'Période' }}</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" wire:model.live="startDate"
                                    class="form-control form-control-sm shadow-sm">
                            </div>
                            <div class="col-6">
                                <input type="date" wire:model.live="endDate"
                                    class="form-control form-control-sm shadow-sm">
                            </div>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <button wire:click="exportStockMovementsReport" wire:loading.attr="disabled"
                            class="btn btn-warning text-dark w-100 py-2 fw-bold shadow-sm rounded-3">
                            <span wire:loading.remove wire:target="exportStockMovementsReport">
                                <i class="bi bi-download me-2"></i> {{ __('reports.export_pdf') }}
                            </span>
                            <span wire:loading wire:target="exportStockMovementsReport">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ __('app.generating') ?? 'Génération...' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->canViewFinancials())
        <!-- Inventories Report Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-info transition-hover">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-info bg-opacity-10 text-info rounded-4">
                            <i class="bi bi-clipboard2-check fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-0">{{ __('reports.inventories_report') }}</h5>
                    </div>
                    <p class="text-muted small">{{ __('reports.inventories_report_desc') }}</p>

                    {{-- Detailed report per inventory --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Choisir un inventaire</label>
                        <select wire:model.live="inventoryId" class="form-select form-select-sm shadow-sm">
                            <option value="">— Sélectionner —</option>
                            @foreach($inventories as $inv)
                                <option value="{{ $inv->id }}">
                                    {{ $inv->date->format('d/m/Y') }}
                                    @if($inv->status === 'completed') ✓ @endif
                                    ({{ $inv->items->count() }} art.)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button wire:click="exportInventoryDetailReport" wire:loading.attr="disabled"
                            class="btn btn-info text-white py-2 fw-bold shadow-sm rounded-3"
                            @if(!$inventoryId) disabled @endif>
                            <span wire:loading.remove wire:target="exportInventoryDetailReport">
                                <i class="bi bi-file-earmark-text me-2"></i> Rapport Détaillé
                            </span>
                            <span wire:loading wire:target="exportInventoryDetailReport">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ __('app.generating') }}
                            </span>
                        </button>

                        {{-- Global list by date range --}}
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted">{{ __('reports.period') }}</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="date" wire:model.live="startDate"
                                        class="form-control form-control-sm shadow-sm">
                                </div>
                                <div class="col-6">
                                    <input type="date" wire:model.live="endDate"
                                        class="form-control form-control-sm shadow-sm">
                                </div>
                            </div>
                        </div>
                        <button wire:click="exportInventoriesReport" wire:loading.attr="disabled"
                            class="btn btn-outline-info py-2 fw-bold shadow-sm rounded-3">
                            <span wire:loading.remove wire:target="exportInventoriesReport">
                                <i class="bi bi-download me-2"></i> Liste globale (PDF)
                            </span>
                            <span wire:loading wire:target="exportInventoriesReport">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ __('app.generating') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <style>
        .transition-hover {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .transition-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }
    </style>
</div>