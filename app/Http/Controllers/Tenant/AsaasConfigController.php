<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AsaasConfigController extends Controller
{
    /**
     * Display the Asaas configuration form
     */
    public function edit()
    {
        $tenant = auth()->user()->tenant;

        return view('tenant.settings.asaas', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Update Asaas configuration for the tenant
     */
    public function update(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $validated = $request->validate([
            'uses_own_asaas' => 'boolean',
            'asaas_api_key' => 'nullable|string|min:10',
            'asaas_webhook_token' => 'nullable|string|min:10',
            'asaas_account_id' => 'nullable|string|max:255',
            'bank_account_config' => 'nullable|array',
            'bank_account_config.bank_code' => 'nullable|string|max:3',
            'bank_account_config.agency' => 'nullable|string|max:10',
            'bank_account_config.account_number' => 'nullable|string|max:20',
            'bank_account_config.is_checking' => 'nullable|boolean',
        ]);

        // If tenant wants to use their own Asaas, validate credentials
        if ($validated['uses_own_asaas'] ?? false) {
            try {
                // Create a temporary service instance to validate credentials
                $testService = new AsaasService(
                    apiKey: $validated['asaas_api_key'],
                    webhookToken: $validated['asaas_webhook_token']
                );

                // If we got here, credentials are valid (at least basic structure)
                Log::info('Asaas credentials validated for tenant', [
                    'tenant_id' => $tenant->id,
                    'account_id' => $validated['asaas_account_id'],
                ]);

            } catch (\Exception $e) {
                Log::warning('Asaas credentials validation failed', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);

                return back()
                    ->withInput($request->input())
                    ->withErrors([
                        'asaas_api_key' => 'Invalid Asaas credentials: ' . $e->getMessage()
                    ]);
            }
        }

        // Update tenant with Asaas configuration
        $tenant->update($validated);

        Log::info('Tenant Asaas configuration updated', [
            'tenant_id' => $tenant->id,
            'uses_own_asaas' => $validated['uses_own_asaas'],
        ]);

        return back()->with('success', 'Asaas configuration updated successfully!');
    }

    /**
     * Disconnect Asaas from tenant account
     */
    public function disconnect()
    {
        $tenant = auth()->user()->tenant;

        $tenant->update([
            'uses_own_asaas' => false,
            'asaas_api_key' => null,
            'asaas_webhook_token' => null,
            'asaas_account_id' => null,
            'bank_account_config' => null,
        ]);

        Log::info('Tenant Asaas configuration disconnected', [
            'tenant_id' => $tenant->id,
        ]);

        return back()->with('success', 'Asaas disconnected from your account.');
    }
}
