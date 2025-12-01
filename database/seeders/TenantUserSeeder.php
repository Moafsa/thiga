<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default tenant
        $tenant = Tenant::create([
            'name' => 'Thiga Transportes',
            'cnpj' => '12.345.678/0001-90',
            'domain' => 'thigatransportes',
            'plan_id' => 1, // Assumindo que existe um plano com ID 1
            'is_active' => true,
            'trial_ends_at' => now()->addDays(30),
            'subscription_status' => 'trial',
        ]);

        // Create admin user
        $user = User::create([
            'name' => 'Administrador',
            'email' => 'admin@thigatransportes.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'phone' => '(11) 99999-9999',
            'is_active' => true,
        ]);

        // Assign admin role
        $user->assignRole('Admin Tenant');
    }
}
