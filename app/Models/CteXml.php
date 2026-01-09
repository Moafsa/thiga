<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CteXml extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'cte_number',
        'access_key',
        'xml',
        'xml_url',
        'is_used',
        'used_at',
        'route_id',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the CT-e XML.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the route that used this XML.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Scope to filter unused XMLs.
     */
    public function scopeUnused($query)
    {
        return $query->where('is_used', false);
    }

    /**
     * Scope to filter used XMLs.
     */
    public function scopeUsed($query)
    {
        return $query->where('is_used', true);
    }

    /**
     * Mark XML as used.
     */
    public function markAsUsed(?int $routeId = null): void
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
            'route_id' => $routeId,
        ]);
    }
}































