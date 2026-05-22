<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SplitBilling extends Model
{
    use HasFactory;

    protected $table = 'split_billings';

    protected $fillable = [
        'tenant_id',
        'tenant_invoice_id',
        'reference_type',
        'reference_id',
        'base_amount',
        'split_percentage',
        'commission_amount',
        'status',
        'calculation_date',
        'invoice_date',
        'payment_date',
        'metadata',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'split_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'calculation_date' => 'date',
        'invoice_date' => 'date',
        'payment_date' => 'date',
        'metadata' => 'array',
    ];

    // ========== RELATIONSHIPS ==========

    /**
     * Get the tenant for this split billing
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the tenant invoice related to this split
     */
    public function tenantInvoice(): BelongsTo
    {
        return $this->belongsTo(TenantInvoice::class);
    }

    // ========== SCOPES ==========

    /**
     * Scope: Get pending split billings
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get calculated split billings
     */
    public function scopeCalculated($query)
    {
        return $query->where('status', 'calculated');
    }

    /**
     * Scope: Get invoiced split billings
     */
    public function scopeInvoiced($query)
    {
        return $query->where('status', 'invoiced');
    }

    /**
     * Scope: Get paid split billings
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope: Filter by tenant
     */
    public function scopeByTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by reference type
     */
    public function scopeByReferenceType($query, string $type)
    {
        return $query->where('reference_type', $type);
    }

    // ========== METHODS ==========

    /**
     * Mark split billing as calculated
     */
    public function markAsCalculated(): void
    {
        $this->update(['status' => 'calculated']);
    }

    /**
     * Mark split billing as invoiced
     */
    public function markAsInvoiced(): void
    {
        $this->update([
            'status' => 'invoiced',
            'invoice_date' => now(),
        ]);
    }

    /**
     * Mark split billing as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => now(),
        ]);
    }

    /**
     * Get formatted commission amount
     */
    public function getFormattedCommissionAttribute(): string
    {
        return 'R$ ' . number_format($this->commission_amount, 2, ',', '.');
    }

    /**
     * Get formatted base amount
     */
    public function getFormattedBaseAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->base_amount, 2, ',', '.');
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'calculated' => 'info',
            'invoiced' => 'primary',
            'paid' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get status display name in Portuguese
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'calculated' => 'Calculada',
            'invoiced' => 'Faturada',
            'paid' => 'Paga',
            default => 'Desconhecido'
        };
    }
}
