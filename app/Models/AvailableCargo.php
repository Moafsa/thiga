<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailableCargo extends Model
{
    use HasFactory;

    protected $table = 'available_cargo';

    protected $fillable = [
        'tenant_id',
        'proposal_id',
        'status',
        'route_id',
        'assigned_at',
        'collected_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'collected_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    public function isCollected(): bool
    {
        return $this->status === 'collected';
    }
}
