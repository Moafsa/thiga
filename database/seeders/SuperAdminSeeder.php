<?php

namespace Database\Seeders;

use App\Models\SuperAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        SuperAdmin::firstOrCreate(
            ['email' => 'superadmin@conext.click'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('SuperAdmin@2026!'),
            ]
        );
    }
}
