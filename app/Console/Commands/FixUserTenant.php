<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixUserTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-user-tenant';

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
        
        if ($user && $tenant) {
            $user->tenant_id = $tenant->id;
            $user->save();
            
            $this->info("User {$user->name} associated with tenant {$tenant->name}");
        } else {
            $this->error("User or tenant not found");
        }
    }
}
