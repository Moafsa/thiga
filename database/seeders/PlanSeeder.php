<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Básico',
                'description' => 'Plano ideal para pequenas transportadoras',
                'price' => 99.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    'basic_tracking',
                    'email_support',
                    'basic_reports',
                    'user_management'
                ],
                'limits' => [
                    'max_users' => 5,
                    'max_shipments' => 100,
                    'max_clients' => 50,
                    'storage_gb' => 1
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Profissional',
                'description' => 'Plano completo para transportadoras em crescimento',
                'price' => 199.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    'advanced_tracking',
                    'whatsapp_ai',
                    'fiscal_integration',
                    'api_access',
                    'advanced_reports',
                    'route_optimization',
                    'priority_support'
                ],
                'limits' => [
                    'max_users' => 15,
                    'max_shipments' => 500,
                    'max_clients' => 200,
                    'storage_gb' => 5
                ],
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Empresarial',
                'description' => 'Plano premium para grandes transportadoras',
                'price' => 399.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    'all_features',
                    'priority_support',
                    'custom_integrations',
                    'white_label',
                    'dedicated_support',
                    'custom_reports',
                    'advanced_analytics'
                ],
                'limits' => [
                    'max_users' => 50,
                    'max_shipments' => 2000,
                    'max_clients' => 1000,
                    'storage_gb' => 20
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
