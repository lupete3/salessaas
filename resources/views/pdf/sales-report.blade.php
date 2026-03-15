<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('reports.sales_report') }}</title>
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
    </style>
</head>

<body>
    <div class="header">
        @if($currentStore->logo)
            <img src="{{ public_path('storage/' . $currentStore->logo) }}" class="logo">
        @endif
        <h1>{{ __('reports.global_sales_report') }}</h1>
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
                <th>{{ __('reports.sale_no') }}</th>
                <th>{{ __('app.date') }}</th>
                <th>{{ __('reports.seller') }}</th>
                <th>{{ __('reports.articles') ?? 'Articles' }}</th>
                <th class="text-right">{{ __('app.amount') }}</th>
                <th class="text-right">{{ __('reports.payment_method') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->sale_number }}</td>
                    <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->user?->name }}</td>
                    <td>
                        @foreach($sale->items as $item)
                            {{ $item->product->name }} (x{{ $item->quantity }}){{ !$loop->last ? ',' : '' }}
                        @endforeach
                    </td>
                    <td class="text-right">{{ number_format($sale->final_amount, 2, ',', ' ') }}</td>
                    <td>{{ $sale->payment_method === 'cash' ? __('reports.cash') : ($sale->payment_method === 'mobile_money' ? __('reports.mobile_money_short') : __('reports.card')) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table style="width: 100%;">
            <tr>
                <td><strong>{{ __('reports.sale_count') ?? "Nombre de ventes" }} :</strong> {{ $sales->count() }}</td>
                <td class="text-right"><strong>{{ __('reports.total_sales_revenue') }} :</strong>
                    {{ number_format($totalAmount, 2, ',', ' ') }} {{ $currency }}</td>
            </tr>
            <tr>
                <td><strong>{{ __('reports.total_articles_sold') }} :</strong>
                    {{ $sales->sum(fn($s) => $s->items->sum('quantity')) }}
                </td>
                <td class="text-right"><strong>{{ __('reports.est_gross_profit') }} :</strong>
                    {{ number_format($totalProfit, 2, ',', ' ') }} {{ $currency }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ __('reports.auto_generated_msg', ['name' => $currentStore->name, 'date' => $date]) }}
        {{ __('reports.page_x_of_y', ['current' => 1, 'total' => 1]) }}
    </div>
</body>

</html>