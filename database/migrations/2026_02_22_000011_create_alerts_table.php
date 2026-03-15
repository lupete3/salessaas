<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['low_stock', 'expiry', 'supplier_debt', 'system']);
            $table->string('title');
            $table->text('message');
            $table->morphs('alertable'); // polymorphic: medicine, supplier, purchase
            $table->boolean('is_read')->default(false);
            $table->enum('severity', ['info', 'warning', 'danger'])->default('warning');
            $table->timestamps();

            $table->index(['pharmacy_id', 'is_read', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
