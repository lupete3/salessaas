<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>{{ __('reports.daily_report') }} - {{ $date }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.5;
            font-size: 13px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #696cff;
            padding-bottom: 15px;
        }

        .store-name {
            font-size: 24px;
            font-weight: bold;
            color: #696cff;
            text-transform: uppercase;
        }

        .logo {
            max-height: 80px;
            margin-bottom: 10px;
        }

        .report-title {
            font-size: 18px;
            color: #566a7f;
            margin-top: 5px;
        }

        .summary-box {
            width: 100%;
            margin-bottom: 30px;
        }

        .summary-card {
            width: 31%;
            display: inline-block;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #d9dee3;
            margin-right: 1%;
        }

        .card-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #a1acb8;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .card-value {
            font-size: 20px;
            font-weight: bold;
        }

        .text-success {
            color: #71dd37;
        }

        .text-danger {
            color: #ff3e1d;
        }

        .text-primary {
            color: #696cff;
        }

        h4 {
            border-left: 4px solid #696cff;
            padding-left: 10px;
            margin-bottom: 15px;
            color: #566a7f;
            text-transform: uppercase;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        th {
            background-color: #f5f5f9;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #d9dee3;
            color: #566a7f;
            font-size: 11px;
            text-transform: uppercase;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #f0f2f4;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #a1acb8;
            padding: 10px 0;
            border-top: 1px solid #d9dee3;
        }
    </style>
</head>

<body>
    <div class="header">
        @if($currentStore->logo)
            <img src="{{ public_path('storage/' . $currentStore->logo) }}" class="logo">
        @endif
        <div class="store-name">{{ $currentStore->name }}</div>
        <div class="report-title">{{ __('reports.daily_report_title') ?? 'Rapport Journalier de Ventes et Dépenses' }}
        </div>
        <div style="margin-top: 5px;">{{ __('app.date') }} : {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
    </div>

    @php
        $totalSales = $sales->sum('final_amount');
        $totalExpenses = $expenses->sum('amount');
        $netResult = $totalSales - $totalExpenses;
    @endphp

    <div class="summary-box">
        <div class="summary-card">
            <div class="card-label">{{ __('pos.sales_today') ?? "Chiffre d'Affaires" }}</div>
            <div class="card-value text-success">{{ number_format($totalSales, 2, ',', ' ') }}
                {{ $currency }}
            </div>
        </div>
        <div class="summary-card">
            <div class="card-label">{{ __('reports.operating_expenses') }}</div>
            <div class="card-value text-danger">{{ number_format($totalExpenses, 2, ',', ' ') }}
                {{ $currency }}
            </div>
        </div>
        <div class="summary-card" style="margin-right: 0;">
            <div class="card-label">{{ __('reports.net_result') ?? 'Résultat Net du jour' }}</div>
            <div class="card-value text-primary">{{ number_format($netResult, 2, ',', ' ') }}
                {{ $currency }}
            </div>
        </div>
    </div>

    <h4>{{ __('reports.top_5_products') }}</h4>
    <table>
        <thead>
            <tr>
                <th>{{ __('reports.product') }}</th>
                <th class="text-center">{{ __('app.quantity') ?? 'Quantité' }}</th>
                <th class="text-end">{{ __('app.revenue') }} ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topProducts as $item)
                <tr>
                    <td class="fw-bold">{{ $item->product->name }}</td>
                    <td class="text-center">{{ $item->total_qty }}</td>
                    <td class="text-end fw-bold">{{ number_format($item->total_revenue, 2, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">{{ __('reports.no_data_available') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="page-break-after: always;"></div>

    <h4>{{ __('reports.expense_details') }}</h4>
    <table>
        <thead>
            <tr>
                <th>{{ __('app.category') ?? 'Catégorie' }}</th>
                <th>{{ __('app.description') ?? 'Description' }}</th>
                <th class="text-end">{{ __('app.amount') }} ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $exp)
                <tr>
                    <td>{{ ucfirst($exp->category) }}</td>
                    <td>{{ $exp->description }}</td>
                    <td class="text-end fw-bold">{{ number_format($exp->amount, 2, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">{{ __('reports.no_expenses_recorded') }}</td>
                </tr>
            @endforelse
            @if($expenses->count() > 0)
                <tr style="background-color: #f5f5f9;">
                    <td colspan="2" class="text-end fw-bold">{{ __('app.total') }} {{ __('finances.expenses') }}</td>
                    <td class="text-end fw-bold">{{ number_format($totalExpenses, 2, ',', ' ') }}
                        {{ $currency }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <h4>{{ __('reports.sale_details') }}</h4>
    <table>
        <thead>
            <tr>
                <th>{{ __('reports.time') ?? 'Heure' }}</th>
                <th>{{ __('reports.invoice_no') }}</th>
                <th>{{ __('reports.seller') }}</th>
                <th class="text-end">{{ __('app.amount') }} ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->created_at->format('H:i') }}</td>
                    <td>{{ $sale->sale_number }}</td>
                    <td>{{ $sale->user->name }}</td>
                    <td class="text-end fw-bold">{{ number_format($sale->final_amount, 2, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">{{ __('reports.no_sales_recorded') }}</td>
                </tr>
            @endforelse
            @if($sales->count() > 0)
                <tr style="background-color: #f5f5f9;">
                    <td colspan="3" class="text-end fw-bold">{{ __('app.total') }} {{ __('pos.sales') }}</td>
                    <td class="text-end fw-bold">{{ number_format($totalSales, 2, ',', ' ') }}
                        {{ $currency }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        {{ __('reports.auto_generated_msg', ['name' => config('app.name'), 'date' => now()->format('d/m/Y H:i')]) }}
    </div>
</body>

</html>