<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('reports.stock_report') }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #198754;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #198754;
            font-size: 20px;
        }

        .logo {
            max-height: 60px;
            margin-bottom: 10px;
        }

        .info {
            margin-bottom: 20px;
        }

        .info table {
            width: 100%;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #198754;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }

        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f0fff0;
            border-radius: 5px;
        }

        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
            color: #fff;
        }

        .bg-danger {
            background-color: #ff3e1d;
        }

        .bg-warning {
            background-color: #ffab00;
            color: #000;
        }
    </style>
</head>

<body>
    <div class="header">
        @if($currentStore->logo)
            <img src="{{ public_path('storage/' . $currentStore->logo) }}" class="logo">
        @endif
        <h1>{{ __('reports.stock_report_title') }}</h1>
        <p>{{ $currentStore->name }} - {{ $date }}</p>
        <p style="font-size: 10px; margin-top: -10px;">{{ $currentStore->address }} | {{ $currentStore->phone }}
        </p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td><strong>{{ __('reports.date') }} :</strong> {{ $date }}</td>
                <td class="text-right"><strong>{{ __('reports.generated_by') }} :</strong> {{ auth()->user()->name }}
                </td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>{{ __('reports.designation') }}</th>
                <th>{{ __('reports.category') }}</th>
                <th>{{ __('reports.supplier') }}</th>
                <th class="text-right">{{ __('reports.current_stock') }}</th>
                <th class="text-right">{{ __('reports.purchase_price_unit') }}</th>
                <th class="text-right">{{ __('reports.selling_price_unit') }}</th>
                <th class="text-right">{{ __('reports.stock_value_pa') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $m)
                <tr>
                    <td>
                        {{ $m->name }}
                        @if($m->isOutOfStock())
                            <span class="badge bg-danger">{{ __('reports.out_of_stock_badge') }}</span>
                        @elseif($m->isLowStock())
                            <span class="badge bg-warning">{{ __('reports.low_stock_badge') }}</span>
                        @endif
                    </td>
                    <td>{{ $m->category }}</td>
                    <td>{{ $m->supplier?->name ?? '—' }}</td>
                    <td class="text-right">{{ $m->stock_quantity }} {{ $m->unit }}</td>
                    <td class="text-right">{{ number_format($m->purchase_price, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($m->selling_price, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($m->stock_quantity * $m->purchase_price, 2, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table style="width: 100%;">
            <tr>
                <td><strong>{{ __('reports.item_count') }} :</strong> {{ $products->count() }}</td>
                <td class="text-right"><strong>{{ __('reports.total_value_purchase') }} :</strong>
                    {{ number_format($totalPurchaseValue, 2, ',', ' ') }} {{ $currency }}</td>
            </tr>
            <tr>
                <td></td>
                <td class="text-right"><strong>{{ __('reports.total_value_selling') }} :</strong>
                    {{ number_format($totalSellingValue, 2, ',', ' ') }} {{ $currency }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ __('reports.auto_generated_msg', ['name' => $currentStore->name, 'date' => $date]) }}
        {{ __('reports.page_x_of_y', ['current' => 1, 'total' => 1]) }}
    </div>
</body>

</html>