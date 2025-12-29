<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'expense_category_id',
        'vehicle_id',
        'route_id',
        'description',
        'amount',
        'fuel_liters',
        'odometer_reading',
        'price_per_liter',
        'due_date',
        'paid_at',
        'status',
        'payment_method',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fuel_liters' => 'decimal:2',
        'odometer_reading' => 'integer',
        'price_per_liter' => 'decimal:4',
        'due_date' => 'date',
        'paid_at' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Get the tenant that owns the expense.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the category for the expense.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * Get the payments for the expense.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the vehicle for the expense (maintenance).
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the route for the expense.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Check if expense is a maintenance (has vehicle).
     */
    public function isMaintenance(): bool
    {
        return !is_null($this->vehicle_id);
    }

    /**
     * Check if expense is linked to a route.
     */
    public function isRouteExpense(): bool
    {
        return !is_null($this->route_id);
    }

    /**
     * Check if expense is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if expense is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if expense is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    /**
     * Mark expense as paid.
     */
    public function markAsPaid(string $paymentMethod = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $paymentMethod ?? $this->payment_method,
        ]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter overdue expenses.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where('due_date', '<', now());
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('expense_category_id', $categoryId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    /**
     * Check if expense is a fuel refueling (has fuel_liters and odometer_reading).
     */
    public function isFuelRefueling(): bool
    {
        return !is_null($this->fuel_liters) && !is_null($this->odometer_reading);
    }

    /**
     * Scope to filter fuel refueling expenses.
     */
    public function scopeFuelRefuelings($query)
    {
        return $query->whereNotNull('fuel_liters')
            ->whereNotNull('odometer_reading');
    }
}












