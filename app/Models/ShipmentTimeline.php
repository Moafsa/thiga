<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'event_type',
        'description',
        'occurred_at',
        'location',
        'latitude',
        'longitude',
        'metadata',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'metadata' => 'array',
    ];

    /**
     * Get the shipment that owns the timeline event.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Scope to filter by event type.
     */
    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope to order by occurred_at descending.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('occurred_at', 'desc');
    }

    /**
     * Get human-readable event type label.
     */
    public function getEventTypeLabelAttribute(): string
    {
        return match($this->event_type) {
            'created' => 'Encomenda Criada',
            'collected' => 'Coletado',
            'in_transit' => 'Em Trânsito',
            'out_for_delivery' => 'Saiu para Entrega',
            'delivery_attempt' => 'Tentativa de Entrega',
            'delivered' => 'Entregue',
            'exception' => 'Ocorrência',
            'cte_issued' => 'CT-e Emitido',
            'cte_authorized' => 'CT-e Autorizado',
            'mdfe_issued' => 'MDF-e Emitido',
            'mdfe_authorized' => 'MDF-e Autorizado',
            default => ucfirst(str_replace('_', ' ', $this->event_type))
        };
    }
}

