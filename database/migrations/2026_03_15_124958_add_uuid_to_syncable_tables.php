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
        Schema::table('sales', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('user_id')->index();
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('store_id')->index();
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('user_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
