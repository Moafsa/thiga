<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteCapacityLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'route_space_booking_id',
        'entry_type', // reserve, confirm, cancel, release
        'weight_delta',
        'volume_delta',
    ];

    protected $casts = [
        'weight_delta' => 'decimal:2',
        'volume_delta' => 'decimal:2',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function spaceBooking(): BelongsTo
    {
        return $this->belongsTo(RouteSpaceBooking::class, 'route_space_booking_id');
    }
}
