<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory, HasActiveScope;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'login_token',
        'temp_password',
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
        'excluded_from_listing_at',
    ];

    /**
     * Ensure client has a login token for magic link auto-login
     */
    public function ensureLoginToken(): string
    {
        if (empty($this->login_token)) {
            $this->login_token = \Illuminate\Support\Str::random(32) . dechex(time()) . \Illuminate\Support\Str::random(16);
            $this->save();
        }
        return $this->login_token;
    }

    /**
     * Get 1-click Auto-Login URL attribute
     */
    public function getAutologinUrlAttribute(): string
    {
        $token = $this->ensureLoginToken();
        return url("/client/autologin/{$token}");
    }

    protected $casts = [
        'is_active' => 'boolean',
        'excluded_from_listing_at' => 'datetime',
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
     * Scope: apenas clientes presentes na listagem do tenant (não excluídos).
     */
    public function scopeListed($query)
    {
        if (Schema::hasColumn('clients', 'excluded_from_listing_at')) {
            return $query->whereNull('excluded_from_listing_at');
        }
        return $query;
    }

    /**
     * Scope: apenas clientes excluídos da listagem.
     */
    public function scopeExcludedFromListing($query)
    {
        if (Schema::hasColumn('clients', 'excluded_from_listing_at')) {
            return $query->whereNotNull('excluded_from_listing_at');
        }
        return $query;
    }

    /**
     * Verifica se o cliente está excluído da listagem.
     */
    public function isExcludedFromListing(): bool
    {
        return $this->excluded_from_listing_at !== null;
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
     * Verifica se já existe outro cliente no tenant com mesmo CNPJ, telefone ou e-mail.
     * Usado para evitar cadastro duplicado.
     *
     * @param int $tenantId
     * @param array $data ['cnpj' => ?|null, 'phone' => ?|null, 'email' => ?|null]
     * @param int|null $excludeClientId Excluir este ID (para update)
     * @return Client|null Cliente duplicado encontrado ou null
     */
    public static function findDuplicateInTenant(int $tenantId, array $data, ?int $excludeClientId = null): ?Client
    {
        $cnpj = isset($data['cnpj']) ? preg_replace('/\D/', '', (string) $data['cnpj']) : null;
        $cnpj = $cnpj !== '' ? $cnpj : null;
        $phone = isset($data['phone']) ? trim((string) $data['phone']) : null;
        $phone = $phone !== '' ? $phone : null;
        $phoneDigits = $phone ? preg_replace('/\D/', '', $phone) : null;
        $email = isset($data['email']) ? trim((string) $data['email']) : null;
        if ($email !== '') {
            $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
        } else {
            $email = null;
        }

        if (!$cnpj && !$phoneDigits && !$email) {
            return null;
        }

        $query = self::where('tenant_id', $tenantId);

        $query->where(function ($q) use ($cnpj, $phoneDigits, $email) {
            if ($cnpj) {
                $q->orWhereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(cnpj,''), '.', ''), '/', ''), '-', ''), ' ', '') = ?",
                    [$cnpj]
                );
            }
            if ($phoneDigits && strlen($phoneDigits) >= 8) {
                $q->orWhere(function ($q2) use ($phoneDigits) {
                    $q2->whereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(phone,''), ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') = ?",
                        [$phoneDigits]
                    );
                    if (Schema::hasColumn('clients', 'phone_e164')) {
                        $q2->orWhere('phone_e164', $phoneDigits)
                            ->orWhere('phone_e164', '55' . $phoneDigits);
                    }
                });
            }
            if ($email) {
                $q->orWhereRaw('LOWER(TRIM(COALESCE(email,\'\'))) = ?', [strtolower($email)]);
            }
        });

        if ($excludeClientId) {
            $query->where('id', '!=', $excludeClientId);
        }

        return $query->first();
    }

    /**
     * Normalize phone number to E.164 format.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // First strip device part if present (e.g. "5511999887766:7@lid" -> "5511999887766@lid")
        $phone = preg_replace('/:(\d+)(@|$)/', '$2', $phone);

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

    /**
     * Find or create client based on address/XML data.
     */
    public static function findOrCreateClient($tenant, array $addressData): Client
    {
        $cnpj = isset($addressData['cnpj']) ? preg_replace('/\D/', '', (string) $addressData['cnpj']) : null;
        $cnpj = $cnpj !== '' ? $cnpj : null;

        $email = isset($addressData['email']) ? trim((string) $addressData['email']) : null;
        if ($email !== '') {
            $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
        } else {
            $email = null;
        }
        $phone = isset($addressData['phone']) ? trim((string) $addressData['phone']) : null;
        $phone = $phone !== '' ? $phone : null;

        $client = null;
        if ($cnpj) {
            $client = self::where('tenant_id', $tenant->id)
                ->whereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(cnpj,''), '.', ''), '/', ''), '-', ''), ' ', '') = ?",
                    [$cnpj]
                )
                ->first();
        }
        if (!$client && ($phone || $email)) {
            $client = self::findDuplicateInTenant($tenant->id, [
                'cnpj' => $cnpj,
                'phone' => $phone,
                'email' => $email,
            ]);
        }

        if ($client) {
            $updateData = [];
            if (!empty($addressData['address']) && empty($client->address)) {
                $updateData['address'] = $addressData['address'];
            }
            if (!empty($addressData['city']) && empty($client->city)) {
                $updateData['city'] = $addressData['city'];
            }
            if (!empty($addressData['state']) && empty($client->state)) {
                $updateData['state'] = $addressData['state'];
            }
            if (!empty($addressData['zip_code']) && empty($client->zip_code)) {
                $updateData['zip_code'] = $addressData['zip_code'];
            }
            if ($email !== null && empty($client->email)) {
                $updateData['email'] = $email;
            }
            if ($phone !== null && empty($client->phone)) {
                $updateData['phone'] = $phone;
            }
            if (!empty($updateData)) {
                $client->update($updateData);
                $client->refresh();
            }
            self::ensureClientHasUserForLogin($client, $tenant);
            return $client;
        }

        return self::createClientFromAddressData($tenant, $addressData, $cnpj, $email, $phone);
    }

    /**
     * Create client from XML/address data and optionally User for login (when email or phone present).
     */
    protected static function createClientFromAddressData($tenant, array $addressData, ?string $cnpj, ?string $email, ?string $phone): Client
    {
        $client = DB::transaction(function () use ($tenant, $addressData, $cnpj, $email, $phone) {
            $client = self::create([
                'tenant_id' => $tenant->id,
                'name' => $addressData['name'] ?? 'Unknown',
                'cnpj' => $cnpj,
                'email' => $email,
                'phone' => $phone,
                'address' => $addressData['address'] ?? '',
                'city' => $addressData['city'] ?? '',
                'state' => $addressData['state'] ?? '',
                'zip_code' => $addressData['zip_code'] ?? '',
                'is_active' => true,
            ]);

            if ($email || $phone) {
                self::createUserForClient($client, $tenant, $email, $phone);
            }

            return $client;
        });

        return $client;
    }

    /**
     * Ensure client has User for login when we have contact info. Use when updating existing client.
     */
    protected static function ensureClientHasUserForLogin(Client $client, $tenant): void
    {
        if (!$client->email && !$client->phone) {
            return;
        }
        if ($client->user_id) {
            return;
        }
        self::createUserForClient($client, $tenant, $client->email ?: null, $client->phone ?: null);
    }

    /**
     * Create User + ClientUser for client login (phone/email code). Skips if email already used.
     */
    protected static function createUserForClient(Client $client, $tenant, ?string $email, ?string $phone): void
    {
        $phoneDigits = $phone ? preg_replace('/\D/', '', $phone) : null;
        $userEmail = $email
            ? Str::lower($email)
            : ('client+' . $tenant->id . '+' . ($phoneDigits ?? '0') . '@tms.local');

        if (User::where('email', $userEmail)->exists()) {
            Log::info('findOrCreateClient: skipped user creation, email already in use', [
                'client_id' => $client->id,
                'email' => $userEmail,
            ]);
            return;
        }

        $user = User::create([
            'name' => $client->name,
            'email' => $userEmail,
            'password' => Hash::make(Str::random(32)),
            'tenant_id' => $tenant->id,
            'phone' => $phoneDigits,
            'is_active' => true,
        ]);

        try {
            $user->assignRole('Client');
        } catch (\Throwable $e) {
            // Role may not exist
        }

        ClientUser::create([
            'client_id' => $client->id,
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
        ]);

        $client->forceFill(['user_id' => $user->id])->save();
    }
}
