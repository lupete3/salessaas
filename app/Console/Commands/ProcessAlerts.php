<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Product;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Console\Command;

class ProcessAlerts extends Command
{
    protected $signature = 'alerts:process';
    protected $description = 'Génère les alertes : stock faible, expiration proche, dettes fournisseurs';

    public function handle(): void
    {
        $stores = Store::where('subscription_status', 'active')->get();

        foreach ($stores as $store) {
            $this->processLowStock($store);
            $this->processExpiringSoon($store);
            $this->processSupplierDebts($store);
        }

        $this->info('Alertes traitées pour ' . $stores->count() . ' entreprise(s).');
    }

    private function processLowStock(Store $store): void
    {
        $lowStockProducts = Product::forStore($store->id)
            ->active()
            ->lowStock()
            ->get();

        foreach ($lowStockProducts as $product) {
            // Avoid duplicate alerts (one per product per day)
            $existing = Alert::forStore($store->id)
                ->where('type', 'low_stock')
                ->where('alertable_id', $product->id)
                ->where('alertable_type', Product::class)
                ->whereDate('created_at', today())
                ->exists();

            if (!$existing) {
                Alert::create([
                    'store_id' => $store->id,
                    'type' => 'low_stock',
                    'severity' => $product->stock_quantity == 0 ? 'danger' : 'warning',
                    'title' => 'Stock faible — ' . $product->name,
                    'message' => "Quantité restante : {$product->stock_quantity} (seuil : {$product->min_stock_alert})",
                    'alertable_id' => $product->id,
                    'alertable_type' => Product::class,
                ]);
            }
        }
    }

    private function processExpiringSoon(Store $store): void
    {
        $products = Product::forStore($store->id)
            ->active()
            ->expiringSoon(30)
            ->with('batches')
            ->get();

        foreach ($products as $product) {
            $nearestBatch = $product->batches
                ->where('quantity_remaining', '>', 0)
                ->where('expiry_date', '>=', now())
                ->sortBy('expiry_date')
                ->first();

            if (!$nearestBatch)
                continue;

            $daysLeft = $nearestBatch->daysUntilExpiry();

            $existing = Alert::forStore($store->id)
                ->where('type', 'expiry')
                ->where('alertable_id', $product->id)
                ->where('alertable_type', Product::class)
                ->whereDate('created_at', today())
                ->exists();

            if (!$existing) {
                Alert::create([
                    'store_id' => $store->id,
                    'type' => 'expiry',
                    'severity' => $daysLeft <= 7 ? 'danger' : 'warning',
                    'title' => 'Expiration proche — ' . $product->name,
                    'message' => "Lot {$nearestBatch->batch_number} expire dans {$daysLeft} jours",
                    'alertable_id' => $product->id,
                    'alertable_type' => Product::class,
                ]);
            }
        }
    }

    private function processSupplierDebts(Store $store): void
    {
        $suppliers = Supplier::forStore($store->id)
            ->where('balance_due', '>', 100)
            ->get();

        foreach ($suppliers as $supplier) {
            $existing = Alert::forStore($store->id)
                ->where('type', 'supplier_debt')
                ->where('alertable_id', $supplier->id)
                ->where('alertable_type', Supplier::class)
                ->whereDate('created_at', today())
                ->exists();

            if (!$existing) {
                Alert::create([
                    'store_id' => $store->id,
                    'type' => 'supplier_debt',
                    'severity' => $supplier->balance_due > 500 ? 'danger' : 'warning',
                    'title' => 'Dette fournisseur — ' . $supplier->name,
                    'message' => 'Solde dû : ' . number_format($supplier->balance_due, 2) . ' USD',
                    'alertable_id' => $supplier->id,
                    'alertable_type' => Supplier::class,
                ]);
            }
        }
    }
}
