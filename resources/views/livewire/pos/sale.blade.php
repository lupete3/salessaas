<div class="row g-4" style="height: calc(100vh - 100px);">
    <!-- Left: Cart & Payment (60%) -->
    <div class="col-lg-7 d-flex flex-column" style="height: 100%;">
        <div class="card shadow-sm border-0 flex-grow-1 d-flex flex-column mb-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-cart3 me-2"></i> {{ __('pos.cart_title') }}</h5>
                <button wire:click="clearCart" wire:loading.attr="disabled" wire:target="clearCart"
                    class="btn btn-sm btn-outline-danger" @if(empty($cart)) disabled @endif>
                    <span wire:loading.remove wire:target="clearCart">{{ __('pos.empty_cart') }}</span>
                    <span wire:loading wire:target="clearCart"><span
                            class="spinner-border spinner-border-sm"></span></span>
                </button>
            </div>
            <div class="card-body p-0 overflow-auto flex-grow-1" style="min-height: 300px;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-3">{{ __('pos.item') }}</th>
                            <th class="text-center" style="width: 120px;">{{ __('pos.price') }}</th>
                            <th class="text-center" style="width: 140px;">{{ __('pos.qty') }}</th>
                            <th class="text-end pe-3" style="width: 120px;">{{ __('pos.total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cart as $key => $item)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold">{{ $item['name'] }}</div>
                                    <small class="text-muted">{{ $item['form'] }}</small>
                                    <small class="text-muted d-block small">
                                        {{ __('pos.in_stock') }} <span class="fw-bold">{{ $item['max_qty'] }}</span>
                                    </small>
                                </td>
                                <td class="text-center">{{ number_format($item['price'], 2) }}</td>
                                <td class="text-center">
                                    <div class="input-group input-group-sm mx-auto" style="width: 100px;">
                                        <button wire:click="updateQty('{{ $key }}', {{ $item['qty'] - 1 }})"
                                            wire:loading.attr="disabled" wire:target="updateQty"
                                            class="btn btn-outline-secondary p-1">-</button>
                                        <input type="number" class="form-control text-center p-0" value="{{ $item['qty'] }}"
                                            wire:change="updateQty('{{ $key }}', $event.target.value)" min="1"
                                            max="{{ $item['max_qty'] }}">
                                        <button wire:click="updateQty('{{ $key }}', {{ $item['qty'] + 1 }})"
                                            wire:loading.attr="disabled" wire:target="updateQty"
                                            class="btn btn-outline-secondary p-1">+</button>
                                    </div>
                                    <small class="text-xs text-muted">Stock: {{ $item['max_qty'] }}</small>
                                </td>
                                <td class="text-end pe-3 fw-bold">{{ number_format($item['subtotal'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="py-5">
                                        <i class="bi bi-cart-x display-1 text-light"></i>
                                        <p class="text-muted mt-3">{{ __('pos.empty_message') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Cart Footer (Totals) -->
            <div class="card-footer bg-light border-top p-4">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('pos.subtotal_label') }}</span>
                            <span class="fw-bold">{{ number_format($this->getTotal(), 2) }}
                                {{ $currency }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <span>{{ __('pos.discount_label') }}</span>
                            <div class="input-group input-group-sm w-50">
                                <input type="number" class="form-control text-end" wire:model.live="amount_discount">
                                <span class="input-group-text">{{ $currency }}</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pt-2 border-top">
                            <span class="h5 fw-bold mb-0">{{ __('pos.final_total') }}</span>
                            <span class="h5 fw-bold mb-0 text-primary">{{ number_format($this->getFinalTotal(), 2) }}
                                {{ $currency }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6 ps-lg-5">
                        <div class="text-end">
                            <h2 class="fw-bold text-primary mb-0" style="font-size: 2.25rem;">
                                {{ number_format($this->getFinalTotal(), 2) }} <small
                                    class="fs-6 text-muted">{{ $currency }}</small>
                            </h2>
                            <span class="text-muted small">{{ __('pos.final_total') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Search & Finalize (40%) -->
    <div class="col-lg-5 d-flex flex-column" style="height: 100%;">
        <!-- Select Customer -->
        <div class="card shadow-sm border-0 mb-3 overflow-visible position-relative">
            <div class="card-body">
                <h6 class="fw-bold mb-2 small text-uppercase text-muted">{{ __('pos.customer_selection') }}</h6>
                @if($selectedCustomerId)
                    <div
                        class="d-flex align-items-center justify-content-between bg-primary bg-opacity-10 p-2 rounded border border-primary-subtle">
                        <div>
                            <span class="fw-bold text-primary">{{ $selectedCustomerName }}</span>
                        </div>
                        <button wire:click="removeCustomer" class="btn btn-sm btn-icon text-danger p-0"><i
                                class="bi bi-x-circle-fill"></i></button>
                    </div>
                @else
                    <div class="position-relative">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-end-0"><i class="bi bi-person-search"></i></span>
                            <input type="text" class="form-control border-start-0"
                                placeholder="{{ __('pos.customer_search') }}"
                                wire:model.live.debounce.250ms="customerSearch">
                        </div>
                        @if(!empty($customerResults))
                            <ul class="list-group position-absolute w-100 shadow-lg z-3 mt-1" style="z-index: 1050;">
                                @foreach($customerResults as $res)
                                    <li class="list-group-item list-group-item-action cursor-pointer py-2"
                                        wire:click="selectCustomer({{ $res['id'] }}, '{{ addslashes($res['name']) }}')">
                                        <div class="fw-bold small">{{ $res['name'] }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $res['phone'] }}</div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Search Product -->
        <div class="card shadow-sm border-0 mb-3 overflow-visible position-relative">
            <div class="card-body">
                <div class="input-group input-group-lg border-0 shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search text-primary"></i></span>
                    <input type="text" class="form-control border-0 px-2" placeholder="{{ __('pos.product_search') }}"
                        wire:model.live.debounce.250ms="search" autocomplete="off" autofocus>
                </div>

                @if(!empty($searchResults))
                    <div class="pos-results mt-2 border rounded shadow">
                        @foreach($searchResults as $res)
                            <div class="pos-item" wire:click="addToCart({{ $res['id'] }})">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $res['name'] }}</strong>
                                    <span class="fw-bold text-primary">{{ number_format($res['selling_price'], 2) }}
                                        {{ $currency }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>{{ $res['generic_name'] ?? '' }} ({{ $res['form'] }})</span>
                                    <span>{{ __('pos.in_stock') }} <strong>{{ $res['stock_quantity'] }}</strong></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment Setup -->
        <div class="card shadow-sm border-0 flex-grow-1 overflow-auto">
            <div class="card-body">
                <h6 class="fw-bold mb-3">{{ __('pos.payment_details') }}</h6>

                <div class="mb-4">
                    <label
                        class="form-label small fw-bold text-uppercase text-muted">{{ __('pos.payment_method_label') }}</label>
                    <div class="row g-2">
                        @foreach(['cash' => 'Espèces', 'mobile' => 'Mobile Money', 'card' => 'Carte'] as $val => $lab)
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="payment_method" id="pm_{{ $val }}"
                                    value="{{ $val }}" wire:model="paymentMethod">
                                <label class="btn btn-outline-success w-100 py-2 d-flex flex-column align-items-center"
                                    for="pm_{{ $val }}">
                                    <i
                                        class="bi {{ $val == 'cash' ? 'bi-cash-stack' : ($val == 'mobile' ? 'bi-phone' : 'bi-credit-card') }} fs-4 mb-1"></i>
                                    <span style="font-size: .7rem">{{ __("pos.payment_{$val}") }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4">
                    <label
                        class="form-label small fw-bold text-uppercase text-muted">{{ __('pos.amount_received') }}</label>
                    <div class="input-group input-group-lg shadow-sm">
                        <input type="number" step="0.01" class="form-control fw-bold text-center" placeholder="0.00"
                            wire:model.live="amount_paid">
                        <span class="input-group-text">{{ $currency }}</span>
                    </div>
                    <div class="mt-2 d-flex gap-2">
                        @foreach([5, 10, 20, 50, 100] as $amt)
                            <button type="button" class="btn btn-xs btn-outline-secondary p-1 flex-grow-1"
                                wire:click="$set('amount_paid', {{ $this->getFinalTotal() + $amt }})">+{{ $amt }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="bg-light p-3 rounded mb-4 text-center border">
                    @if($this->getChange() > 0)
                        <div class="text-muted small">{{ __('pos.change_to_return') }}</div>
                        <div class="h3 fw-bold mb-0 text-success">
                            {{ number_format($this->getChange(), 2) }} {{ $currency }}
                        </div>
                    @elseif($this->getFinalTotal() > ($amount_paid ?: 0))
                        <div class="text-danger small fw-bold text-uppercase">{{ __('pos.debt_to_record') }}</div>
                        <div class="h3 fw-bold mb-0 text-danger">
                            {{ number_format($this->getFinalTotal() - ($amount_paid ?: 0), 2) }} {{ $currency }}
                        </div>
                        @if(!$selectedCustomerId)
                            <div class="badge bg-danger mt-1">{{ __('pos.customer_required') }}</div>
                        @endif
                    @else
                        <div class="text-muted small">{{ __('pos.exact_amount') }}</div>
                        <div class="h3 fw-bold mb-0 text-primary">
                            0.00 {{ $currency }}
                        </div>
                    @endif
                </div>

                <button class="btn btn-primary btn-lg w-100 py-3 shadow-lg border-0" wire:click="finalize"
                    wire:loading.attr="disabled" wire:target="finalize" @if(empty($cart) || (($amount_paid ?: 0) < $this->getFinalTotal() && !$selectedCustomerId)) disabled @endif>
                    <span wire:loading.remove wire:target="finalize">
                        <i class="bi bi-check2-circle me-2"></i> {{ __('pos.validate_sale') }}
                    </span>
                    <span wire:loading wire:target="finalize">
                        <span class="spinner-border spinner-border-sm me-2"></span> {{ __('pos.processing') }}
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Receipt Modal (Placeholder) -->
    <div class="modal fade @if($showReceiptModal) show @endif"
        style="display: @if($showReceiptModal) block @else none @endif;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">{{ __('pos.sale_success_title') }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showReceiptModal', false)"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="display-1 text-success mb-3"><i class="bi bi-check-circle"></i></div>
                    <h4>{{ __('pos.sale_success_msg') }}</h4>
                    <p class="text-muted mb-4 small">{{ __('pos.print_receipt_ask') }}</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('pos.receipt', $lastSaleId ?? 0) }}" target="_blank" class="btn btn-primary"
                            onclick="window.setTimeout(() => { @this.set('showReceiptModal', false) }, 1000)">
                            <i class="bi bi-printer me-2"></i>{{ __('pos.print_ticket') }}
                        </a>
                        <button class="btn btn-outline-secondary"
                            wire:click="$set('showReceiptModal', false)">{{ __('pos.later') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if($showReceiptModal)
    <div class="modal-backdrop fade show"></div> @endif
</div>