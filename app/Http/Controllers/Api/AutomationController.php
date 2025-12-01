<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrchestrateOrderRequest;
use App\Http\Resources\OrchestratedOrderResource;
use App\Models\Tenant;
use App\Services\OrderOrchestrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AutomationController extends Controller
{
    public function __construct(
        protected OrderOrchestrationService $orderOrchestrationService
    ) {
    }

    /**
     * Handle orchestration workflow.
     */
    public function order(OrchestrateOrderRequest $request): JsonResponse
    {
        $tenant = $this->authenticateTenant($request);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Invalid or missing tenant token.',
            ], 401);
        }

        try {
            $idempotencyKey = $request->header('Idempotency-Key');
            $result = $this->orderOrchestrationService->orchestrate(
                tenant: $tenant,
                payload: $request->validated(),
                idempotencyKey: $idempotencyKey
            );

            $status = $result['idempotent'] ? 200 : 201;

            return (new OrchestratedOrderResource($result))
                ->response()
                ->setStatusCode($status);
        } catch (\Throwable $e) {
            Log::error('Automation order failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to orchestrate order. ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function authenticateTenant(Request $request): ?Tenant
    {
        $token = $request->header('X-Tenant-Token')
            ?? $request->bearerToken()
            ?? $request->input('token');

        if (!$token) {
            return null;
        }

        $token = str_replace('Bearer ', '', $token);
        $hashedToken = hash('sha256', $token);

        $tenant = Tenant::where('api_token', $hashedToken)->first();

        if ($tenant) {
            return $tenant;
        }

        $tenant = Tenant::where('domain', $token)->first();

        if ($tenant) {
            $tenant->generateApiToken();
            return $tenant;
        }

        return null;
    }
}















