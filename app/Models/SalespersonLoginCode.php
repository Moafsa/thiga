<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalespersonLoginCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'salesperson_id',
        'phone_e164',
        'code_hash',
        'channel',
        'attempts',
        'last_attempt_at',
        'sent_at',
        'expires_at',
        'used_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(Salesperson::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }
}
