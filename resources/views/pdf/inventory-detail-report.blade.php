<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('reports.inventories_report') }} - {{ $inventory->date->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 3px solid #0dcaf0;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0 0 4px 0;
            color: #0dcaf0;
            font-size: 18px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #555;
            margin: 2px 0;
        }

        .logo {
            max-height: 50px;
            margin-bottom: 8px;
        }

        .meta-box {
            background-color: #f8f9fa;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 10px;
        }

        .meta-box table {
            width: 100%;
        }

        .meta-box td {
            padding: 2px 6px;
        }

        .meta-box .label {
            color: #666;
            font-weight: bold;
            width: 30%;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        table.data th,
        table.data td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }

        table.data thead th {
            background-color: #0dcaf0;
            color: #fff;
            font-weight: bold;
            font-size: 10px;
        }

        table.data tbody tr:nth-child(even) {
            background-color: #f8fdff;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .shortage {
            color: #dc3545;
            font-weight: bold;
        }

        .surplus {
            color: #198754;
            font-weight: bold;
        }

        .neutral {
            color: #6c757d;
        }

        .summary {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px 14px;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .summary table {
            width: 100%;
        }

        .summary td {
            padding: 4px 8px;
            font-size: 10px;
        }

        .summary .danger-cell {
            background-color: #fff5f5;
            color: #dc3545;
            font-weight: bold;
        }

        .summary .success-cell {
            background-color: #f0fff4;
            color: #198754;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #198754;
            color: #fff;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 4px;
        }
    </style>
</head>

<body>
    <div class="header">
        @if($currentStore->logo)
            <img src="{{ public_path('storage/' . $currentStore->logo) }}" class="logo">
        @endif
        <h1>{{ __('stock.inventory') }} — {{ __('reports.details') }}</h1>
        <p class="subtitle">{{ $currentStore->name }} | {{ $currentStore->address }} | {{ $currentStore->phone }}</p>
    </div>

    <!-- Inventory metadata -->
    <div class="meta-box">
        <table>
            <tr>
                <td class="label">{{ __('app.date') }} :</td>
                <td>{{ $inventory->date->format('d/m/Y') }}</td>
                <td class="label">{{ __('app.status') }} :</td>
                <td>
                    @if($inventory->status === 'completed')
                        <span class="badge badge-success">Complété</span>
                    @elseif($inventory->status === 'in_progress')
                        <span class="badge badge-warning">En cours</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst($inventory->status) }}</span>
                    @endif
                </td>
                <td class="label">{{ __('app.agent') }} :</td>
                <td>{{ $inventory->user?->name ?? '-' }}</td>
            </tr>
            @if($inventory->notes)
                <tr>
                    <td class="label">Notes :</td>
                    <td colspan="5">{{ $inventory->notes }}</td>
                </tr>
            @endif
        </table>
    </div>

    <!-- Detail Table -->
    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('app.name') }}</th>
                <th class="text-center">{{ __('stock.theoretical_qty') }}</th>
                <th class="text-center">{{ __('stock.physical_qty') }}</th>
                <th class="text-center">{{ __('stock.difference') }}</th>
                <th class="text-right">{{ __('products.purchase_price') ?? 'P.A. Unitaire' }}</th>
                <th class="text-right">{{ __('reports.stock_value_pa') }}</th>
                <th class="text-center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $row)
                @php
                    $item = $row['item'];
                    $diff = $row['diff'];
                    $diffClass = $diff < 0 ? 'shortage' : ($diff > 0 ? 'surplus' : 'neutral');
                    $sign = $diff > 0 ? '+' : '';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="fw-bold">{{ $item->product?->name ?? '-' }}</td>
                    <td class="text-center">{{ $item->quantity_theoretical }}</td>
                    <td class="text-center">{{ $item->quantity_physical }}</td>
                    <td class="text-center {{ $diffClass }}">{{ $sign }}{{ $diff }}</td>
                    <td class="text-right">{{ number_format($item->product?->purchase_price ?? 0, 2, ',', ' ') }}</td>
                    <td class="text-right {{ $diffClass }}">
                        {{ $diff != 0 ? number_format($row['value_diff'], 2, ',', ' ') . ' ' . $currency : '—' }}
                    </td>
                    <td class="text-center">
                        @if($diff < 0)
                            <span class="badge" style="background:#dc3545;color:#fff;">Manque</span>
                        @elseif($diff > 0)
                            <span class="badge" style="background:#198754;color:#fff;">Surplus</span>
                        @else
                            <span class="badge badge-secondary">OK</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary">
        <table>
            <tr>
                <td style="width:25%"><strong>Articles inventoriés :</strong> {{ $items->count() }}</td>
                <td style="width:25%"><strong>Articles conformes :</strong> {{ $items->where('diff', 0)->count() }}</td>
                <td class="danger-cell" style="width:25%">
                    ⚠ Valeur des manquants : {{ number_format($totalShortageValue, 2, ',', ' ') }} {{ $currency }}
                    ({{ $items->where('is_shortage', true)->count() }} art.)
                </td>
                <td class="success-cell" style="width:25%">
                    ↑ Valeur des surplus : {{ number_format($totalSurplusValue, 2, ',', ' ') }} {{ $currency }}
                    ({{ $items->where('is_surplus', true)->count() }} art.)
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ __('reports.auto_generated_msg', ['name' => $currentStore->name, 'date' => $date]) }}
    </div>
</body>

</html>