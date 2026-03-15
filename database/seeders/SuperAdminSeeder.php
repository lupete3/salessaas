<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = \App\Models\Role::where('slug', \App\Models\Role::SUPERADMIN)->first();

        if ($role) {
            \App\Models\User::updateOrCreate(
                ['email' => 'admin@pharmasaas.cd'],
                [
                    'name' => 'Admin Global',
                    'password' => \Illuminate\Support\Facades\Hash::make('superadmin'),
                    'role_id' => $role->id,
                    'store_id' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
