<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $sale->sale_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 80mm;
            margin: 0;
            padding: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .header {
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
        }

        .logo {
            max-width: 50mm;
            max-height: 25mm;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            border-bottom: 1px solid #000;
        }

        .item-name {
            display: block;
        }

        .item-details {
            font-size: 10px;
            color: #555;
        }
    </style>
</head>

<body onload="window.print(); window.onafterprint = function() { window.close(); }">
    <div class="text-center header">
        @if($sale->store->logo)
            <img src="{{ asset('storage/' . $sale->store->logo) }}" class="logo">
        @endif
        <div class="bold" style="font-size: 16px;">{{ $sale->store->name }}</div>
        <div>{{ $sale->store->address }}</div>
        <div>Tél: {{ $sale->store->phone }}</div>
        <div class="divider"></div>
        <div class="bold">{{ __('pos.receipt_title') }}</div>
        <div>N°: {{ $sale->sale_number }}</div>
        <div>Date: {{ $sale->created_at->format('d/m/Y H:i') }}</div>
        <div>{{ __('pos.seller') }}: {{ $sale->user->name }}</div>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th>{{ __('pos.designation') }}</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>
                        <span class="item-name">{{ $item->product->name }}</span>
                        <span class="item-details">{{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</span>
                    </td>
                    <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table style="margin-top: 5px;">
        <tr>
            <td>{{ __('pos.subtotal') }}:</td>
            <td class="text-right">{{ number_format($sale->total_amount, 2) }}</td>
        </tr>
        @if($sale->discount > 0)
            <tr>
                <td>{{ __('pos.discount') }}:</td>
                <td class="text-right">-{{ number_format($sale->discount, 2) }}</td>
            </tr>
        @endif
        <tr class="bold">
            <td style="font-size: 14px;">{{ __('pos.total_net') }}:</td>
            <td class="text-right" style="font-size: 14px;">{{ number_format($sale->final_amount, 2) }}
                {{ $sale->store->currency }}
            </td>
        </tr>
    </table>

    <div class="divider" style="margin-top: 10px;"></div>

    <table>
        <tr>
            <td>{{ __('pos.paid') }}:</td>
            <td class="text-right">{{ number_format($sale->amount_paid, 2) }}</td>
        </tr>
        <tr>
            <td>{{ __('pos.change_returned') }}:</td>
            <td class="text-right">{{ number_format($sale->change_given, 2) }}</td>
        </tr>
        <tr>
            <td>{{ __('pos.payment_mode') }}:</td>
            <td class="text-right">{{ __('pos.payment_' . $sale->payment_method) }}</td>
        </tr>
    </table>

    <div class="text-center footer">
        <div class="divider"></div>
        <p>{{ __('pos.receipt_footer_thanks') }}</p>
        <p>{{ __('pos.software_by', ['name' => 'SalesSaaS']) }}</p>
    </div>
</body>

</html>