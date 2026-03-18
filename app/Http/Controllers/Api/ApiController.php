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
use Illuminate\Support\Facades\DB;
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

        if ($user->store && !$user->store->isActive() && !$user->isSuperAdmin()) {
            return response()->json(['message' => 'L\'entreprise est inactive ou l\'abonnement a expiré.'], 403);
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
                'address' => $user->store->address,
                'phone' => $user->store->phone,
                'email' => $user->store->email,
                'logo' => $user->store->logo ? asset('storage/' . $user->store->logo) : null,
                'license_number' => $user->store->license_number,
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

        return response()->json([
            'products' => $products,
            'store' => $user->store ? [
                'id' => $user->store->id,
                'name' => $user->store->name,
                'currency' => $user->store->currency,
                'address' => $user->store->address,
                'phone' => $user->store->phone,
                'email' => $user->store->email,
                'logo' => $user->store->logo ? asset('storage/' . $user->store->logo) : null,
                'license_number' => $user->store->license_number,
            ] : null,
        ]);
    }

    public function getCustomers(Request $request)
    {
        $user = $request->user();
        $customers = Customer::where('store_id', $user->store_id)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'uuid' => $c->uuid ?? (string) $c->id,
                    'local_id' => $c->uuid ?? (string) $c->id,
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

    public function getExpenses(Request $request)
    {
        $user = $request->user();
        $expenses = Expense::where('store_id', $user->store_id)
            ->latest()
            ->limit(100)
            ->get()
            ->map(function ($e) {
                return [
                    'local_id' => $e->uuid,
                    'amount' => (float) $e->amount,
                    'description' => $e->description,
                    'category' => $e->category,
                    'spent_at' => is_string($e->expense_date) ? $e->expense_date : $e->expense_date->toISOString(),
                    'is_synced' => true,
                ];
            });

        return response()->json(['expenses' => $expenses]);
    }

    public function getSales(Request $request)
    {
        $user = $request->user();
        $sales = Sale::where('store_id', $user->store_id)
            ->with(['items.product', 'customer'])
            ->latest()
            ->limit(100)
            ->get()
            ->map(function ($s) {
                return [
                    'local_id' => $s->uuid,
                    'server_id' => $s->id,
                    'sold_at' => $s->created_at->toIso8601String(),
                    'payment_method' => $s->payment_method,
                    'customer_uuid' => $s->customer?->uuid,
                    'customer_name' => $s->customer?->name,
                    'customer_phone' => $s->customer?->phone,
                    'notes' => $s->notes ?? '',
                    'discount' => (float) $s->discount,
                    'amount_paid' => (float) $s->amount_paid,
                    'change_given' => (float) $s->change_given,
                    'total_amount' => (float) $s->total_amount,
                    'final_amount' => (float) $s->final_amount,
                    'is_synced' => true,
                    'items' => $s->items->map(function ($i) {
                        return [
                            'product_id' => $i->product_id,
                            'product_name' => $i->product?->name ?? 'Produit Inconnu',
                            'quantity' => $i->quantity,
                            'unit_price' => (float) $i->unit_price,
                            'discount' => 0,
                            'subtotal' => (float) $i->subtotal,
                        ];
                    })
                ];
            });

        return response()->json(['sales' => $sales]);
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

                // Find customer id if uuid provided (check both uuid and local_id for robustness)
                $customerId = null;
                if (!empty($saleData['customer_uuid'])) {
                    $customerId = Customer::where('store_id', $user->store_id)
                        ->where(function ($q) use ($saleData) {
                            $q->where('uuid', $saleData['customer_uuid'])
                                ->orWhere('uuid', 'LIKE', $saleData['customer_uuid']); // Some older clients might send it differently
                        })->value('id');

                    if (!$customerId) {
                        // If not found by server UUID, maybe it's still using the mobile's local_id
                        // Note: Our sync stores local_id as 'uuid' in the database for new customers
                        $customerId = Customer::where('store_id', $user->store_id)
                            ->where('uuid', $saleData['customer_uuid'])
                            ->value('id');
                    }
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

                $saleId = null;
                if (!empty($pData['sale_uuid'])) {
                    $saleId = Sale::where('uuid', $pData['sale_uuid'])->value('id');
                }

                $payment = DebtPayment::create([
                    'store_id' => $user->store_id,
                    'user_id' => $user->id,
                    'customer_id' => $customerId,
                    'sale_id' => $saleId,
                    'uuid' => $pData['uuid'],
                    'amount' => $pData['amount'],
                    'payment_method' => $pData['payment_method'],
                    'paid_at' => $pData['paid_at'] ? date('Y-m-d H:i:s', strtotime($pData['paid_at'])) : now(),
                ]);

                // Update sale if linked
                if ($saleId) {
                    $sale = Sale::find($saleId);
                    if ($sale) {
                        $sale->increment('amount_paid', $pData['amount']);
                    }
                } else {
                    // Global payment (FIFO) - Optional: we could implement FIFO on backend too 
                    // but for now, the aggregate debt calculation on Customer model handles it.
                }
            }
        }

        // Process Cancelled Sales
        if (isset($changes['cancelled_sales'])) {
            foreach ($changes['cancelled_sales'] as $saleUuid) {
                $sale = Sale::where('uuid', $saleUuid)->where('status', '!=', 'cancelled')->first();
                if ($sale) {
                    DB::transaction(function () use ($sale, $user) {
                        $sale->update(['status' => 'cancelled']);

                        foreach ($sale->items as $item) {
                            $product = Product::find($item['product_id']);
                            if ($product) {
                                $before = $product->stock_quantity;
                                $product->increment('stock_quantity', $item['quantity']);
                                $after = $product->fresh()->stock_quantity;

                                // Log stock restoration
                                \App\Models\StockMovement::create([
                                    'store_id' => $user->store_id,
                                    'product_id' => $item['product_id'],
                                    'user_id' => $user->id,
                                    'type' => 'in',
                                    'quantity' => $item['quantity'],
                                    'quantity_before' => $before,
                                    'quantity_after' => $after,
                                    'reason' => 'Annulation Vente (Sync)',
                                    'reference' => $sale->sale_number,
                                    'reference_type' => 'sale',
                                ]);
                            }
                        }
                    });
                }
            }
        }

        return response()->json([
            'success' => true,
            'results' => [
                'customers' => array_map(fn($c) => ['local_id' => $c['uuid'], 'uuid' => $c['uuid']], $changes['customers'] ?? []),
                'sales' => array_map(fn($s) => ['local_id' => $s['uuid'], 'server_id' => 1], $changes['sales'] ?? []),
                'expenses' => array_map(fn($e) => ['local_id' => $e['uuid']], $changes['expenses'] ?? []),
                'debt_payments' => array_map(fn($p) => ['local_id' => $p['uuid']], $changes['debt_payments'] ?? []),
                'cancelled_sales' => $changes['cancelled_sales'] ?? [],
            ],
            'store' => $user->store ? [
                'id' => $user->store->id,
                'name' => $user->store->name,
                'currency' => $user->store->currency,
                'address' => $user->store->address,
                'phone' => $user->store->phone,
                'email' => $user->store->email,
                'logo' => $user->store->logo ? asset('storage/' . $user->store->logo) : null,
                'license_number' => $user->store->license_number,
            ] : null,
        ]);
    }

    public function getUsers(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $users = User::where('store_id', $user->store_id)
            ->with('role')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role?->name,
                    'is_active' => $u->is_active,
                ];
            });

        return response()->json(['users' => $users]);
    }

    public function getPurchases(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $purchases = \App\Models\Purchase::where('store_id', $user->store_id)
            ->with(['supplier', 'items.product'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'date' => $p->purchase_date->toIso8601String(),
                    'number' => $p->purchase_number,
                    'supplier_name' => $p->supplier?->name,
                    'total_amount' => (float) $p->total_amount,
                    'amount_paid' => (float) $p->amount_paid,
                    'status' => $p->status,
                    'items' => $p->items->map(function ($i) {
                        return [
                            'product_name' => $i->product?->name,
                            'quantity' => $i->quantity,
                            'unit_cost' => (float) $i->unit_cost,
                            'subtotal' => (float) $i->subtotal,
                        ];
                    }),
                ];
            });

        return response()->json(['purchases' => $purchases]);
    }

    public function getSuppliers(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $suppliers = \App\Models\Supplier::where('store_id', $user->store_id)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'phone' => $s->phone,
                    'email' => $s->email,
                    'address' => $s->address,
                ];
            });

        return response()->json(['suppliers' => $suppliers]);
    }

    public function storeSupplier(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $supplier = \App\Models\Supplier::create(array_merge($validated, ['store_id' => $user->store_id]));

        return response()->json(['supplier' => $supplier]);
    }

    public function updateSupplier(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $supplier = \App\Models\Supplier::where('store_id', $user->store_id)->findOrFail($id);
        $supplier->update($request->only(['name', 'phone', 'email', 'address']));

        return response()->json(['supplier' => $supplier]);
    }

    public function destroySupplier(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $supplier = \App\Models\Supplier::where('store_id', $user->store_id)->findOrFail($id);
        $supplier->delete();

        return response()->json(['message' => 'Fournisseur supprimé.']);
    }

    public function getInventories(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Get stock movements or current stock status with buying prices
        $products = Product::where('store_id', $user->store_id)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'stock' => $p->stock_quantity,
                    'buying_price' => $p->purchase_price,
                    'selling_price' => $p->selling_price,
                    'total_valuation' => $p->stock_quantity * $p->purchase_price,
                ];
            });

        return response()->json(['inventory' => $products]);
    }

    public function getAdminStats(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $today = now()->startOfDay();
        $store_id = $user->store_id;

        $sales = \App\Models\Sale::where('store_id', $store_id)
            ->where('created_at', '>=', $today)
            ->get();

        $revenue = $sales->sum('final_amount');
        $expenses = \App\Models\Expense::where('store_id', $store_id)
            ->where('created_at', '>=', $today)
            ->sum('amount');

        // Calculate Profit (Revenue - Cost of goods sold)
        $total_cost = 0;
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $total_cost += $item->quantity * ($item->product?->purchase_price ?? 0);
            }
        }

        $profit = $revenue - $total_cost - $expenses;

        // Global Stock Valuation
        $stock_valuation = \App\Models\Product::where('store_id', $store_id)
            ->get()
            ->sum(fn($p) => $p->stock_quantity * $p->purchase_price);

        return response()->json([
            'revenue' => (float) $revenue,
            'expenses' => (float) $expenses,
            'profit' => (float) $profit,
            'stock_valuation' => (float) $stock_valuation,
            'sales_count' => $sales->count(),
        ]);
    }

    public function getRoles(Request $request)
    {
        return response()->json(['roles' => \App\Models\Role::all(['id', 'name', 'slug'])]);
    }

    public function storeUser(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role_id' => $validated['role_id'],
            'store_id' => $user->store_id,
            'is_active' => true,
        ]);

        return response()->json(['user' => $newUser, 'message' => 'Utilisateur créé.']);
    }

    public function updateUser(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $target = User::where('store_id', $user->store_id)->findOrFail($id);
        $target->update($request->only(['name', 'email', 'role_id']));
        if ($request->filled('password')) {
            $target->update(['password' => bcrypt($request->password)]);
        }

        return response()->json(['user' => $target->fresh()->load('role'), 'message' => 'Utilisateur modifié.']);
    }

    public function toggleUser(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $target = User::where('store_id', $user->store_id)->findOrFail($id);
        $target->is_active = !$target->is_active;
        $target->save();

        return response()->json(['is_active' => $target->is_active]);
    }

    public function storeProduct(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'min_stock' => 'nullable|integer',
        ]);

        $product = Product::create(array_merge($validated, [
            'store_id' => $user->store_id,
            'status' => true,
        ]));

        return response()->json(['product' => $product]);
    }

    public function updateProduct(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role?->slug, ['admin', 'proprietaire'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $product = Product::where('store_id', $user->store_id)->findOrFail($id);
        $product->update($request->only(['name', 'purchase_price', 'selling_price', 'stock_quantity', 'min_stock']));

        return response()->json(['product' => $product]);
    }
}
