<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('reports.stock_movements_report') }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ffc107;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #ffc107;
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
            color: #ffc107;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-success {
            color: #198754;
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
            background-color: #fffdf5;
            border: 1px solid #ffeeba;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        @if($currentStore->logo)
            <img src="{{ public_path('storage/' . $currentStore->logo) }}" class="logo">
        @endif
        <h1>{{ __('reports.stock_movements_report') }}</h1>
        <p>{{ $currentStore->name }} - {{ __('reports.period') }} {{ __('reports.from') }}
            {{ date('d/m/Y', strtotime($startDate)) }} {{ __('reports.to') }}
            {{ date('d/m/Y', strtotime($endDate)) }}
        </p>
        <p style="font-size: 10px; margin-top: -10px;">{{ $currentStore->address }} | {{ $currentStore->phone }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>{{ __('app.date') }}</th>
                <th>{{ __('purchases.product') ?? 'Produit' }}</th>
                <th>{{ __('app.status') ?? 'Type' }}</th>
                <th class="text-center">{{ __('purchases.quantity') }}</th>
                <th class="text-center">{{ __('stock.previous') ?? 'Avant' }}</th>
                <th class="text-center">{{ __('stock.new') ?? 'Après' }}</th>
                <th>{{ __('stock.reason_notes') ?? 'Raison' }}</th>
                <th>{{ __('app.user') ?? 'Utilisateur' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $m)
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td><strong>{{ $m->product->name }}</strong></td>
                    <td>{{ $m->typeLabel() }}</td>
                    <td
                        class="text-center fw-bold {{ $m->quantity_after < $m->quantity_before ? 'text-danger' : 'text-success' }}">
                        {{ $m->quantity_after < $m->quantity_before ? '-' : '+' }}{{ $m->quantity }}
                    </td>
                    <td class="text-center">{{ $m->quantity_before }}</td>
                    <td class="text-center fw-bold">{{ $m->quantity_after }}</td>
                    <td>{{ $m->reason ?? '-' }}</td>
                    <td>{{ $m->user?->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table style="width: 100%;">
            <tr>
                <td><strong>{{ __('reports.total') ?? 'Total Mouvements' }} :</strong> {{ $movements->count() }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ __('reports.auto_generated_msg', ['name' => $currentStore->name, 'date' => $date]) }}
    </div>
</body>

</html>