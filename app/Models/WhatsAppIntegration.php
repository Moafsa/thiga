<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class WhatsAppIntegration extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_integrations';

    protected $fillable = [
        'tenant_id',
        'name',
        'display_phone',
        'webhook_url',
        'status',
        'last_session_payload',
        'last_synced_at',
        'connected_at',
        'disconnected_at',
    ];

    protected $hidden = [
        'wuzapi_user_token_encrypted',
        'wuzapi_user_token_hash',
    ];

    protected $appends = [
        'masked_token',
    ];

    protected $casts = [
        'last_session_payload' => 'array',
        'last_synced_at' => 'datetime',
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
    ];

    public const STATUS_DISCONNECTED = 'disconnected';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONNECTED = 'connected';
    public const STATUS_ERROR = 'error';

    /**
     * Tenant relationship.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Templates relationship.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(WhatsAppMessageTemplate::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Define the token hash when setting the token in plain text.
     */
    public function setUserToken(string $plainToken): void
    {
        $this->wuzapi_user_token_hash = hash('sha256', $plainToken);
        $this->wuzapi_user_token_encrypted = Crypt::encryptString($plainToken);
    }

    /**
     * Retrieve decrypted token.
     */
    public function getUserToken(): ?string
    {
        if (!$this->wuzapi_user_token_encrypted) {
            return null;
        }

        return Crypt::decryptString($this->wuzapi_user_token_encrypted);
    }

    /**
     * Compute masked token for UI.
     */
    protected function maskedToken(): Attribute
    {
        return Attribute::get(function (): ?string {
            $token = $this->getUserToken();

            if (!$token) {
                return null;
            }

            $length = strlen($token);
            if ($length <= 6) {
                return str_repeat('*', $length);
            }

            $start = substr($token, 0, 3);
            $end = substr($token, -3);
            $maskLength = max(0, $length - 6);

            return $start . str_repeat('*', $maskLength) . $end;
        });
    }

    /**
     * Determine if integration is connected.
     */
    public function isConnected(): bool
    {
        return $this->status === self::STATUS_CONNECTED;
    }
}

