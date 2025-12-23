<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'route_id',
        'driver_id',
        'sender_client_id',
        'receiver_client_id',
        'tracking_number',
        'tracking_code',
        'title',
        'description',
        'recipient_name',
        'recipient_address',
        'recipient_city',
        'recipient_state',
        'recipient_zip_code',
        'recipient_phone',
        'weight',
        'volume',
        'quantity',
        'value',              // Total invoice/goods value (used for route revenue calculation)
        'goods_value',        // Value of goods (synonym of 'value', kept for compatibility)
        'freight_value',      // Freight/shipping cost (may differ from 'value')
        'pickup_address',
        'pickup_city',
        'pickup_state',
        'pickup_zip_code',
        'pickup_latitude',
        'pickup_longitude',
        'delivery_address',
        'delivery_city',
        'delivery_state',
        'delivery_zip_code',
        'delivery_latitude',
        'delivery_longitude',
        'pickup_date',
        'pickup_time',
        'delivery_date',
        'delivery_time',
        'status',
        'picked_up_at',
        'delivered_at',
        'notes',
        'delivery_notes',
        'metadata',
        'cte_number',
        'cte_status',
        'invoice_number',
        'invoice_details',
        'dimensions',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'volume' => 'decimal:2',
        'value' => 'decimal:2',
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
        'pickup_date' => 'date',
        'delivery_date' => 'date',
        'pickup_time' => 'datetime',
        'delivery_time' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the tenant that owns the shipment.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the route that contains this shipment.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the driver assigned to this shipment.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the sender client.
     */
    public function senderClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'sender_client_id');
    }

    /**
     * Get the receiver client.
     */
    public function receiverClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'receiver_client_id');
    }

    /**
     * Get the fiscal documents (CT-e) for this shipment.
     */
    public function fiscalDocuments(): HasMany
    {
        return $this->hasMany(FiscalDocument::class);
    }

    /**
     * Get the CT-e for this shipment.
     */
    public function cte(): ?FiscalDocument
    {
        return $this->fiscalDocuments()
            ->where('document_type', 'cte')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Check if shipment has authorized CT-e.
     */
    public function hasAuthorizedCte(): bool
    {
        return $this->fiscalDocuments()
            ->where('document_type', 'cte')
            ->where('status', 'authorized')
            ->exists();
    }

    /**
     * Get delivery proofs for this shipment.
     */
    public function deliveryProofs(): HasMany
    {
        return $this->hasMany(DeliveryProof::class);
    }

    /**
     * Get timeline events for this shipment.
     */
    public function timeline(): HasMany
    {
        return $this->hasMany(ShipmentTimeline::class);
    }

    /**
     * Get invoice items for this shipment.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Check if shipment is invoiced.
     */
    public function isInvoiced(): bool
    {
        return $this->invoiceItems()->exists();
    }

    /**
     * Scope to filter shipments ready for invoicing (with authorized CT-e and not invoiced).
     */
    public function scopeReadyForInvoicing($query)
    {
        return $query->whereHas('fiscalDocuments', function ($q) {
            $q->where('document_type', 'cte')
              ->where('status', 'authorized');
        })->whereDoesntHave('invoiceItems');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter ready for routing (CT-e authorized).
     */
    public function scopeReadyForRouting($query)
    {
        return $query->whereHas('fiscalDocuments', function ($q) {
            $q->where('document_type', 'cte')
              ->where('status', 'authorized');
        });
    }
}

