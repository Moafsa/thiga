<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'base_value' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'final_value' => 'decimal:2',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'attachments' => 'array',
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
