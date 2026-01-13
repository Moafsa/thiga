<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientLoginCode;
use App\Models\ClientUser;
use App\Models\Tenant;
use App\Models\WhatsAppIntegration;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ClientAuthService
{
    public const CODE_TTL_MINUTES = 5;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const MAX_ATTEMPTS = 5;

    public function __construct(
        protected WhatsAppIntegrationManager $integrationManager,
        protected WuzApiService $wuzApiService
    ) {
    }

    public function requestLoginCode(string $rawPhone, ?string $deviceId = null, ?ClientUser $assignment = null): ClientLoginCode
    {
        $normalizedPhone = $this->normalizePhone($rawPhone);

        if (!$normalizedPhone) {
            throw ValidationException::withMessages([
                'phone' => __('Informe um telefone válido.'),
            ]);
        }

        $client = $this->resolveClientForAssignment($rawPhone, $normalizedPhone, $assignment);

        if (!$client) {
            throw ValidationException::withMessages([
                'phone' => __('Não encontramos um cliente com este telefone.'),
            ]);
        }

        $tenant = $assignment?->tenant ?? $client->tenant;

        if (!$tenant) {
            throw new RuntimeException('Client tenant not found.');
        }

        $this->ensureCanRequestCode($client, $normalizedPhone);

        $code = (string) random_int(100000, 999999);
        $codeHash = hash('sha256', $code);
        $expiresAt = now()->addMinutes(self::CODE_TTL_MINUTES);

        $integration = $this->resolveIntegration($tenant);

        $message = $this->buildCodeMessage($client, $tenant, $code);

        try {
            $this->dispatchWhatsAppMessage($integration, $normalizedPhone, $message);
            Log::info('WhatsApp message sent successfully to client', [
                'integration_id' => $integration->id,
                'phone' => $normalizedPhone,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send WhatsApp message before creating code', [
                'phone' => $normalizedPhone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        $loginCode = null;
        DB::transaction(function () use (&$loginCode, $client, $normalizedPhone, $codeHash, $expiresAt, $deviceId, $tenant) {
            $loginCode = ClientLoginCode::create([
                'tenant_id' => $tenant->id,
                'client_id' => $client->id,
                'phone_e164' => $normalizedPhone,
                'code_hash' => $codeHash,
                'channel' => 'whatsapp',
                'sent_at' => now(),
                'expires_at' => $expiresAt,
                'metadata' => array_filter([
                    'device_id' => $deviceId,
                ]),
            ]);
        });

        return $loginCode;
    }

    public function verifyLoginCode(string $rawPhone, string $code, ?string $deviceId = null, ?ClientUser $assignment = null): Client
    {
        $normalizedPhone = $this->normalizePhone($rawPhone);

        if (!$normalizedPhone) {
            throw ValidationException::withMessages([
                'phone' => __('Informe um telefone válido.'),
            ]);
        }

        $client = $this->resolveClientForAssignment($rawPhone, $normalizedPhone, $assignment);

        if (!$client) {
            throw ValidationException::withMessages([
                'phone' => __('Telefone não encontrado.'),
            ]);
        }

        // Extract local part (without DDI) for variations search
        $localPhone = strlen($normalizedPhone) > 2 ? substr($normalizedPhone, 2) : $normalizedPhone;
        
        $loginCode = ClientLoginCode::where('client_id', $client->id)
            ->where(function ($query) use ($normalizedPhone, $localPhone) {
                // Exact match
                $query->where('phone_e164', $normalizedPhone)
                    ->orWhere('phone_e164', $localPhone)
                    // For DDD 54, handle variations with/without digit 9
                    ->orWhere(function ($q) use ($localPhone) {
                        if (str_starts_with($localPhone, '54')) {
                            $length = strlen($localPhone);
                            
                            // If local has 10 digits (without 9), also search for 11 digits (with 9)
                            if ($length === 10) {
                                // Add 9 after DDD: 54 + 9 + rest
                                $withNine = substr($localPhone, 0, 2) . '9' . substr($localPhone, 2);
                                $q->where('phone_e164', '55' . $withNine)
                                  ->orWhere('phone_e164', $withNine);
                            }
                            
                            // If local has 11 digits (with 9), also search for 10 digits (without 9)
                            if ($length === 11 && $localPhone[2] === '9') {
                                // Remove the 9: 54 + rest (skip position 2)
                                $withoutNine = substr($localPhone, 0, 2) . substr($localPhone, 3);
                                $q->where('phone_e164', '55' . $withoutNine)
                                  ->orWhere('phone_e164', $withoutNine);
                            }
                        }
                    });
            })
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$loginCode) {
            throw ValidationException::withMessages([
                'code' => __('Código inválido ou expirado. Solicite um novo.'),
            ]);
        }

        if ($loginCode->attempts >= self::MAX_ATTEMPTS) {
            throw ValidationException::withMessages([
                'code' => __('Número máximo de tentativas excedido. Solicite um novo código.'),
            ]);
        }

        $loginCode->increment('attempts', 1, [
            'last_attempt_at' => now(),
            'metadata' => array_filter(array_merge($loginCode->metadata ?? [], [
                'last_device_id' => $deviceId,
            ])),
        ]);

        if (!hash_equals($loginCode->code_hash, hash('sha256', $code))) {
            throw ValidationException::withMessages([
                'code' => __('Código incorreto.'),
            ]);
        }

        $loginCode->forceFill([
            'used_at' => now(),
        ])->save();

        return $client;
    }

    public function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (!$digits) {
            return null;
        }

        if (str_starts_with($digits, '55')) {
            $digits = substr($digits, 2);
        }

        $local = $this->normalizeBrazilianLocalNumber($digits);

        return $local ? '55' . $local : null;
    }

    protected function normalizeBrazilianLocalNumber(string $digits): ?string
    {
        $length = strlen($digits);

        if ($length === 11 && $digits[2] === '9') {
            return substr($digits, 0, 2) . substr($digits, 3);
        }

        if ($length === 10) {
            return $digits;
        }

        if ($length === 11 && $digits[2] !== '9') {
            return $digits;
        }

        return null;
    }

    protected function findClientByPhone(string $rawPhone, string $normalizedPhone, array $with = ['tenant']): ?Client
    {
        // Try Client model normalization first
        $canonicalPhone = Client::normalizePhone($rawPhone);

        if ($canonicalPhone) {
            $client = Client::with($with)
                ->whereNotNull('phone_e164')
                ->where('phone_e164', $canonicalPhone)
                ->first();

            if ($client) {
                return $client;
            }
        }

        // Also try raw phone (cleaned) - both with and without DDI
        $rawDigits = preg_replace('/\D/', '', $rawPhone);
        if ($rawDigits) {
            // Try as-is (without DDI)
            $client = Client::with($with)
                ->whereNotNull('phone_e164')
                ->where('phone_e164', $rawDigits)
                ->first();
            if ($client) {
                Log::info('Client found using raw phone (no DDI)', [
                    'raw' => $rawPhone,
                    'rawDigits' => $rawDigits,
                    'client_id' => $client->id,
                ]);
                return $client;
            }
            
            // Also try with DDI (in case it's stored that way)
            if (!str_starts_with($rawDigits, '55')) {
                $client = Client::with($with)
                    ->whereNotNull('phone_e164')
                    ->where('phone_e164', '55' . $rawDigits)
                    ->first();
                if ($client) {
                    Log::info('Client found using raw phone (with DDI)', [
                        'raw' => $rawPhone,
                        'withDDI' => '55' . $rawDigits,
                        'client_id' => $client->id,
                    ]);
                    return $client;
                }
            }
        }

        // Fall back to normalized phone search with variations
        return $this->findClientByNormalizedPhone($normalizedPhone, $rawPhone, $with);
    }

    protected function findClientByNormalizedPhone(string $normalizedPhone, ?string $rawPhone = null, array $with = []): ?Client
    {
        $localPhone = strlen($normalizedPhone) > 2 ? substr($normalizedPhone, 2) : $normalizedPhone;
        
        $searchPhones = [
            $normalizedPhone,
            $localPhone,
        ];
        
        if ($rawPhone) {
            $rawDigits = preg_replace('/\D/', '', $rawPhone);
            if ($rawDigits) {
                if (!str_starts_with($rawDigits, '55')) {
                    $searchPhones[] = '55' . $rawDigits;
                }
                $searchPhones[] = $rawDigits;
            }
        }
        
        // For DDD 54, handle variations with/without digit 9
        if (str_starts_with($localPhone, '54')) {
            $length = strlen($localPhone);
            
            if ($length === 10) {
                $withNine = substr($localPhone, 0, 2) . '9' . substr($localPhone, 2);
                $searchPhones[] = '55' . $withNine;
                $searchPhones[] = $withNine;
            }
            
            if ($length === 11 && isset($localPhone[2]) && $localPhone[2] === '9') {
                $withoutNine = substr($localPhone, 0, 2) . substr($localPhone, 3);
                $searchPhones[] = '55' . $withoutNine;
                $searchPhones[] = $withoutNine;
            }
        }
        
        $searchPhones = array_unique($searchPhones);
        
        Log::info('Searching client with phone variations', [
            'normalizedPhone' => $normalizedPhone,
            'localPhone' => $localPhone,
            'searchPhones' => $searchPhones,
        ]);
        
        $client = Client::with($with)
            ->whereNotNull('phone_e164')
            ->whereIn('phone_e164', $searchPhones)
            ->first();
            
        if ($client) {
            Log::info('Client found', [
                'client_id' => $client->id,
                'phone_e164' => $client->phone_e164,
                'matched_phone' => $client->phone_e164,
            ]);
        } else {
            Log::warning('Client not found with any phone variation', [
                'searchPhones' => $searchPhones,
            ]);
        }
        
        return $client;
    }

    public function getAssignmentsByPhone(string $rawPhone): Collection
    {
        $cleanPhone = preg_replace('/\D/', '', $rawPhone);
        
        if (!$cleanPhone || strlen($cleanPhone) < 10) {
            Log::warning('Invalid phone number', ['raw' => $rawPhone, 'clean' => $cleanPhone]);
            return collect();
        }

        Log::info('Searching client by phone', [
            'raw' => $rawPhone,
            'clean' => $cleanPhone,
        ]);

        $client = $this->findClientByPhoneDirect($cleanPhone, ['userAssignments.tenant', 'userAssignments.user']);

        if (!$client) {
            Log::warning('Client not found by phone', [
                'raw' => $rawPhone,
                'clean' => $cleanPhone,
            ]);
            return collect();
        }

        $assignments = $client->userAssignments()->with(['tenant', 'user', 'client'])->get();
        
        Log::info('Client assignments retrieved', [
            'client_id' => $client->id,
            'assignments_count' => $assignments->count(),
            'assignments' => $assignments->pluck('id')->toArray(),
        ]);

        return $assignments;
    }
    
    protected function findClientByPhoneDirect(string $cleanPhone, array $with): ?Client
    {
        $client = Client::with($with)
            ->whereNotNull('phone_e164')
            ->where('phone_e164', $cleanPhone)
            ->first();
            
        if ($client) {
            Log::info('Client found with exact match', [
                'cleanPhone' => $cleanPhone,
                'client_id' => $client->id,
                'phone_e164' => $client->phone_e164,
            ]);
            return $client;
        }
        
        $searchPhones = [];
        $localPart = str_starts_with($cleanPhone, '55') ? substr($cleanPhone, 2) : $cleanPhone;
        
        $searchPhones[] = $cleanPhone;
        
        if ($localPart !== $cleanPhone) {
            $searchPhones[] = $localPart;
        }
        
        if (!str_starts_with($cleanPhone, '55')) {
            $searchPhones[] = '55' . $cleanPhone;
            $searchPhones[] = '55' . $localPart;
        }
        
        if (str_starts_with($localPart, '54')) {
            $length = strlen($localPart);
            
            if ($length === 11 && isset($localPart[2]) && $localPart[2] === '9') {
                $withoutNine = substr($localPart, 0, 2) . substr($localPart, 3);
                $searchPhones[] = $withoutNine;
                $searchPhones[] = '55' . $withoutNine;
            }
            
            if ($length === 10) {
                $withNine = substr($localPart, 0, 2) . '9' . substr($localPart, 2);
                $searchPhones[] = $withNine;
                $searchPhones[] = '55' . $withNine;
            }
        }
        
        $searchPhones = array_unique($searchPhones);
        
        if (!empty($searchPhones)) {
            Log::info('Searching client with phone variations', [
                'cleanPhone' => $cleanPhone,
                'searchPhones' => $searchPhones,
            ]);
            
            $client = Client::with($with)
                ->whereNotNull('phone_e164')
                ->whereIn('phone_e164', $searchPhones)
                ->first();
                
            if ($client) {
                Log::info('Client found with variations', [
                    'client_id' => $client->id,
                    'phone_e164' => $client->phone_e164,
                    'matched_phone' => $client->phone_e164,
                ]);
                return $client;
            }
        }
        
        Log::warning('Client not found with any phone variation', [
            'cleanPhone' => $cleanPhone,
            'searchPhones' => $searchPhones,
        ]);
        
        return null;
    }

    protected function ensureCanRequestCode(Client $client, string $phone): void
    {
        $recentCode = ClientLoginCode::where('client_id', $client->id)
            ->where('phone_e164', $phone)
            ->where('created_at', '>=', now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))
            ->latest()
            ->first();

        if ($recentCode) {
            throw ValidationException::withMessages([
                'phone' => __('Aguarde alguns instantes antes de solicitar um novo código.'),
            ]);
        }
    }

    protected function resolveClientForAssignment(string $rawPhone, string $normalizedPhone, ?ClientUser $assignment): ?Client
    {
        if ($assignment && $assignment->client) {
            return $assignment->client;
        }

        return $this->findClientByPhone($rawPhone, $normalizedPhone, ['tenant', 'user']);
    }

    protected function resolveIntegration(Tenant $tenant): WhatsAppIntegration
    {
        $integration = $this->integrationManager->getConnectedIntegrationForTenant($tenant->id);

        if (!$integration || !($token = $integration->getUserToken())) {
            throw ValidationException::withMessages([
                'phone' => __('Não foi possível enviar o código. Integração WhatsApp indisponível.'),
            ]);
        }

        return $integration;
    }

    protected function dispatchWhatsAppMessage(WhatsAppIntegration $integration, string $phone, string $message): void
    {
        $token = $integration->getUserToken();

        if (!$token) {
            throw new RuntimeException('WhatsApp integration token missing.');
        }

        $formattedPhone = $phone;
        if (!str_starts_with($phone, '+')) {
            if (str_starts_with($phone, '54')) {
                $formattedPhone = '+55' . $phone;
            } elseif (str_starts_with($phone, '55')) {
                $formattedPhone = '+' . $phone;
            } else {
                $formattedPhone = '+55' . $phone;
            }
        }

        try {
            $this->wuzApiService->sendTextMessage($token, $formattedPhone, $message);
        } catch (\Throwable $e) {
            Log::error('Client login code WhatsApp dispatch failed', [
                'integration_id' => $integration->id,
                'phone' => $phone,
                'formatted_phone' => $formattedPhone,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'phone' => __('Não foi possível enviar o código pelo WhatsApp. Tente novamente mais tarde.'),
            ]);
        }
    }

    protected function buildCodeMessage(Client $client, Tenant $tenant, string $code): string
    {
        $company = $tenant->name ?? 'Equipe de Transporte';
        $expiresAt = CarbonImmutable::now()->addMinutes(self::CODE_TTL_MINUTES)->locale('pt_BR');

        $formattedExpiry = $expiresAt->translatedFormat('H:i');

        $greeting = $client->name ? Str::of($client->name)->words(2, '')->title() : 'Cliente';

        return "📦 *{$company}*\n\n"
            . "Olá, {$greeting}!\n"
            . "Seu código de acesso é *{$code}*.\n\n"
            . "Ele expira às {$formattedExpiry}. Não compartilhe este código.\n\n"
            . "Se você não solicitou, informe imediatamente o suporte.";
    }
}
