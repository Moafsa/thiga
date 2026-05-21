<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentCostAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'route_expense_id',
        'shipment_id',
        'route_id',
        'allocated_amount',
        'allocation_pct',
        'allocation_basis',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'allocation_pct' => 'decimal:4',
        'tenant_id' => 'integer',
        'route_expense_id' => 'integer',
        'shipment_id' => 'integer',
        'route_id' => 'integer',
    ];

    /**
     * Get the tenant that owns this allocation.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the route expense that generated this allocation.
     */
    public function routeExpense(): BelongsTo
    {
        return $this->belongsTo(RouteExpense::class);
    }

    /**
     * Get the shipment this allocation was assigned to.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Get the route this allocation relates to.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }
}
