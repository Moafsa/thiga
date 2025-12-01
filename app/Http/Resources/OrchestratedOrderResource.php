<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrchestratedOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->resource['data'] ?? [];
        $notifications = $data['notifications'] ?? [];
        $metadata = $data['metadata'] ?? [];
        $idempotent = $this->resource['idempotent'] ?? false;

        return [
            'success' => true,
            'idempotent' => $idempotent,
            'data' => [
                'tenant_id' => $data['tenant_id'] ?? null,
                'customer' => $data['customer'] ?? null,
                'proposal' => $data['proposal'] ?? null,
                'shipment' => $data['shipment'] ?? null,
                'route' => $data['route'] ?? null,
                'freight_breakdown' => $data['freight_breakdown'] ?? null,
                'notifications' => $notifications,
                'metadata' => $metadata,
            ],
        ];
    }
}















