<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@thiga.com.br',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $superAdmin->assignRole('Super Admin');

        // Create Tenant Admin
        $tenant = Tenant::first();
        if ($tenant) {
            $tenantAdmin = User::create([
                'name' => 'Admin Thiga',
                'email' => 'admin@thiga.transportes.com',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'phone' => '+5511999999999',
                'is_active' => true,
            ]);
            $tenantAdmin->assignRole('Admin Tenant');

            // Create other users for the tenant
            $users = [
                [
                    'name' => 'João Silva',
                    'email' => 'joao@thiga.transportes.com',
                    'role' => 'Financeiro',
                    'phone' => '+5511888888888',
                ],
                [
                    'name' => 'Maria Santos',
                    'email' => 'maria@thiga.transportes.com',
                    'role' => 'Operacional',
                    'phone' => '+5511777777777',
                ],
                [
                    'name' => 'Pedro Costa',
                    'email' => 'pedro@thiga.transportes.com',
                    'role' => 'Vendedor',
                    'phone' => '+5511666666666',
                ],
            ];

            foreach ($users as $userData) {
                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password'),
                    'tenant_id' => $tenant->id,
                    'phone' => $userData['phone'],
                    'is_active' => true,
                ]);
                $user->assignRole($userData['role']);
            }
        }
    }
}























