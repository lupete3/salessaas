<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename Tables
        Schema::rename('pharmacies', 'stores');
        Schema::rename('medicines', 'products');
        Schema::rename('medicine_batches', 'product_batches');

        // 2. Rename Columns (Foreign Keys)

        // Users
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('pharmacy_id', 'store_id');
        });

        // Suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            $table->renameColumn('pharmacy_id', 'store_id');
        });

        // Products (was medicines)
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('pharmacy_id', 'store_id');
            $table->renameColumn('scientific_name', 'generic_name');
        });

        // Product Batches (was medicine_batches)
        Schema::table('product_batches', function (Blueprint $table) {
            $table->renameColumn('pharmacy_id', 'store_id');
            $table->renameColumn('medicine_id', 'product_id');
        });

        // Stock Movements
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->renameColumn('pharmacy_id', 'store_id');
            $table->renameColumn('medicine_id', 'product_id');
        });

        // Sales
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('pharmacy_id', 'store_id');
        });

        // Sale Items
        Schema::table('sale_items', function (Blueprint $table) {
            $table->renameColumn('medicine_id', 'product_id');
        });

        // Purchases
        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('pharmacy_id', 'store_id');
        });

        // Purchase Items
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->renameColumn('medicine_id', 'product_id');
        });

        // Expenses
        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('pharmacy_id', 'store_id');
        });

        // Alerts
        Schema::table('alerts', function (Blueprint $table) {
            $table->renameColumn('pharmacy_id', 'store_id');
        });

        // Customers
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('pharmacy_id', 'store_id');
        });
    }

    public function down(): void
    {
        // Reverse Logic
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('store_id', 'pharmacy_id');
        });
        Schema::table('alerts', function (Blueprint $table) {
            $table->renameColumn('store_id', 'pharmacy_id');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('store_id', 'pharmacy_id');
        });
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->renameColumn('product_id', 'medicine_id');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('store_id', 'pharmacy_id');
        });
        Schema::table('sale_items', function (Blueprint $table) {
            $table->renameColumn('product_id', 'medicine_id');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('store_id', 'pharmacy_id');
        });
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->renameColumn('product_id', 'medicine_id');
            $table->renameColumn('store_id', 'pharmacy_id');
        });
        Schema::table('product_batches', function (Blueprint $table) {
            $table->renameColumn('product_id', 'medicine_id');
            $table->renameColumn('store_id', 'pharmacy_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('store_id', 'pharmacy_id');
            $table->renameColumn('generic_name', 'scientific_name');
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->renameColumn('store_id', 'pharmacy_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('store_id', 'pharmacy_id');
        });

        Schema::rename('product_batches', 'medicine_batches');
        Schema::rename('products', 'medicines');
        Schema::rename('stores', 'pharmacies');
    }
};
