<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DriverExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'route_id',
        'expense_type',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'receipt_url',
        'notes',
        'status',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'metadata' => 'array',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get receipt URL
     */
    public function getReceiptUrlAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        // Try MinIO first, then public disk
        try {
            if (Storage::disk('minio')->exists($value)) {
                return Storage::disk('minio')->url($value);
            }
        } catch (\Exception $e) {
            // MinIO not available
        }

        if (Storage::disk('public')->exists($value)) {
            return Storage::disk('public')->url($value);
        }

        return null;
    }

    /**
     * Get expense type label
     */
    public function getExpenseTypeLabelAttribute(): string
    {
        return match($this->expense_type) {
            'toll' => 'Pedágio',
            'fuel' => 'Combustível',
            'meal' => 'Refeição',
            'parking' => 'Estacionamento',
            'other' => 'Outro',
            default => $this->expense_type,
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            default => $this->status,
        };
    }

    /**
     * Check if expense is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if expense is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Scope for approved expenses
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending expenses
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope by expense type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('expense_type', $type);
    }
}






