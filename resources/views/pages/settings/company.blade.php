<?php

use App\Concerns\StoreValidationRules;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use StoreValidationRules, WithFileUploads;

    public string $name = '';
    public string $address = '';
    public string $phone = '';
    public string $email = '';
    public string $currency = '';
    public string $license_number = '';
    public $logo;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $store = Auth::user()->store;

        $this->name = $store->name;
        $this->address = $store->address ?? '';
        $this->phone = $store->phone ?? '';
        $this->email = $store->email ?? '';
        $this->currency = $store->currency ?? 'CDF';
        $this->license_number = $store->license_number ?? '';
    }

    /**
     * Update the store information.
     */
    public function updateStoreInformation(): void
    {
        $store = Auth::user()->store;

        $validated = $this->validate($this->storeRules($store->id));

        $store->fill($validated);

        if ($this->logo) {
            $path = $this->logo->store('logos', 'public');
            $store->logo = $path;
        }

        $store->save();

        $this->dispatch('company-updated');
    }
}; ?>

<section class="w-full pb-5">
    <x-pages::settings.layout :heading="__('app.company_title')" :subheading="__('app.company_subtitle')">

        <div class="card shadow-sm border-0 overflow-hidden mb-4">
            <div class="card-body p-0">
                <!-- Company Header -->
                <div class="p-4 bg-body-tertiary border-bottom d-flex align-items-center gap-4">
                    <div
                        style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: #fff; border: 1px solid #dee2e6; overflow: hidden;">
                        @if(auth()->user()->store->logo)
                            <img src="{{ Storage::url(auth()->user()->store->logo) }}" alt="Logo"
                                class="w-100 h-100 object-fit-contain">
                        @else
                            <i class="bi bi-building fs-1 text-muted"></i>
                        @endif
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">{{ auth()->user()->store->name }}</h5>
                        <p class="text-muted mb-0 small"><i class="bi bi-geo-alt me-1"></i>
                            {{ auth()->user()->store->address ?: __('app.no_address') }}
                        </p>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="p-4">
                    <form wire:submit="updateStoreInformation">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label
                                    class="form-label fw-bold small text-uppercase text-muted">{{ __('app.company_name') }}</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text border-end-0"><i
                                            class="bi bi-building text-muted"></i></span>
                                    <input type="text" wire:model="name" class="form-control border-start-0 py-2"
                                        required>
                                </div>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label
                                    class="form-label fw-bold small text-uppercase text-muted">{{ __('app.company_email') }}</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text border-end-0"><i
                                            class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" wire:model="email" class="form-control border-start-0 py-2">
                                </div>
                                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label
                                    class="form-label fw-bold small text-uppercase text-muted">{{ __('app.company_phone') }}</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text border-end-0"><i
                                            class="bi bi-telephone text-muted"></i></span>
                                    <input type="text" wire:model="phone" class="form-control border-start-0 py-2">
                                </div>
                                @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label
                                    class="form-label fw-bold small text-uppercase text-muted">{{ __('app.company_currency') }}</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text border-end-0"><i
                                            class="bi bi-cash-stack text-muted"></i></span>
                                    <input type="text" wire:model="currency" class="form-control border-start-0 py-2"
                                        required maxlength="10">
                                </div>
                                @error('currency') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12 mb-4">
                                <label
                                    class="form-label fw-bold small text-uppercase text-muted">{{ __('app.company_address') }}</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text border-end-0"><i
                                            class="bi bi-geo-alt text-muted"></i></span>
                                    <textarea wire:model="address" class="form-control border-start-0 py-2" rows="2"
                                        required></textarea>
                                </div>
                                @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label
                                    class="form-label fw-bold small text-uppercase text-muted">{{ __('app.company_license') }}</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text border-end-0"><i
                                            class="bi bi-file-earmark-lock text-muted"></i></span>
                                    <input type="text" wire:model="license_number"
                                        class="form-control border-start-0 py-2">
                                </div>
                                @error('license_number') <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label
                                    class="form-label fw-bold small text-uppercase text-muted">{{ __('app.company_logo') }}</label>
                                <div class="input-group shadow-sm">
                                    <input type="file" wire:model="logo" class="form-control py-2">
                                </div>
                                <div wire:loading wire:target="logo" class="text-muted small mt-1">Uploading...</div>
                                @error('logo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 pt-2">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm" wire:loading.attr="disabled">
                                <i class="bi bi-check-circle me-2"></i> {{ __('app.save') }}
                            </button>
                            <x-action-message class="text-success small fw-bold" on="company-updated">
                                <i class="bi bi-check-lg me-1"></i> {{ __('app.company_updated') }}
                            </x-action-message>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-pages::settings.layout>
</section>