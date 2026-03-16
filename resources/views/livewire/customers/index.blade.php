<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('customers.title') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ __('customers.title') }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> {{ __('customers.add') }}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0"
                            placeholder="{{ __('customers.search_placeholder') ?? 'Rechercher...' }}"
                            wire:model.live="search">
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ __('customers.name') }}</th>
                        <th>{{ __('customers.phone') }}</th>
                        <th>{{ __('customers.email') }}</th>
                        <th class="text-end">{{ __('customers.total_debt') }}</th>
                        <th class="text-end pe-4">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-primary">{{ $customer->name }}</div>
                                <small class="text-muted">{{ $customer->address }}</small>
                            </td>
                            <td>{{ $customer->phone ?? '—' }}</td>
                            <td>{{ $customer->email ?? '—' }}</td>
                            <td class="text-end fw-bold {{ $customer->total_debt > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($customer->total_debt, 2) }} {{ $currency }}
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('customers.details', $customer->id) }}"
                                    class="btn btn-sm btn-icon text-info" title="Détails"><i class="bi bi-eye"></i></a>
                                @if(auth()->user()->canEdit())
                                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-icon"><i
                                            class="bi bi-pencil"></i></a>
                                @endif
                                @if(auth()->user()->canDelete())
                                    <button wire:click="confirmDelete({{ $customer->id }})"
                                        class="btn btn-sm btn-icon text-danger"><i class="bi bi-trash"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">{{ __('app.no_results') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="card-footer border-top py-3">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    <!-- Delete Modal -->
    <div class="modal fade @if($showDeleteModal) show @endif"
        style="display: @if($showDeleteModal) block @else none @endif;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('app.confirm_delete') ?? 'Confirmer la suppression' }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('app.delete_warning') ?? 'Êtes-vous sûr de vouloir supprimer cet élément ?' }}</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary"
                        wire:click="$set('showDeleteModal', false)">{{ __('app.cancel') }}</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">{{ __('app.delete') }}</button>
                </div>
            </div>
        </div>
    </div>
    @if($showDeleteModal)
    <div class="modal-backdrop fade show"></div> @endif
</div>