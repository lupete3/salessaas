<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('roles')->insertOrIgnore([
            [
                'name' => 'Super Administrateur',
                'slug' => 'superadmin',
                'permissions' => json_encode(['dashboard', 'pharmacies', 'settings']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pharmacien',
                'slug' => 'pharmacien',
                'permissions' => json_encode(['dashboard', 'pos', 'medicines', 'stock', 'suppliers', 'purchases', 'finances', 'reports']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Caissier',
                'slug' => 'caissier',
                'permissions' => json_encode(['dashboard', 'pos', 'medicines']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->whereIn('slug', ['superadmin', 'pharmacien', 'caissier'])->delete();
    }
};
