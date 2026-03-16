<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('reports.inventories_report') }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #0dcaf0;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #0dcaf0;
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
            color: #0dcaf0;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
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
            background-color: #f0f8ff;
            border: 1px solid #cce5ff;
            border-radius: 5px;
        }

        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
            color: #fff;
        }

        .bg-success {
            background-color: #28a745;
        }

        .bg-warning {
            background-color: #ffc107;
            color: #000;
        }

        .bg-secondary {
            background-color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="header">
        @if($currentStore->logo)
            <img src="{{ public_path('storage/' . $currentStore->logo) }}" class="logo">
        @endif
        <h1>{{ __('reports.inventories_report') }}</h1>
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
                <th>{{ __('app.reference') ?? 'Référence' }}</th>
                <th>{{ __('app.status') ?? 'Statut' }}</th>
                <th>{{ __('app.user') ?? 'Utilisateur' }}</th>
                <th class="text-right">{{ __('reports.articles') ?? 'Articles Comptés' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventories as $inventory)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($inventory->date)->format('d/m/Y') }}</td>
                    <td>{{ $inventory->reference }}</td>
                    <td>
                        @if($inventory->status === 'completed')
                            <span class="badge bg-success">{{ __('app.completed') ?? 'Complété' }}</span>
                        @elseif($inventory->status === 'in_progress')
                            <span class="badge bg-warning">{{ __('app.processing') ?? 'En cours' }}</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($inventory->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $inventory->user?->name }}</td>
                    <td class="text-right">{{ $inventory->items->count() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table style="width: 100%;">
            <tr>
                <td><strong>{{ __('reports.total') ?? 'Total Inventaires' }} :</strong> {{ $inventories->count() }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ __('reports.auto_generated_msg', ['name' => $currentStore->name, 'date' => $date]) }}
    </div>
</body>

</html>