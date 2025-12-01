<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckUserTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-user-tenant';

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
        $user = \App\Models\User::first();
        $tenant = \App\Models\Tenant::first();
        
        $this->info("User: {$user->name} (ID: {$user->id})");
        $this->info("Tenant ID: " . ($user->tenant_id ?? 'NULL'));
        $this->info("Tenant: {$tenant->name} (ID: {$tenant->id})");
        
        if (!$user->tenant_id) {
            $user->tenant_id = $tenant->id;
            $user->save();
            $this->info("✅ User associated with tenant!");
        } else {
            $this->info("✅ User already has tenant!");
        }
        
        // Check if user has role
        if (!$user->hasRole('Admin Tenant')) {
            $user->assignRole('Admin Tenant');
            $this->info("✅ Role assigned to user!");
        } else {
            $this->info("✅ User already has role!");
        }
    }
}
