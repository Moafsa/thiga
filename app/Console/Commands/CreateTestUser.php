<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateTestUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-test-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenant = \App\Models\Tenant::first();
        
        // Create test user
        $user = \App\Models\User::create([
            'name' => 'Teste User',
            'email' => 'teste@teste.com',
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
            'tenant_id' => $tenant->id,
            'phone' => '(11) 99999-9999',
            'is_active' => true,
        ]);
        
        // Assign role
        $user->assignRole('Admin Tenant');
        
        $this->info("✅ Test user created!");
        $this->info("Email: teste@teste.com");
        $this->info("Password: 123456");
    }
}
