<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'key',
        'name',
        'category',
        'language',
        'placeholders',
        'content',
        'enabled',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'enabled' => 'boolean',
    ];

    /**
     * Tenant relationship.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}















