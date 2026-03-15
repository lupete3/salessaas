<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('reports.finance_report') }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
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

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #555;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table td {
            padding: 10px;
            vertical-align: middle;
        }

        .label {
            font-size: 12px;
            font-weight: 600;
        }

        .value {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
        }

        .text-success {
            color: #71dd37;
        }

        .text-danger {
            color: #ff3e1d;
        }

        .text-warning {
            color: #ffab00;
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

        .result-box {
            margin-top: 30px;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #eee;
        }

        .result-label {
            font-size: 16px;
            margin-bottom: 10px;
            color: #666;
        }

        .result-value {
            font-size: 28px;
            font-weight: 800;
        }
    </style>
</head>

<body>
    <div class="header">
        @if($currentStore->logo)
            <img src="{{ public_path('storage/' . $currentStore->logo) }}" class="logo">
        @endif
        <h1>{{ __('reports.finance_summary_title') }}</h1>
        <p>{{ $currentStore->name }} - {{ __('reports.period') }} {{ __('reports.from') }}
            {{ date('d/m/Y', strtotime($startDate)) }} {{ __('reports.to') }}
            {{ date('d/m/Y', strtotime($endDate)) }}
        </p>
        <p style="font-size: 10px; margin-top: -10px;">{{ $currentStore->address }} | {{ $currentStore->phone }}
        </p>
    </div>

    <!-- RECEIPTS SECTION -->
    <div class="section">
        <div class="section-title">{{ __('reports.receipts') }}</div>
        <table class="table">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th class="label" style="text-align: left; padding: 8px;">{{ __('reports.designation') }}</th>
                    <th class="label" style="text-align: right; padding: 8px;">{{ __('app.amount') }} ({{ $currency }})
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="label">{{ __('reports.cash_sales') }}</td>
                    <td class="value text-success">+ {{ number_format($salesByMethod->get('cash', 0), 2, ',', ' ') }}
                    </td>
                </tr>
                <tr>
                    <td class="label">{{ __('reports.mobile_sales') }}</td>
                    <td class="value text-success">+
                        {{ number_format($salesByMethod->get('mobile_money', 0), 2, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td class="label">{{ __('reports.card_sales') }}</td>
                    <td class="value text-success">+ {{ number_format($salesByMethod->get('card', 0), 2, ',', ' ') }}
                    </td>
                </tr>
                <tr style="border-top: 1px solid #ddd; background-color: #f0fff0;">
                    <td class="label" style="text-transform: uppercase;">{{ __('reports.total_receipts') }}</td>
                    <td class="value text-success" style="font-size: 16px;">
                        {{ number_format($totalSales, 2, ',', ' ') }} {{ $currency }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- PAYMENTS SECTION -->
    <div class="section">
        <div class="section-title">{{ __('reports.payments') }}</div>
        <table class="table">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th class="label" style="text-align: left; padding: 8px;">{{ __('reports.designation') }}</th>
                    <th class="label" style="text-align: right; padding: 8px;">{{ __('app.amount') }} ({{ $currency }})
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByCategory as $category => $total)
                    <tr>
                        <td class="label">{{ ucfirst($category) }}</td>
                        <td class="value text-danger">- {{ number_format($total, 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="label">{{ __('reports.total_stock_purchases') }}</td>
                    <td class="value text-danger">- {{ number_format($totalPurchases, 2, ',', ' ') }}</td>
                </tr>
                <tr style="border-top: 1px solid #ddd; background-color: #fff0f0;">
                    <td class="label" style="text-transform: uppercase;">{{ __('reports.total_payments') }}</td>
                    <td class="value text-danger" style="font-size: 16px;">
                        {{ number_format($totalExpenses + $totalPurchases, 2, ',', ' ') }} {{ $currency }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="result-box"
        style="background-color: {{ $netCashFlow >= 0 ? '#f0fff0' : '#fff0f0' }}; border-color: {{ $netCashFlow >= 0 ? '#d1ffd1' : '#ffd1d1' }};">
        <div class="result-label" style="font-weight: bold; text-transform: uppercase;">
            {{ __('reports.net_cash_balance') }}</div>
        <div class="result-value"
            style="color: {{ $netCashFlow >= 0 ? '#198754' : '#dc3545' }}; text-align: center; font-weight: bold; font-size: 32px;">
            {{ number_format($netCashFlow, 2, ',', ' ') }} {{ $currentStore->currency }}
        </div>
        <p style="margin-top: 10px; font-style: italic; font-size: 10px; color: #666;">
            {{ __('reports.finance_footer_note') }}
        </p>
    </div>

    <div class="section" style="margin-top: 40px; page-break-inside: avoid;">
        <div class="section-title">{{ __('reports.signatures') }}</div>
        <table style="width: 100%; margin-top: 20px;">
            <tr>
                <td style="width: 50%; text-align: center;">
                    <strong>{{ __('reports.accountant_manager') }}</strong><br><br><br><br>
                    ..........................................
                </td>
                <td style="width: 50%; text-align: center;">
                    <strong>{{ __('reports.owner') }}</strong><br><br><br><br>
                    ..........................................
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ __('reports.auto_generated_msg', ['name' => $currentStore->name, 'date' => $date]) }}
        {{ __('reports.page_x_of_y', ['current' => 1, 'total' => 1]) }}
    </div>
</body>

</html>