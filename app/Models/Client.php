<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Client extends Model
{
    use HasFactory, HasActiveScope;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'cnpj',
        'email',
        'phone',
        'phone_e164',
        'address',
        'city',
        'state',
        'zip_code',
        'salesperson_id',
        'is_active',
        'marker',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'marker' => 'bronze',
    ];

    /**
     * Get available markers
     */
    public static function getAvailableMarkers(): array
    {
        return [
            'bronze' => [
                'label' => 'Bronze',
                'color' => '#CD7F32',
                'bg_color' => 'rgba(205, 127, 50, 0.2)',
            ],
            'silver' => [
                'label' => 'Prata',
                'color' => '#C0C0C0',
                'bg_color' => 'rgba(192, 192, 192, 0.2)',
            ],
            'gold' => [
                'label' => 'Ouro',
                'color' => '#FFD700',
                'bg_color' => 'rgba(255, 215, 0, 0.2)',
            ],
            'blue' => [
                'label' => 'Azul',
                'color' => '#2196F3',
                'bg_color' => 'rgba(33, 150, 243, 0.2)',
            ],
            'yellow' => [
                'label' => 'Amarelo',
                'color' => '#FFEB3B',
                'bg_color' => 'rgba(255, 235, 59, 0.2)',
            ],
            'red' => [
                'label' => 'Vermelho',
                'color' => '#F44336',
                'bg_color' => 'rgba(244, 67, 54, 0.2)',
            ],
        ];
    }

    /**
     * Get marker info
     */
    public function getMarkerInfo(): array
    {
        $markers = self::getAvailableMarkers();
        $marker = $this->marker ?: 'bronze';
        return $markers[$marker] ?? $markers['bronze'];
    }

    /**
     * Get marker label
     */
    public function getMarkerLabelAttribute(): string
    {
        return $this->getMarkerInfo()['label'];
    }

    /**
     * Get marker color
     */
    public function getMarkerColorAttribute(): string
    {
        return $this->getMarkerInfo()['color'];
    }

    /**
     * Get marker background color
     */
    public function getMarkerBgColorAttribute(): string
    {
        return $this->getMarkerInfo()['bg_color'];
    }

    /**
     * Get the tenant that owns the client.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the salesperson for the client.
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(Salesperson::class);
    }

    /**
     * Get the shipments for the client.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'sender_client_id');
    }

    /**
     * Get the addresses for the client.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }

    /**
     * Get the proposals for the client.
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    /**
     * Get the invoices for the client.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the user associated with the client.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all user assignments for this client (multi-tenant support).
     */
    public function userAssignments(): HasMany
    {
        return $this->hasMany(ClientUser::class);
    }

    /**
     * Get the freight tables that belong to this client (one-to-many relationship).
     */
    public function freightTablesOwned(): HasMany
    {
        return $this->hasMany(FreightTable::class);
    }

    /**
     * Get the freight tables associated with this client (many-to-many relationship).
     */
    public function freightTables(): BelongsToMany
    {
        return $this->belongsToMany(FreightTable::class, 'client_freight_table')
            ->withTimestamps();
    }

    /**
     * Normalize phone number to E.164 format.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (!$digits) {
            return null;
        }

        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            return $digits;
        }

        if (strlen($digits) >= 10 && strlen($digits) <= 11) {
            return '55' . $digits;
        }

        return $digits;
    }

    protected static function booted(): void
    {
        static::saving(function (Client $client) {
            // Normalize phone to E.164 format only if column exists
            if (\Schema::hasColumn('clients', 'phone_e164')) {
                if ($client->phone) {
                    $client->phone_e164 = self::normalizePhone($client->phone);
                } else {
                    $client->phone_e164 = null;
                }
            }
        });
    }
}
