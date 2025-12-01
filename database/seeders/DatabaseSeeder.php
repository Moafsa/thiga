<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            RoleSeeder::class,
            TenantSeeder::class,
            UserSeeder::class,
            ExpenseCategorySeeder::class,
            FreightTableSeeder::class,
        ]);
    }
}


