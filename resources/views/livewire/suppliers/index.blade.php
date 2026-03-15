<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('suppliers.title') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('suppliers.title') }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> {{ __('suppliers.add') }}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="{{ __('app.search') }}..."
                            wire:model.live="search">
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ __('suppliers.name') }}</th>
                        <th>{{ __('suppliers.contact_name') }}</th>
                        <th>{{ __('suppliers.phone') }}</th>
                        <th class="text-end">{{ __('suppliers.total_purchases') }}</th>
                        <th class="text-end pe-4">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $supplier->name }}</div>
                                <small class="text-muted">{{ $supplier->address }}</small>
                            </td>
                            <td>{{ $supplier->contact_person }}</td>
                            <td>{{ $supplier->phone }}</td>
                            <td class="text-end fw-bold text-danger">
                                {{ number_format($supplier->purchases_sum_balance_due, 2) }} {{ $currency }}
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-icon"><i
                                        class="bi bi-pencil"></i></a>
                                <button wire:click="confirmDelete({{ $supplier->id }})"
                                    class="btn btn-sm btn-icon text-danger"><i class="bi bi-trash"></i></button>
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
    </div>
</div>