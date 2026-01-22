<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'salesperson_id',
        'proposal_number',
        'title',
        'description',
        'weight',
        'height',
        'width',
        'length',
        'cubage',
        'base_value',
        'discount_percentage',
        'discount_value',
        'final_value',
        'status',
        'valid_until',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'notes',
        'attachments',
        'collection_requested',
        'collection_requested_at',
        'origin_address',
        'origin_city',
        'origin_state',
        'origin_zip_code',
        'origin_latitude',
        'origin_longitude',
        'destination_address',
        'destination_city',
        'destination_state',
        'destination_zip_code',
        'destination_latitude',
        'destination_longitude',
        'client_name',
        'client_whatsapp',
        'client_email',
        'destination_name',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set tenant_id from authenticated user if not set
        static::creating(function ($proposal) {
            // Se tenant_id não foi definido, obtém do usuário autenticado
            if (!$proposal->tenant_id) {
                $user = auth()->user();
                if ($user && $user->tenant) {
                    $proposal->tenant_id = $user->tenant->id;
                }
            }
        });
    }

    protected $casts = [
        'weight' => 'decimal:2',
        'height' => 'decimal:3',
        'width' => 'decimal:3',
        'length' => 'decimal:3',
        'cubage' => 'decimal:3',
        'base_value' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'final_value' => 'decimal:2',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'attachments' => 'array',
        'collection_requested' => 'boolean',
        'collection_requested_at' => 'datetime',
        'origin_latitude' => 'decimal:8',
        'origin_longitude' => 'decimal:8',
        'destination_latitude' => 'decimal:8',
        'destination_longitude' => 'decimal:8',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(Salesperson::class);
    }

    public function availableCargo(): HasOne
    {
        return $this->hasOne(AvailableCargo::class);
    }

    public function hasCollectionRequested(): bool
    {
        return $this->collection_requested === true;
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySalesperson($query, int $salespersonId)
    {
        return $query->where('salesperson_id', $salespersonId);
    }

    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isNegotiating(): bool
    {
        return $this->status === 'negotiating';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->valid_until && $this->valid_until->isPast());
    }

    public function canApplyDiscount(float $discountPercentage): bool
    {
        return $this->salesperson->canApplyDiscount($discountPercentage);
    }

    public function calculateDiscount(float $discountPercentage): array
    {
        $discountValue = ($this->base_value * $discountPercentage) / 100;
        $finalValue = $this->base_value - $discountValue;

        return [
            'discount_percentage' => $discountPercentage,
            'discount_value' => $discountValue,
            'final_value' => $finalValue,
        ];
    }

    public function getFormattedBaseValueAttribute(): string
    {
        return 'R$ ' . number_format($this->base_value, 2, ',', '.');
    }

    public function getFormattedFinalValueAttribute(): string
    {
        return 'R$ ' . number_format($this->final_value, 2, ',', '.');
    }

    public function getFormattedDiscountValueAttribute(): string
    {
        return 'R$ ' . number_format($this->discount_value, 2, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Rascunho',
            'sent' => 'Enviada',
            'negotiating' => 'Em Negociação',
            'accepted' => 'Aceita',
            'rejected' => 'Rejeitada',
            'expired' => 'Expirada',
            default => 'Desconhecido'
        };
    }
}
