<div class="container-fluid pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('stock.inventory_list') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('stock.inventory') }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('stock.inventory.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> {{ __('stock.new_inventory') }}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">{{ __('stock.inventory_date') }}</th>
                            <th>{{ __('app.user') }}</th>
                            <th>{{ __('stock.inventory_status') }}</th>
                            <th>{{ __('stock.inventory_notes') }}</th>
                            <th class="text-end pe-3">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventories as $inventory)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold">{{ $inventory->date->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $inventory->created_at->format('H:i') }}</small>
                                </td>
                                <td>{{ $inventory->user->name }}</td>
                                <td>
                                    @if($inventory->status === 'completed')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            {{ __('app.completed') ?? 'Complété' }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ $inventory->status }}
                                        </span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ Str::limit($inventory->notes, 50) }}</small></td>
                                <td class="text-end pe-3">
                                    {{-- Actions if needed --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard2-x fs-1 mb-3 d-block"></i>
                                    {{ __('app.no_results') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($inventories->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $inventories->links() }}
            </div>
        @endif
    </div>
</div>