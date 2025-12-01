<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'document_type',
        'shipment_id',
        'route_id',
        'mitt_id',
        'mitt_number',
        'access_key',
        'status',
        'xml',
        'pdf_url',
        'xml_url',
        'error_message',
        'error_details',
        'mitt_response',
        'sent_at',
        'authorized_at',
        'cancelled_at',
    ];

    protected $casts = [
        'error_details' => 'array',
        'mitt_response' => 'array',
        'sent_at' => 'datetime',
        'authorized_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the fiscal document.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the shipment associated with the fiscal document (for CT-e).
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Get the route associated with the fiscal document (for MDF-e).
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Check if document is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if document is processing.
     */
    public function isProcessing(): bool
    {
        return in_array($this->status, ['validating', 'processing']);
    }

    /**
     * Check if document is authorized.
     */
    public function isAuthorized(): bool
    {
        return $this->status === 'authorized';
    }

    /**
     * Check if document has error.
     */
    public function hasError(): bool
    {
        return in_array($this->status, ['rejected', 'error']);
    }

    /**
     * Check if document is CT-e.
     */
    public function isCte(): bool
    {
        return $this->document_type === 'cte';
    }

    /**
     * Check if document is MDF-e.
     */
    public function isMdfe(): bool
    {
        return $this->document_type === 'mdfe';
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'validating' => 'Validating',
            'processing' => 'Processing',
            'authorized' => 'Authorized',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            'error' => 'Error',
            default => 'Unknown'
        };
    }

    /**
     * Scope to filter by document type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter CT-e documents.
     */
    public function scopeCte($query)
    {
        return $query->where('document_type', 'cte');
    }

    /**
     * Scope to filter MDF-e documents.
     */
    public function scopeMdfe($query)
    {
        return $query->where('document_type', 'mdfe');
    }
}






















