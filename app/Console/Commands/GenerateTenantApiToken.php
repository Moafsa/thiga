<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateTenantApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:generate-api-token {tenant? : The tenant ID or domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API token for a tenant (for MCP freight API)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantIdentifier = $this->argument('tenant');

        if (!$tenantIdentifier) {
            // List all tenants and ask user to select
            $tenants = Tenant::all();
            
            if ($tenants->isEmpty()) {
                $this->error('No tenants found. Please create a tenant first.');
                return 1;
            }

            $tenantChoices = $tenants->map(function ($tenant) {
                return "{$tenant->id} - {$tenant->name} ({$tenant->domain})";
            })->toArray();

            $selected = $this->choice('Select a tenant:', $tenantChoices);
            $tenantId = (int) explode(' - ', $selected)[0];
            $tenant = Tenant::find($tenantId);
        } else {
            // Find tenant by ID or domain
            $tenant = is_numeric($tenantIdentifier) 
                ? Tenant::find($tenantIdentifier)
                : Tenant::where('domain', $tenantIdentifier)->first();
        }

        if (!$tenant) {
            $this->error('Tenant not found.');
            return 1;
        }

        // Generate token
        $token = $tenant->generateApiToken();
        
        $this->info("API Token generated successfully for tenant: {$tenant->name}");
        $this->line('');
        $this->line("Token: <fg=green>{$token}</>");
        $this->line('');
        $this->line('Use this token in your API requests:');
        $this->line('Header: X-Tenant-Token: ' . $token);
        $this->line('');
        $this->warn('⚠️  Save this token securely. It will not be shown again.');

        return 0;
    }
}
