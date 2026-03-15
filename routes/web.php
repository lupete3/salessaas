<?php

use App\Livewire\Dashboard;
use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Products\Form as ProductForm;
use App\Livewire\Pos\Sale as PosSale;
use App\Livewire\Pos\History as PosHistory;
use App\Livewire\Stock\Movements as StockMovements;
use App\Livewire\Stock\Adjust as StockAdjust;
use App\Livewire\Stock\InventoryIndex;
use App\Livewire\Stock\InventoryCreate;
use App\Livewire\Suppliers\Index as SuppliersIndex;
use App\Livewire\Suppliers\Form as SupplierForm;
use App\Livewire\Purchases\Index as PurchasesIndex;
use App\Livewire\Purchases\Form as PurchaseForm;
use App\Livewire\Finances\Expenses;
use App\Livewire\Finances\Journal;
use App\Livewire\Finances\Report as FinanceReport;
use App\Livewire\Reports\Daily as DailyReport;
use App\Livewire\Reports\Monthly as MonthlyReport;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Users\Form as UserForm;
use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Customers\Form as CustomerForm;
use App\Livewire\Customers\Dettes as CustomerDettes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

// ── Locale switcher ─────────────────────────────────────────────────────────
Route::get('/locale/{locale}', function (string $locale) {
    $supported = ['fr', 'en', 'sw'];
    if (in_array($locale, $supported)) {
        Session::put('locale', $locale);
        App::setLocale($locale);

        // Persist on user record if authenticated
        if ($user = auth()->user()) {
            $user->update(['locale' => $locale]);
        }
    }
    return back();
})->name('locale.switch');

// ── Welcome / Home ───────────────────────────────────────────────────────────
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

// ── Subscription expired page ────────────────────────────────────────────────
Route::get('/abonnement-expire', function () {
    return view('subscription.expired');
})->name('subscription.expired');

// ── Authenticated & tenant-protected routes ──────────────────────────────────
Route::middleware(['auth', 'verified', 'tenant'])->group(function () {

    // Dashboard
    Route::get('/tableau-de-bord', Dashboard::class)->name('dashboard');

    // ── Point de vente ────────────────────────────────────────────────────────
    Route::prefix('vente')->name('pos.')->group(function () {
        Route::get('/', PosSale::class)->name('sale');
        Route::get('/historique', PosHistory::class)->name('history');
        Route::get('/ticket/{sale}', [\App\Http\Controllers\Pos\ReceiptController::class, 'show'])->name('receipt');
    });

    // ── Produits ───────────────────────────────────────────────────────────────
    Route::prefix('produits')->name('products.')->group(function () {
        Route::get('/', ProductsIndex::class)->name('index');
        Route::get('/ajouter', ProductForm::class)->name('create');
        Route::get('/{product}/modifier', ProductForm::class)->name('edit');
    });

    // ── Stock ─────────────────────────────────────────────────────────────────
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/mouvements', StockMovements::class)->name('movements');
        Route::get('/ajustement', StockAdjust::class)->name('adjust');
        Route::get('/inventaire', InventoryIndex::class)->name('inventory.index');
        Route::get('/inventaire/nouveau', InventoryCreate::class)->name('inventory.create');
    });

    // ── Fournisseurs ──────────────────────────────────────────────────────────
    Route::prefix('fournisseurs')->name('suppliers.')->group(function () {
        Route::get('/', SuppliersIndex::class)->name('index');
        Route::get('/ajouter', SupplierForm::class)->name('create');
        Route::get('/{supplier}/modifier', SupplierForm::class)->name('edit');
    });

    // ── Achats ────────────────────────────────────────────────────────────────
    Route::prefix('achats')->name('purchases.')->group(function () {
        Route::get('/', PurchasesIndex::class)->name('index');
        Route::get('/ajouter', PurchaseForm::class)->name('create');
        Route::get('/{purchase}/modifier', PurchaseForm::class)->name('edit');
    });

    // ── Finances ──────────────────────────────────────────────────────────────
    Route::prefix('finances')->name('finances.')->group(function () {
        Route::get('/depenses', Expenses::class)->name('expenses');
        Route::get('/journal', Journal::class)->name('journal');
        Route::get('/rapport', FinanceReport::class)->name('report');
    });

    // ── Rapports ──────────────────────────────────────────────────────────────
    Route::prefix('rapports')->name('reports.')->group(function () {
        Route::get('/', \App\Livewire\Reports\Index::class)->name('index');
        Route::get('/journalier', DailyReport::class)->name('daily');
        Route::get('/mensuel', MonthlyReport::class)->name('monthly');
    });

    // ── Clients & Dettes ──────────────────────────────────────────────────────
    Route::prefix('clients')->name('customers.')->group(function () {
        Route::get('/', CustomersIndex::class)->name('index');
        Route::get('/ajouter', CustomerForm::class)->name('create');
        Route::get('/{customer}/modifier', CustomerForm::class)->name('edit');
        Route::get('/dettes', CustomerDettes::class)->name('dettes');
    });

    // ── Utilisateurs (owner only) ─────────────────────────────────────────────
    Route::prefix('utilisateurs')->name('users.')->middleware('role:proprietaire')->group(function () {
        Route::get('/', UsersIndex::class)->name('index');
        Route::get('/ajouter', UserForm::class)->name('create');
        Route::get('/{user}/modifier', UserForm::class)->name('edit');
    });
});

// ── Master Management (Super Admin ONLY) ──────────────────────────────────
Route::middleware(['auth', 'role:superadmin', 'tenant'])->prefix('master')->name('admin.')->group(function () {
    Route::get('/tableau-de-bord', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
    Route::get('/stores', \App\Livewire\Admin\Stores::class)->name('stores');
});

require __DIR__ . '/settings.php';
