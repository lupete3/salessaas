<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ __('users.title') }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('app.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('users.title') }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> {{ __('users.add') }}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ __('users.user') }}</th>
                        <th>{{ __('users.role') }}</th>
                        <th>{{ __('users.phone') }}</th>
                        <th class="text-center">{{ __('users.status') }}</th>
                        <th class="text-end pe-4">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-label-primary rounded p-2 me-3"
                                        style="background:#e7e7ff; color:#696cff">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $u->name }}</div>
                                        <small class="text-muted">{{ $u->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-label-secondary"
                                    style="background:#f1f0f2; color:#8592a3">{{ $u->role->name }}</span></td>
                            <td>{{ $u->phone }}</td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" wire:click="toggle({{ $u->id }})"
                                        @if($u->is_active) checked @endif>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('users.edit', $u->id) }}" class="btn btn-sm btn-icon"><i
                                        class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>