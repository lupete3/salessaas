<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('scientific_name')->nullable();
            $table->string('category')->nullable();         // antibiotique, antalgique, etc.
            $table->string('form')->nullable();              // comprimé, sirop, injection
            $table->string('dosage')->nullable();            // 500mg, 5ml
            $table->string('barcode')->nullable()->unique();
            $table->string('unit')->default('comprimé');    // unité de vente
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_alert')->default(10); // seuil d'alerte stock faible
            $table->boolean('requires_prescription')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
