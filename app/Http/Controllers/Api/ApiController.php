<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\DebtPayment;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('store')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants invalides.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->slug,
            ],
            'store' => $user->store ? [
                'id' => $user->store->id,
                'name' => $user->store->name,
                'currency' => $user->store->currency,
            ] : null,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true]);
    }

    public function getProducts(Request $request)
    {
        $user = $request->user();
        $products = Product::where('store_id', $user->store_id)
            ->where('is_active', true)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'store_id' => $p->store_id,
                    'category_id' => $p->category_id,
                    'name' => $p->name,
                    'barcode' => $p->barcode,
                    'description' => $p->description,
                    'selling_price' => $p->selling_price,
                    'buying_price' => $p->purchase_price,
                    'stock' => $p->stock_quantity,
                    'min_stock' => $p->min_stock_alert,
                    'unit' => $p->unit,
                ];
            });

        return response()->json(['products' => $products]);
    }

    public function getCustomers(Request $request)
    {
        $user = $request->user();
        $customers = Customer::where('store_id', $user->store_id)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'uuid' => $c->uuid,
                    'local_id' => $c->uuid,
                    'name' => $c->name,
                    'phone' => $c->phone,
                    'address' => $c->address,
                    'total_debt' => $c->total_debt,
                ];
            });

        $payments = DebtPayment::where('store_id', $user->store_id)
            ->get()
            ->map(function ($p) {
                return [
                    'local_id' => $p->uuid,
                    'customer_uuid' => $p->customer->uuid,
                    'amount' => (float) $p->amount,
                    'payment_method' => $p->payment_method,
                    'paid_at' => $p->paid_at->toISOString(),
                ];
            });

        return response()->json([
            'customers' => $customers,
            'debt_payments' => $payments
        ]);
    }

    public function sync(Request $request)
    {
        $user = $request->user();
        $changes = $request->input('changes', []);

        // Process Customers FIRST (so sales can link to them)
        \Log::info('Sync: Processing customers', ['count' => count($changes['customers'] ?? [])]);
        if (isset($changes['customers'])) {
            foreach ($changes['customers'] as $cData) {
                Customer::updateOrCreate(
                    ['uuid' => $cData['uuid']],
                    [
                        'store_id' => $user->store_id,
                        'name' => $cData['name'],
                        'phone' => $cData['phone'],
                        'address' => $cData['address'] ?? null,
                    ]
                );
            }
        }

        // Process Sales
        \Log::info('Sync: Processing sales', ['count' => count($changes['sales'] ?? [])]);
        if (isset($changes['sales'])) {
            foreach ($changes['sales'] as $saleData) {
                if (Sale::where('uuid', $saleData['uuid'])->exists())
                    continue;

                // Find customer id if uuid provided
                $customerId = null;
                if (!empty($saleData['customer_uuid'])) {
                    $customerId = Customer::where('uuid', $saleData['customer_uuid'])->value('id');
                }

                $sale = Sale::create([
                    'store_id' => $user->store_id,
                    'user_id' => $user->id,
                    'customer_id' => $customerId,
                    'uuid' => $saleData['uuid'],
                    'sale_number' => Sale::generateSaleNumber($user->store_id),
                    'total_amount' => $saleData['total_amount'],
                    'final_amount' => $saleData['final_amount'],
                    'amount_paid' => $saleData['amount_paid'] ?? $saleData['final_amount'],
                    'change_given' => $saleData['change_given'] ?? 0,
                    'discount' => $saleData['discount'],
                    'payment_method' => $saleData['payment_method'],
                    'status' => 'completed',
                    'created_at' => $saleData['sale_date'],
                ]);

                foreach ($saleData['items'] as $item) {
                    $sale->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    // Update stock
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->decrement('stock_quantity', $item['quantity']);
                    }
                }
            }
        }

        // Process Expenses
        if (isset($changes['expenses'])) {
            foreach ($changes['expenses'] as $eData) {
                if (Expense::where('uuid', $eData['uuid'])->exists())
                    continue;

                Expense::create([
                    'store_id' => $user->store_id,
                    'user_id' => $user->id,
                    'uuid' => $eData['uuid'],
                    'amount' => $eData['amount'],
                    'description' => $eData['description'],
                    'category' => $eData['category'],
                    'expense_date' => $eData['spent_at'] ? date('Y-m-d', strtotime($eData['spent_at'])) : now(),
                ]);
            }
        }

        // Process Debt Payments
        if (isset($changes['debt_payments'])) {
            foreach ($changes['debt_payments'] as $pData) {
                if (DebtPayment::where('uuid', $pData['uuid'])->exists())
                    continue;

                $customerId = Customer::where('uuid', $pData['customer_uuid'])->value('id');
                if (!$customerId)
                    continue;

                DebtPayment::create([
                    'store_id' => $user->store_id,
                    'user_id' => $user->id,
                    'customer_id' => $customerId,
                    'uuid' => $pData['uuid'],
                    'amount' => $pData['amount'],
                    'payment_method' => $pData['payment_method'],
                    'paid_at' => $pData['paid_at'] ? date('Y-m-d H:i:s', strtotime($pData['paid_at'])) : now(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'results' => [
                'customers' => array_map(fn($c) => ['local_id' => $c['uuid'], 'uuid' => $c['uuid']], $changes['customers'] ?? []),
                'sales' => array_map(fn($s) => ['local_id' => $s['uuid'], 'server_id' => 1], $changes['sales'] ?? []),
                'expenses' => array_map(fn($e) => ['local_id' => $e['uuid']], $changes['expenses'] ?? []),
                'debt_payments' => array_map(fn($p) => ['local_id' => $p['uuid']], $changes['debt_payments'] ?? []),
            ]
        ]);
    }
}
