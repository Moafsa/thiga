<?php

namespace App\Services;

use App\Models\Salesperson;
use App\Models\SalespersonLoginCode;
use App\Models\Tenant;
use App\Models\WhatsAppIntegration;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SalespersonAuthService
{
    public const CODE_TTL_MINUTES = 5;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const MAX_ATTEMPTS = 5;

    public function __construct(
        protected WhatsAppIntegrationManager $integrationManager,
        protected WuzApiService $wuzApiService
    ) {
    }

    public function requestLoginCode(string $rawPhone, ?string $deviceId = null, ?int $tenantId = null): SalespersonLoginCode
    {
        $normalizedPhone = $this->normalizePhone($rawPhone);

        if (!$normalizedPhone) {
            throw ValidationException::withMessages([
                'phone' => __('Informe um telefone válido.'),
            ]);
        }

        $salesperson = $this->findSalespersonByPhone($rawPhone, $normalizedPhone, $tenantId);

        if (!$salesperson) {
            throw ValidationException::withMessages([
                'phone' => __('Não encontramos um vendedor com este telefone.'),
            ]);
        }

        $tenant = $salesperson->tenant;

        if (!$tenant) {
            throw new RuntimeException('Salesperson tenant not found.');
        }

        $this->ensureCanRequestCode($salesperson, $normalizedPhone);

        $code = (string) random_int(100000, 999999);
        $codeHash = hash('sha256', $code);
        $expiresAt = now()->addMinutes(self::CODE_TTL_MINUTES);

        $integration = $this->resolveIntegration($tenant);

        $message = $this->buildCodeMessage($salesperson, $tenant, $code);

        try {
            $this->dispatchWhatsAppMessage($integration, $normalizedPhone, $message);
            Log::info('WhatsApp message sent successfully for salesperson', [
                'integration_id' => $integration->id,
                'phone' => $normalizedPhone,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send WhatsApp message before creating code for salesperson', [
                'phone' => $normalizedPhone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        $loginCode = null;
        DB::transaction(function () use (&$loginCode, $salesperson, $normalizedPhone, $codeHash, $expiresAt, $deviceId, $tenant) {
            $loginCode = SalespersonLoginCode::create([
                'tenant_id' => $tenant->id,
                'salesperson_id' => $salesperson->id,
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

    public function verifyLoginCode(string $rawPhone, string $code, ?string $deviceId = null, ?int $tenantId = null): Salesperson
    {
        $normalizedPhone = $this->normalizePhone($rawPhone);

        if (!$normalizedPhone) {
            throw ValidationException::withMessages([
                'phone' => __('Informe um telefone válido.'),
            ]);
        }

        $salesperson = $this->findSalespersonByPhone($rawPhone, $normalizedPhone, $tenantId);

        if (!$salesperson) {
            throw ValidationException::withMessages([
                'phone' => __('Telefone não encontrado.'),
            ]);
        }

        // Extract local part (without DDI) for variations search
        $localPhone = strlen($normalizedPhone) > 2 ? substr($normalizedPhone, 2) : $normalizedPhone;
        
        $loginCode = SalespersonLoginCode::where('salesperson_id', $salesperson->id)
            ->where(function ($query) use ($normalizedPhone, $localPhone) {
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

        return $salesperson;
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

    protected function findSalespersonByPhone(string $rawPhone, string $normalizedPhone, ?int $tenantId = null): ?Salesperson
    {
        // Try Salesperson model normalization first
        $canonicalPhone = Salesperson::normalizePhone($rawPhone);

        $query = Salesperson::with(['tenant', 'user'])
            ->whereNotNull('phone_e164')
            ->where('is_active', true);

        if ($canonicalPhone) {
            $query->where('phone_e164', $canonicalPhone);
        } else {
            // Also try raw phone (cleaned) - both with and without DDI
            $rawDigits = preg_replace('/\D/', '', $rawPhone);
            if ($rawDigits) {
                // Try as-is (without DDI)
                $query->where('phone_e164', $rawDigits);
                
                // Also try with DDI (in case it's stored that way)
                if (!str_starts_with($rawDigits, '55')) {
                    $query->orWhere('phone_e164', '55' . $rawDigits);
                }
            }
        }

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $salesperson = $query->first();

        if ($salesperson) {
            return $salesperson;
        }

        // Fall back to normalized phone search with variations
        return $this->findSalespersonByNormalizedPhone($normalizedPhone, $rawPhone, $tenantId);
    }

    protected function findSalespersonByNormalizedPhone(string $normalizedPhone, ?string $rawPhone = null, ?int $tenantId = null): ?Salesperson
    {
        // normalizedPhone comes as '55' + local (10 or 11 digits)
        // Extract local part (without DDI)
        $localPhone = strlen($normalizedPhone) > 2 ? substr($normalizedPhone, 2) : $normalizedPhone;
        
        // Build list of phone variations to search
        $searchPhones = [
            $normalizedPhone,      // Normalized: 55 + local (10 digits without 9)
            $localPhone,           // Local without DDI
        ];
        
        // Add raw phone variations if provided
        if ($rawPhone) {
            $rawDigits = preg_replace('/\D/', '', $rawPhone);
            if ($rawDigits) {
                // Add raw with DDI
                if (!str_starts_with($rawDigits, '55')) {
                    $searchPhones[] = '55' . $rawDigits;
                }
                // Add raw as-is
                $searchPhones[] = $rawDigits;
            }
        }
        
        // For DDD 54, handle variations with/without digit 9
        if (str_starts_with($localPhone, '54')) {
            $length = strlen($localPhone);
            
            // If local has 10 digits (normalized removed the 9), also search for 11 digits (with 9)
            if ($length === 10) {
                // Add 9 after DDD: 54 + 9 + rest of 8 digits = 11 digits
                $withNine = substr($localPhone, 0, 2) . '9' . substr($localPhone, 2);
                $searchPhones[] = '55' . $withNine;  // With DDI
                $searchPhones[] = $withNine;         // Without DDI
            }
            
            // If local has 11 digits (with 9), also search for 10 digits (without 9)
            if ($length === 11 && isset($localPhone[2]) && $localPhone[2] === '9') {
                // Remove the 9: 54 + rest (skip position 2 which is the 9)
                $withoutNine = substr($localPhone, 0, 2) . substr($localPhone, 3);
                $searchPhones[] = '55' . $withoutNine;  // With DDI
                $searchPhones[] = $withoutNine;         // Without DDI
            }
        }
        
        // Remove duplicates and search
        $searchPhones = array_unique($searchPhones);
        
        Log::info('Searching salesperson with phone variations', [
            'normalizedPhone' => $normalizedPhone,
            'localPhone' => $localPhone,
            'searchPhones' => $searchPhones,
        ]);
        
        $query = Salesperson::with(['tenant', 'user'])
            ->whereNotNull('phone_e164')
            ->where('is_active', true)
            ->whereIn('phone_e164', $searchPhones);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $salesperson = $query->first();
            
        if ($salesperson) {
            Log::info('Salesperson found', [
                'salesperson_id' => $salesperson->id,
                'phone_e164' => $salesperson->phone_e164,
                'matched_phone' => $salesperson->phone_e164,
            ]);
        } else {
            Log::warning('Salesperson not found with any phone variation', [
                'searchPhones' => $searchPhones,
            ]);
        }
        
        return $salesperson;
    }

    protected function ensureCanRequestCode(Salesperson $salesperson, string $phone): void
    {
        $recentCode = SalespersonLoginCode::where('salesperson_id', $salesperson->id)
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

        // Format phone number for WhatsApp: ensure it starts with +55
        $formattedPhone = $phone;
        if (!str_starts_with($phone, '+')) {
            // If phone starts with 54, add +55 prefix: 5497092223 -> +555497092223
            if (str_starts_with($phone, '54')) {
                $formattedPhone = '+55' . $phone;
            } elseif (str_starts_with($phone, '55')) {
                // If already has 55, just add +
                $formattedPhone = '+' . $phone;
            } else {
                // Otherwise, add +55 prefix
                $formattedPhone = '+55' . $phone;
            }
        }

        try {
            $this->wuzApiService->sendTextMessage($token, $formattedPhone, $message);
        } catch (\Throwable $e) {
            Log::error('Salesperson login code WhatsApp dispatch failed', [
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

    protected function buildCodeMessage(Salesperson $salesperson, Tenant $tenant, string $code): string
    {
        $company = $tenant->name ?? 'Equipe de Vendas';
        $expiresAt = CarbonImmutable::now()->addMinutes(self::CODE_TTL_MINUTES)->locale('pt_BR');

        $formattedExpiry = $expiresAt->translatedFormat('H:i');

        $greeting = $salesperson->name ? Str::of($salesperson->name)->words(2, '')->title() : 'Vendedor';

        return "💼 *{$company}*\n\n"
            . "Olá, {$greeting}!\n"
            . "Seu código de acesso é *{$code}*.\n\n"
            . "Ele expira às {$formattedExpiry}. Não compartilhe este código.\n\n"
            . "Se você não solicitou, informe imediatamente o gestor.";
    }
}
