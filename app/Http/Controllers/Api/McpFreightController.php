<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\FreightCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class McpFreightController extends Controller
{
    protected FreightCalculationService $freightService;

    public function __construct(FreightCalculationService $freightService)
    {
        $this->freightService = $freightService;
    }

    /**
     * Calculate freight via MCP API (for n8n integration)
     * 
     * POST /api/mcp/freight/calculate
     * 
     * Headers:
     *   X-Tenant-Token: {tenant_api_token}
     * 
     * Body:
     * {
     *   "destination": "BELO HORIZONTE - MG",
     *   "weight": 55.5,
     *   "cubage": 0.5,
     *   "invoice_value": 1500.00,
     *   "options": {
     *     "tde_markets": false,
     *     "pallets": 2,
     *     "is_weekend_or_holiday": false
     *   }
     * }
     */
    public function calculate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'destination' => 'required|string',
            'weight' => 'required|numeric|min:0',
            'cubage' => 'nullable|numeric|min:0',
            'invoice_value' => 'required|numeric|min:0',
            'options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Authenticate tenant via token
        $tenant = $this->authenticateTenant($request);
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Invalid or missing tenant token.',
            ], 401);
        }

        try {
            $result = $this->freightService->calculate(
                tenant: $tenant,
                destination: $request->destination,
                weight: (float)$request->weight,
                cubage: (float)($request->cubage ?? 0),
                invoiceValue: (float)$request->invoice_value,
                options: $request->options ?? []
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);

        } catch (\Exception $e) {
            Log::error('MCP Freight Calculation Error', [
                'tenant_id' => $tenant->id ?? null,
                'request' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available destinations for a tenant
     * 
     * GET /api/mcp/freight/destinations
     */
    public function destinations(Request $request)
    {
        $tenant = $this->authenticateTenant($request);
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Invalid or missing tenant token.',
            ], 401);
        }

        $destinations = \App\Models\FreightTable::where('tenant_id', $tenant->id)
            ->active()
            ->select('destination_name', 'destination_state', 'destination_type')
            ->distinct()
            ->get()
            ->map(function ($table) {
                return [
                    'name' => $table->destination_name,
                    'state' => $table->destination_state,
                    'type' => $table->destination_type,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $destinations,
        ], 200);
    }

    /**
     * Health check endpoint
     * 
     * GET /api/mcp/freight/health
     */
    public function health()
    {
        return response()->json([
            'success' => true,
            'status' => 'ok',
            'service' => 'freight-calculation-mcp',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    /**
     * Authenticate tenant via token
     */
    protected function authenticateTenant(Request $request): ?Tenant
    {
        // Try to get token from header
        $token = $request->header('X-Tenant-Token') 
              ?? $request->header('Authorization') 
              ?? $request->input('token');

        if (!$token) {
            return null;
        }

        // Remove 'Bearer ' prefix if present
        $token = str_replace('Bearer ', '', $token);

        // Find tenant by API token (hashed)
        $hashedToken = hash('sha256', $token);
        $tenant = Tenant::where('api_token', $hashedToken)->first();

        if ($tenant) {
            return $tenant;
        }

        // Fallback: Try to find by domain (for backward compatibility)
        $tenant = Tenant::where('domain', $token)->first();

        if ($tenant) {
            // Generate API token for tenants using domain authentication
            $tenant->generateApiToken();
            return $tenant;
        }

        return null;
    }
}
