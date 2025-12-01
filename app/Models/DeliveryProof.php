<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'shipment_id',
        'driver_id',
        'proof_type',
        'description',
        'latitude',
        'longitude',
        'address',
        'city',
        'state',
        'photos',
        'documents',
        'recipient_name',
        'recipient_document',
        'recipient_signature',
        'delivery_time',
        'status',
        'rejection_reason',
        'metadata',
        'device_info',
        'app_version',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'photos' => 'array',
        'documents' => 'array',
        'delivery_time' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('proof_type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            default => 'Desconhecido'
        };
    }

    public function getFormattedLocationAttribute(): string
    {
        if ($this->address) {
            return "{$this->address}, {$this->city}/{$this->state}";
        }

        return "Lat: {$this->latitude}, Lng: {$this->longitude}";
    }

    public function getPhotosCountAttribute(): int
    {
        return count($this->photos ?? []);
    }

    public function getDocumentsCountAttribute(): int
    {
        return count($this->documents ?? []);
    }
}
