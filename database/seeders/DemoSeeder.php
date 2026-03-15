<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\Role;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $ownerRole = Role::where('slug', Role::OWNER)->first();
        if (!$ownerRole) {
            $ownerRole = Role::create([
                'name' => 'Propriétaire',
                'slug' => Role::OWNER,
                'permissions' => Role::getPermissionsFor(Role::OWNER),
            ]);
        }

        $managerRole = Role::where('slug', Role::MANAGER)->first();
        if (!$managerRole) {
            $managerRole = Role::create([
                'name' => 'Gérant',
                'slug' => Role::MANAGER,
                'permissions' => Role::getPermissionsFor(Role::MANAGER),
            ]);
        }

        $sellerRole = Role::where('slug', Role::SELLER)->first();
        if (!$sellerRole) {
            $sellerRole = Role::create([
                'name' => 'Vendeur',
                'slug' => Role::SELLER,
                'permissions' => Role::getPermissionsFor(Role::SELLER),
            ]);
        }

        // 2. Store
        $store = Store::create([
            'name' => 'Kivu Sales & Services',
            'phone' => '+243990000001',
            'email' => 'contact@kivu-sales.cd',
            'address' => 'Av. Patrice Lumumba, Bukavu, RDC',
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addYear(),
            'currency' => 'USD',
            'locale' => 'fr',
        ]);

        // 3. Users
        $owner = User::create([
            'store_id' => $store->id,
            'role_id' => $ownerRole->id,
            'name' => 'Placide Bourgeois',
            'email' => 'placide@salessaas.cd',
            'password' => Hash::make('password'),
            'phone' => '+243998877665',
            'is_active' => true,
            'locale' => 'fr',
        ]);

        $seller = User::create([
            'store_id' => $store->id,
            'role_id' => $sellerRole->id,
            'name' => 'Justin Marchand',
            'email' => 'justin@salessaas.cd',
            'password' => Hash::make('password'),
            'phone' => '+243887766554',
            'is_active' => true,
            'locale' => 'fr',
        ]);

        // 4. Suppliers
        $supplier1 = Supplier::create([
            'store_id' => $store->id,
            'name' => 'Wholesale Depot Kinshasa',
            'phone' => '+243810000001',
            'email' => 'orders@wdk.cd',
            'address' => 'Gombe, Kinshasa',
            'contact_person' => 'Mr. Kabamba',
        ]);

        // 5. Products
        $prods = [
            ['name' => 'Smartphone Galaxy A14', 'category' => 'Électronique', 'form' => 'Unité', 'price' => 150.0],
            ['name' => 'Laptop HP 250 G8', 'category' => 'Informatique', 'form' => 'Unité', 'price' => 450.0],
            ['name' => 'Clé USB 64GB', 'category' => 'Accessoires', 'form' => 'Unité', 'price' => 15.0],
            ['name' => 'Écran 24" Dell', 'category' => 'Informatique', 'form' => 'Unité', 'price' => 180.0],
            ['name' => 'Souris Sans Fil Logi', 'category' => 'Accessoires', 'form' => 'Unité', 'price' => 25.0],
        ];

        foreach ($prods as $p) {
            $product = Product::create([
                'store_id' => $store->id,
                'supplier_id' => $supplier1->id,
                'name' => $p['name'],
                'category' => $p['category'],
                'form' => $p['form'],
                'unit' => strtolower($p['form']),
                'purchase_price' => $p['price'] * 0.75,
                'selling_price' => $p['price'],
                'stock_quantity' => 50,
                'min_stock_alert' => 10,
            ]);

            // Add an initial batch
            ProductBatch::create([
                'store_id' => $store->id,
                'product_id' => $product->id,
                'batch_number' => 'LOT-' . rand(1000, 9999),
                'quantity' => 50,
                'quantity_remaining' => 50,
                'expiry_date' => now()->addYears(rand(1, 3))->toDateString(),
                'purchase_price' => $p['price'] * 0.75,
                'selling_price' => $p['price'],
            ]);
        }
    }
}
