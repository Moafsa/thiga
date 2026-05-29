<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RouteCapacityOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'route_id',
        'offered_weight',
        'offered_volume',
        'price_per_kg',
        'price_per_m3',
        'min_price',
        'status', // active, booked, completed, cancelled
        'restrictions',
    ];

    protected $casts = [
        'offered_weight' => 'decimal:2',
        'offered_volume' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'price_per_m3' => 'decimal:2',
        'min_price' => 'decimal:2',
        'restrictions' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function spaceBookings(): HasMany
    {
        return $this->hasMany(RouteSpaceBooking::class, 'route_capacity_offer_id');
    }
}
