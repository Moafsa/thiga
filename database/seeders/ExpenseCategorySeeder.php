<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;
use App\Models\Tenant;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tenants
        $tenants = Tenant::all();

        $defaultCategories = [
            [
                'name' => 'Combustível',
                'description' => 'Gastos com combustível para veículos',
                'color' => '#FF6B35',
            ],
            [
                'name' => 'Salários',
                'description' => 'Pagamento de salários e encargos',
                'color' => '#4ECDC4',
            ],
            [
                'name' => 'Manutenção',
                'description' => 'Manutenção de veículos e equipamentos',
                'color' => '#45B7D1',
            ],
            [
                'name' => 'Pedágio',
                'description' => 'Gastos com pedágios',
                'color' => '#F9CA24',
            ],
            [
                'name' => 'Alimentação',
                'description' => 'Gastos com alimentação da equipe',
                'color' => '#6C5CE7',
            ],
            [
                'name' => 'Impostos',
                'description' => 'Impostos e taxas',
                'color' => '#A55EEA',
            ],
            [
                'name' => 'Seguro',
                'description' => 'Seguros de veículos e carga',
                'color' => '#26DE81',
            ],
            [
                'name' => 'Outros',
                'description' => 'Outras despesas',
                'color' => '#778CA3',
            ],
        ];

        foreach ($tenants as $tenant) {
            foreach ($defaultCategories as $category) {
                ExpenseCategory::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'name' => $category['name'],
                    ],
                    [
                        'description' => $category['description'],
                        'color' => $category['color'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}






















