<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    /**
     * Track shipment by tracking number
     */
    public function track(Request $request): JsonResponse
    {
        try {
            $trackingNumber = $request->input('tracking_number') ?? $request->input('tracking_code');

            if (!$trackingNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tracking number is required'
                ], 400);
            }

            $shipment = Shipment::where('tracking_number', $trackingNumber)
                ->with(['senderClient', 'receiverClient', 'route', 'driver'])
                ->first();

            if (!$shipment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_number' => $shipment->tracking_number,
                    'status' => $shipment->status,
                    'status_description' => $this->getStatusDescription($shipment->status),
                    'sender' => [
                        'name' => $shipment->senderClient->name ?? 'N/A',
                        'city' => $shipment->pickup_city ?? 'N/A',
                        'state' => $shipment->pickup_state ?? 'N/A',
                    ],
                    'receiver' => [
                        'name' => $shipment->receiverClient->name ?? 'N/A',
                        'city' => $shipment->delivery_city ?? 'N/A',
                        'state' => $shipment->delivery_state ?? 'N/A',
                    ],
                    'weight' => $shipment->weight,
                    'volume' => $shipment->volume,
                    'quantity' => $shipment->quantity,
                    'value' => $shipment->value,
                    'created_at' => $shipment->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $shipment->updated_at->format('Y-m-d H:i:s'),
                    'delivered_at' => $shipment->delivered_at?->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Tracking API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get status description in Portuguese
     */
    protected function getStatusDescription(string $status): string
    {
        $descriptions = [
            'pending' => 'Aguardando coleta',
            'scheduled' => 'Agendado',
            'picked_up' => 'Coletado',
            'in_transit' => 'Em trânsito',
            'out_for_delivery' => 'Saiu para entrega',
            'delivered' => 'Entregue',
            'exception' => 'Ocorrência',
            'returned' => 'Devolvido',
            'cancelled' => 'Cancelado',
        ];

        return $descriptions[$status] ?? 'Status desconhecido';
    }

    /**
     * Get shipment history (using timeline)
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $trackingNumber = $request->input('tracking_number') ?? $request->input('tracking_code');

            if (!$trackingNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tracking number is required'
                ], 400);
            }

            $shipment = Shipment::where('tracking_number', $trackingNumber)
                ->with(['timeline' => function ($query) {
                    $query->orderBy('occurred_at', 'desc');
                }])
                ->first();

            if (!$shipment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipment not found'
                ], 404);
            }

            $timelineService = app(\App\Services\ShipmentTimelineService::class);
            $timeline = $timelineService->getPublicTimeline($shipment);

            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_number' => $shipment->tracking_number,
                    'current_status' => $shipment->status,
                    'current_status_description' => $this->getStatusDescription($shipment->status),
                    'history' => $timeline
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Shipment history API error', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get shipment timeline
     */
    public function timeline(Request $request, string $trackingNumber): JsonResponse
    {
        try {
            $shipment = Shipment::where('tracking_number', $trackingNumber)
                ->orWhere('tracking_code', $trackingNumber)
                ->first();

            if (!$shipment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipment not found'
                ], 404);
            }

            $timelineService = app(\App\Services\ShipmentTimelineService::class);
            $timeline = $timelineService->getPublicTimeline($shipment);

            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_number' => $shipment->tracking_number,
                    'status' => $shipment->status,
                    'status_description' => $this->getStatusDescription($shipment->status),
                    'timeline' => $timeline,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}













