<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Plan;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan = Plan::where('name', 'Profissional')->first();
        
        if ($plan) {
            Tenant::create([
                'name' => 'Transportadora Thiga',
                'cnpj' => '12.345.678/0001-90',
                'domain' => 'thiga.transportes.com',
                'plan_id' => $plan->id,
                'is_active' => true,
                'trial_ends_at' => now()->addDays(30),
                'subscription_status' => 'trial',
            ]);
        }
    }
}























