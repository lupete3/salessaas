<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function show(Sale $sale)
    {
        // Check if sale belongs to same store
        if ($sale->store_id !== auth()->user()->store_id) {
            abort(403);
        }

        $sale->load(['items.product', 'user', 'store']);

        $store = auth()->user()->store;
        $currency = $store->currency ?: 'USD';

        return view('pos.receipt', [
            'sale' => $sale,
            'currentStore' => $store,
            'currency' => $currency,
        ]);
    }
}
