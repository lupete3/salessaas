<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">{{ __('finances.expenses') }}</h4>
        <button class="btn btn-primary" wire:click="openModal()">
            <i class="bi bi-plus-lg me-1"></i> {{ __('finances.add_expense') }}
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ __('finances.date') }}</th>
                        <th>{{ __('finances.category') }}</th>
                        <th>{{ __('finances.description') }}</th>
                        <th class="text-end">{{ __('finances.amount') }}</th>
                        <th>{{ __('pos.payment_method') }}</th>
                        <th class="text-end pe-4">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $exp)
                        <tr>
                            <td class="ps-4">{{ $exp->expense_date->format('d/m/Y') }}</td>
                            <td><span class="badge bg-info-subtle text-info">{{ $exp->category }}</span></td>
                            <td>{{ $exp->description }}</td>
                            <td class="text-end fw-bold text-danger">- {{ number_format($exp->amount, 2) }}
                                {{ $currency }}
                            </td>
                            <td>{{ $exp->payment_method }}</td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-icon" wire:click="openModal({{ $exp->id }})"><i
                                        class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-icon text-danger" wire:click="delete({{ $exp->id }})"
                                    wire:confirm="{{ __('finances.delete_confirm') }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">{{ __('app.no_results') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade @if($showModal) show @endif" style="display: @if($showModal) block @else none @endif;"
        tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        {{ $editingId ? __('app.edit') : __('app.add') }} {{ __('finances.expenses') }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">{{ __('finances.category') }}</label>
                                <select class="form-select" wire:model="category">
                                    <option value="">{{ __('app.choose') ?? 'Choisir' }}...</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('finances.description') }}</label>
                                <input type="text" class="form-control" wire:model="description"
                                    placeholder="ex: Loyer, Electricité...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('finances.amount') }} ({{ $currency }})</label>
                                <input type="number" step="0.01" class="form-control" wire:model="amount">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('finances.date') }}</label>
                                <input type="date" class="form-control" wire:model="expense_date">
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('pos.payment_method') }}</label>
                                <select class="form-select" wire:model="payment_method">
                                    <option value="cash">{{ __('finances.payment_methods.cash') }}</option>
                                    <option value="mobile_money">{{ __('finances.payment_methods.mobile_money') }}
                                    </option>
                                    <option value="bank">{{ __('finances.payment_methods.bank') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-outline-secondary"
                                wire:click="$set('showModal', false)">{{ __('app.cancel') }}</button>
                            <button type="submit" class="btn btn-primary px-4">{{ __('app.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @if($showModal)
    <div class="modal-backdrop fade show"></div> @endif
</div>