<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\ShipmentTimelineService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DeliveryConfirmationController extends Controller
{
    protected ShipmentTimelineService $timelineService;

    public function __construct(ShipmentTimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
    }

    /**
     * Confirm delivery via tracking number and token
     */
    public function confirm(Request $request, string $trackingNumber): JsonResponse
    {
        try {
            $request->validate([
                'token' => 'required|string',
                'confirmed' => 'required|boolean',
                'notes' => 'nullable|string|max:500',
            ]);

            $shipment = Shipment::where('tracking_number', $trackingNumber)
                ->orWhere('tracking_code', $trackingNumber)
                ->first();

            if (!$shipment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipment not found'
                ], 404);
            }

            // Verify token (simple token validation - can be improved)
            $expectedToken = md5($shipment->tracking_number . $shipment->id . config('app.key'));
            if ($request->token !== $expectedToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token'
                ], 403);
            }

            if ($request->confirmed) {
                // Record confirmation in timeline
                $this->timelineService->recordEvent(
                    $shipment,
                    'delivered',
                    "Entrega confirmada pelo cliente" . ($request->notes ? ": {$request->notes}" : ""),
                    "{$shipment->delivery_city}/{$shipment->delivery_state}",
                    null,
                    null,
                    ['confirmed_by' => 'client', 'notes' => $request->notes]
                );

                Log::info('Delivery confirmed by client', [
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $trackingNumber,
                    'notes' => $request->notes,
                ]);
            } else {
                // Record problem report
                $this->timelineService->recordEvent(
                    $shipment,
                    'exception',
                    "Problema reportado pelo cliente: " . ($request->notes ?? 'Sem detalhes'),
                    "{$shipment->delivery_city}/{$shipment->delivery_state}",
                    null,
                    null,
                    ['reported_by' => 'client', 'notes' => $request->notes]
                );

                Log::warning('Delivery problem reported by client', [
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $trackingNumber,
                    'notes' => $request->notes,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $request->confirmed ? 'Delivery confirmed successfully' : 'Problem reported successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Delivery confirmation error', [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}











