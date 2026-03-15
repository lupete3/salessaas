<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('reports.customer_debts_report') }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #dc3545;
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
            color: #dc3545;
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
            background-color: #fff5f5;
            border: 1px solid #ffcccc;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        @if($currentStore->logo)
            <img src="{{ public_path('storage/' . $currentStore->logo) }}" class="logo">
        @endif
        <h1>{{ __('reports.customer_debts_report') }}</h1>
        <p>{{ $currentStore->name }} - {{ $date }}</p>
        <p style="font-size: 10px; margin-top: -10px;">{{ $currentStore->address }} | {{ $currentStore->phone }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>{{ __('app.name') ?? 'Client' }}</th>
                <th>{{ __('app.phone') ?? 'Téléphone' }}</th>
                <th class="text-right">{{ __('app.total_debt') ?? 'Dette Totale' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->phone ?? '-' }}</td>
                    <td class="text-right fw-bold">{{ number_format($customer->total_debt, 2, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table style="width: 100%;">
            <tr>
                <td><strong>{{ __('reports.total') ?? 'Nombre de Clients' }} :</strong> {{ $customers->count() }}</td>
                <td class="text-right"><strong>{{ __('app.total_amount') ?? 'Montant Total' }} :</strong>
                    {{ number_format($totalDebt, 2, ',', ' ') }} {{ $currency }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ __('reports.auto_generated_msg', ['name' => $currentStore->name, 'date' => $date]) }}
    </div>
</body>

</html>