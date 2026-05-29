<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RouteSpaceBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_uuid',
        'owner_tenant_id',
        'booker_tenant_id',
        'route_capacity_offer_id',
        'shipment_id',
        'cargo_title',
        'booked_weight',
        'booked_volume',
        'pickup_city',
        'pickup_state',
        'delivery_city',
        'delivery_state',
        'status', // pending_approval, approved, rejected, cargo_received, in_transit, delivered, cancelled
        'amount_base',
        'amount_detour_cost',
        'amount_platform_fee',
        'amount_final',
        'payment_status', // pending, paid, refunded, dispute
        'asaas_payment_id',
        'matching_link_token',
    ];

    protected $casts = [
        'booked_weight' => 'decimal:2',
        'booked_volume' => 'decimal:2',
        'amount_base' => 'decimal:2',
        'amount_detour_cost' => 'decimal:2',
        'amount_platform_fee' => 'decimal:2',
        'amount_final' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (RouteSpaceBooking $booking) {
            $booking->booking_uuid = (string) Str::uuid();
            $booking->matching_link_token = (string) Str::random(40);
        });
    }

    public function ownerTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'owner_tenant_id');
    }

    public function bookerTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'booker_tenant_id');
    }

    public function capacityOffer(): BelongsTo
    {
        return $this->belongsTo(RouteCapacityOffer::class, 'route_capacity_offer_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(RouteCapacityLedgerEntry::class, 'route_space_booking_id');
    }

    /**
     * Scope to allow both the owner tenant and booker tenant to view this record.
     * Bypasses standard single-tenant scopes.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where(function ($q) use ($tenantId) {
            $q->where('owner_tenant_id', $tenantId)
              ->orWhere('booker_tenant_id', $tenantId);
        });
    }
}
