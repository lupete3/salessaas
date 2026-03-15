<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('reports.purchases_report') }}</title>
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
            padding: 15px;
            background-color: #f0fff0;
            border: 1px solid #d1ffd1;
            border-radius: 5px;
        }

        .status {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            text-transform: uppercase;
        }

        .status-paid {
            background-color: #d1ffd1;
            color: #006600;
        }

        .status-partial {
            background-color: #fff9c4;
            color: #827717;
        }

        .status-pending {
            background-color: #ffcdd2;
            color: #b71c1c;
        }
    </style>
</head>

<body>
    <div class="header">
        @if($currentStore->logo)
            <img src="{{ public_path('storage/' . $currentStore->logo) }}" class="logo">
        @endif
        <h1>{{ __('reports.global_purchases_report') }}</h1>
        <p>{{ $currentStore->name }} - {{ __('reports.period') }} {{ __('reports.from') }}
            {{ date('d/m/Y', strtotime($startDate)) }} {{ __('reports.to') }}
            {{ date('d/m/Y', strtotime($endDate)) }}
        </p>
        <p style="font-size: 10px; margin-top: -10px;">{{ $currentStore->address }} | {{ $currentStore->phone }}
        </p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>{{ __('reports.purchase_no') }}</th>
                <th>{{ __('app.date') }}</th>
                <th>{{ __('reports.supplier') }}</th>
                <th>{{ __('reports.products_list') }}</th>
                <th class="text-right">{{ __('app.total') }}</th>
                <th>{{ __('app.status') ?? 'Statut' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->purchase_number }}</td>
                    <td>{{ $purchase->created_at->format('d/m/Y') }}</td>
                    <td>{{ $purchase->supplier->name }}</td>
                    <td>
                        @foreach($purchase->items as $item)
                            {{ $item->product->name }} (x{{ $item->quantity }}){{ !$loop->last ? ',' : '' }}
                        @endforeach
                    </td>
                    <td class="text-right">{{ number_format($purchase->total_amount, 2, ',', ' ') }}</td>
                    <td>
                        <span class="status status-{{ $purchase->status }}">
                            {{ $purchase->statusLabel() }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table style="width: 100%;">
            <tr>
                <td><strong>{{ __('reports.order_count') }} :</strong> {{ $purchases->count() }}</td>
                <td class="text-right"><strong>{{ __('reports.total_purchases_amount') }} :</strong>
                    {{ number_format($totalAmount, 2, ',', ' ') }} {{ $currency }}</td>
            </tr>
            <tr>
                <td><strong>{{ __('reports.total_paid') }} :</strong>
                    {{ number_format($purchases->sum('amount_paid'), 2, ',', ' ') }} {{ $currency }}
                </td>
                <td class="text-right"><strong>{{ __('reports.balance_due_report') }} :</strong>
                    {{ number_format($purchases->sum('balance_due'), 2, ',', ' ') }} {{ $currency }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ __('reports.auto_generated_msg', ['name' => $currentStore->name, 'date' => $date]) }}
        {{ __('reports.page_x_of_y', ['current' => 1, 'total' => 1]) }}
    </div>
</body>

</html>