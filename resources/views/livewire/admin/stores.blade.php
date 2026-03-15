<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('stores.title') }}</h4>
            <p class="text-muted small mb-0">Administrez les points de vente de la plateforme PharmaSaaS</p>
        </div>
        <button wire:click="create" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> {{ __('stores.add') }}
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i
                                class="bi bi-search text-muted"></i></span>
                        <input wire:model.live="search" type="text" class="form-control bg-light border-start-0"
                            placeholder="Rechercher par nom ou email...">
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">{{ __('stores.name') }}</th>
                        <th>Contact</th>
                        <th>{{ __('stores.subscription_status') }}</th>
                        <th>{{ __('stores.subscription_ends_at') }}</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stores as $store)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px;">
                                        <i class="bi bi-shop fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $store->name }}</div>
                                        <div class="text-muted small">{{ $store->address ?? 'Pas d\'adresse' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small"><i class="bi bi-envelope me-1"></i> {{ $store->email ?: 'N/A' }}</div>
                                <div class="small"><i class="bi bi-telephone me-1"></i> {{ $store->phone ?: 'N/A' }}
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusClass = match ($store->subscription_status) {
                                        'active' => 'bg-success',
                                        'trial' => 'bg-info',
                                        'inactive' => 'bg-secondary',
                                        'expired' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }} rounded-pill px-3">
                                    {{ ucfirst($store->subscription_status) }}
                                </span>
                            </td>
                            <td>
                                {{ $store->subscription_ends_at ? $store->subscription_ends_at->format('d/m/Y') : 'Illimité' }}
                            </td>
                            <td class="text-end pe-4">
                                <button wire:click="edit({{ $store->id }})" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button
                                    onclick="confirm('Êtes-vous sûr de vouloir supprimer ce point de vente ?') || event.stopImmediatePropagation()"
                                    wire:click="delete({{ $store->id }})" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Aucun point de vente trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stores->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $stores->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Creation/Edition -->
    @if($showingModal)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">{{ $isEditing ? __('stores.edit') : __('stores.add') }}
                        </h5>
                        <button type="button" wire:click="$set('showingModal', false)" class="btn-close"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('stores.name') }}</label>
                                <input wire:model="name" type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Ex: Boutique Progrès">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Téléphone</label>
                                    <input wire:model="phone" type="text"
                                        class="form-control @error('phone') is-invalid @enderror" placeholder="+243...">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input wire:model="email" type="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        placeholder="contact@store.com">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Adresse</label>
                                <textarea wire:model="address" class="form-control @error('address') is-invalid @enderror"
                                    rows="2"></textarea>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">{{ __('stores.subscription_status') }}</label>
                                    <select wire:model="subscription_status"
                                        class="form-select @error('subscription_status') is-invalid @enderror">
                                        <option value="active">Actif</option>
                                        <option value="inactive">Inactif</option>
                                        <option value="trial">Essai</option>
                                        <option value="expired">Expiré</option>
                                    </select>
                                    @error('subscription_status') <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Expiration</label>
                                    <input wire:model="subscription_ends_at" type="date"
                                        class="form-control @error('subscription_ends_at') is-invalid @enderror">
                                    @error('subscription_ends_at') <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if($isEditing && $storeId)
                                    <div class="col-12 mt-2">
                                        <div class="alert alert-info py-2 small">
                                            <i class="bi bi-info-circle me-1"></i> ID Unique:
                                            <strong>STORE-{{ $storeId }}</strong>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer bg-light rounded-bottom">
                            <button type="button" wire:click="$set('showingModal', false)"
                                class="btn btn-outline-secondary">Annuler</button>
                            <button type="submit" class="btn btn-primary px-4">
                                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-2"
                                    role="status"></span>
                                {{ $isEditing ? 'Enregistrer les modifications' : 'Créer le point de vente' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>