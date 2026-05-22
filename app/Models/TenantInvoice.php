<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'invoice_number',
        'type',
        'base_amount',
        'split_percentage',
        'split_amount',
        'total_amount',
        'period_start',
        'period_end',
        'issue_date',
        'due_date',
        'status',
        'asaas_invoice_id',
        'asaas_payment_id',
        'metadata',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'split_percentage' => 'decimal:2',
        'split_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'issue_date' => 'date',
        'due_date' => 'date',
        'metadata' => 'array',
    ];

    // ========== RELATIONSHIPS ==========

    /**
     * Get the tenant that owns this invoice
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the subscription related to this invoice
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    // ========== SCOPES ==========

    /**
     * Scope: Get overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('due_date', '<', now());
    }

    /**
     * Scope: Get pending invoices (not paid yet)
     */
    public function scopePending($query)
    {
        return $query->where('status', 'issued')
            ->where('due_date', '>=', now());
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope: Get invoices by tenant
     */
    public function scopeByTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // ========== METHODS ==========

    /**
     * Generate unique invoice number
     * Format: TI-YYYY-MM-XXXXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        $lastInvoice = self::where('invoice_number', 'like', "TI-{$year}-{$month}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        $sequence = 1;
        if ($lastInvoice) {
            $lastSequence = (int) substr($lastInvoice->invoice_number, -6);
            $sequence = $lastSequence + 1;
        }

        return sprintf('TI-%s-%s-%06d', $year, $month, $sequence);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }

    /**
     * Mark invoice as overdue
     */
    public function markAsOverdue(): void
    {
        $this->update(['status' => 'overdue']);
    }

    /**
     * Cancel this invoice
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' &&
               $this->status !== 'cancelled' &&
               $this->due_date->isPast();
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice is open (awaiting payment)
     */
    public function isOpen(): bool
    {
        return $this->status === 'issued' &&
               $this->due_date->isFuture();
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->total_amount, 2, ',', '.');
    }

    /**
     * Get formatted split/commission amount
     */
    public function getFormattedCommissionAttribute(): string
    {
        return 'R$ ' . number_format($this->split_amount, 2, ',', '.');
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'issued' => 'info',
            'sent' => 'primary',
            'paid' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'muted',
            default => 'secondary'
        };
    }
}
