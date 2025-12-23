<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverLoginCode;
use App\Models\DriverTenantAssignment;
use App\Models\Tenant;
use App\Models\WhatsAppIntegration;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class DriverAuthService
{
    public const CODE_TTL_MINUTES = 5;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const MAX_ATTEMPTS = 5;

    public function __construct(
        protected WhatsAppIntegrationManager $integrationManager,
        protected WuzApiService $wuzApiService
    ) {
    }

    public function requestLoginCode(string $rawPhone, ?string $deviceId = null, ?DriverTenantAssignment $assignment = null): DriverLoginCode
    {
        $normalizedPhone = $this->normalizePhone($rawPhone);

        if (!$normalizedPhone) {
            throw ValidationException::withMessages([
                'phone' => __('Informe um telefone válido.'),
            ]);
        }

        $driver = $this->resolveDriverForAssignment($rawPhone, $normalizedPhone, $assignment);

        if (!$driver) {
            throw ValidationException::withMessages([
                'phone' => __('Não encontramos um motorista com este telefone.'),
            ]);
        }

        $tenant = $assignment?->tenant ?? $driver->tenant;

        if (!$tenant) {
            throw new RuntimeException('Driver tenant not found.');
        }

        $this->ensureCanRequestCode($driver, $normalizedPhone);

        $code = (string) random_int(100000, 999999);
        $codeHash = hash('sha256', $code);
        $expiresAt = now()->addMinutes(self::CODE_TTL_MINUTES);

        $integration = $this->resolveIntegration($tenant);

        $message = $this->buildCodeMessage($driver, $tenant, $code);

        try {
            $this->dispatchWhatsAppMessage($integration, $normalizedPhone, $message);
            Log::info('WhatsApp message sent successfully', [
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
        DB::transaction(function () use (&$loginCode, $driver, $normalizedPhone, $codeHash, $expiresAt, $deviceId, $tenant) {
            $loginCode = DriverLoginCode::create([
                'tenant_id' => $tenant->id,
                'driver_id' => $driver->id,
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

    public function verifyLoginCode(string $rawPhone, string $code, ?string $deviceId = null, ?DriverTenantAssignment $assignment = null): Driver
    {
        $normalizedPhone = $this->normalizePhone($rawPhone);

        if (!$normalizedPhone) {
            throw ValidationException::withMessages([
                'phone' => __('Informe um telefone válido.'),
            ]);
        }

        $driver = $this->resolveDriverForAssignment($rawPhone, $normalizedPhone, $assignment);

        if (!$driver) {
            throw ValidationException::withMessages([
                'phone' => __('Telefone não encontrado.'),
            ]);
        }

        // Extract local part (without DDI) for variations search
        $localPhone = strlen($normalizedPhone) > 2 ? substr($normalizedPhone, 2) : $normalizedPhone;
        
        $loginCode = DriverLoginCode::where('driver_id', $driver->id)
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

        return $driver;
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

    protected function findDriverByPhone(string $rawPhone, string $normalizedPhone, array $with = ['tenant']): ?Driver
    {
        // Try Driver model normalization first
        $canonicalPhone = Driver::normalizePhone($rawPhone);

        if ($canonicalPhone) {
            $driver = Driver::with($with)
                ->whereNotNull('phone_e164')
                ->where('phone_e164', $canonicalPhone)
                ->first();

            if ($driver) {
                return $driver;
            }
        }

        // Also try raw phone (cleaned) - both with and without DDI
        // Since user said DB stores only DDD + number (54997092223), not with DDI (5554997092223)
        $rawDigits = preg_replace('/\D/', '', $rawPhone);
        if ($rawDigits) {
            // Try as-is (without DDI - this is how user said it's stored)
            $driver = Driver::with($with)
                ->whereNotNull('phone_e164')
                ->where('phone_e164', $rawDigits)
                ->first();
            if ($driver) {
                Log::info('Driver found using raw phone (no DDI)', [
                    'raw' => $rawPhone,
                    'rawDigits' => $rawDigits,
                    'driver_id' => $driver->id,
                ]);
                return $driver;
            }
            
            // Also try with DDI (in case it's stored that way)
            if (!str_starts_with($rawDigits, '55')) {
                $driver = Driver::with($with)
                    ->whereNotNull('phone_e164')
                    ->where('phone_e164', '55' . $rawDigits)
                    ->first();
                if ($driver) {
                    Log::info('Driver found using raw phone (with DDI)', [
                        'raw' => $rawPhone,
                        'withDDI' => '55' . $rawDigits,
                        'driver_id' => $driver->id,
                    ]);
                    return $driver;
                }
            }
        }

        // Fall back to normalized phone search with variations
        return $this->findDriverByNormalizedPhone($normalizedPhone, $rawPhone, $with);
    }

    protected function findDriverByNormalizedPhone(string $normalizedPhone, ?string $rawPhone = null, array $with = []): ?Driver
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
        
        Log::info('Searching driver with phone variations', [
            'normalizedPhone' => $normalizedPhone,
            'localPhone' => $localPhone,
            'searchPhones' => $searchPhones,
        ]);
        
        $driver = Driver::with($with)
            ->whereNotNull('phone_e164')
            ->whereIn('phone_e164', $searchPhones)
            ->first();
            
        if ($driver) {
            Log::info('Driver found', [
                'driver_id' => $driver->id,
                'phone_e164' => $driver->phone_e164,
                'matched_phone' => $driver->phone_e164,
            ]);
        } else {
            Log::warning('Driver not found with any phone variation', [
                'searchPhones' => $searchPhones,
            ]);
        }
        
        return $driver;
    }

    public function getAssignmentsByPhone(string $rawPhone): Collection
    {
        // Clean the phone number but keep it as-is (don't remove the 9)
        $cleanPhone = preg_replace('/\D/', '', $rawPhone);
        
        if (!$cleanPhone || strlen($cleanPhone) < 10) {
            Log::warning('Invalid phone number', ['raw' => $rawPhone, 'clean' => $cleanPhone]);
            return collect();
        }

        Log::info('Searching driver by phone', [
            'raw' => $rawPhone,
            'clean' => $cleanPhone,
        ]);

        // Search directly with the clean phone number (tries with and without DDI)
        $driver = $this->findDriverByPhoneDirect($cleanPhone, ['assignments.tenant', 'assignments.user']);

        if (!$driver) {
            Log::warning('Driver not found by phone', [
                'raw' => $rawPhone,
                'clean' => $cleanPhone,
            ]);
            return collect();
        }

        $assignments = $driver->assignments()->with(['tenant', 'user', 'driver'])->get();
        
        Log::info('Driver assignments retrieved', [
            'driver_id' => $driver->id,
            'assignments_count' => $assignments->count(),
            'assignments' => $assignments->pluck('id')->toArray(),
        ]);

        return $assignments;
    }
    
    protected function findDriverByPhoneDirect(string $cleanPhone, array $with): ?Driver
    {
        // First, try exact match (as stored in DB - only DDD + number, no DDI)
        $driver = Driver::with($with)
            ->whereNotNull('phone_e164')
            ->where('phone_e164', $cleanPhone)
            ->first();
            
        if ($driver) {
            Log::info('Driver found with exact match', [
                'cleanPhone' => $cleanPhone,
                'driver_id' => $driver->id,
                'phone_e164' => $driver->phone_e164,
            ]);
            return $driver;
        }
        
        // If not found, build search variations with normalization
        $searchPhones = [];
        
        // Remove DDI if present
        $localPart = str_starts_with($cleanPhone, '55') ? substr($cleanPhone, 2) : $cleanPhone;
        
        // Always add the original clean phone as-is
        $searchPhones[] = $cleanPhone;
        
        // Also add local part if different from clean phone
        if ($localPart !== $cleanPhone) {
            $searchPhones[] = $localPart;
        }
        
        // Add version with DDI (55) if not already present
        if (!str_starts_with($cleanPhone, '55')) {
            $searchPhones[] = '55' . $cleanPhone;
            $searchPhones[] = '55' . $localPart;
        }
        
        // For DDD 54, handle variations with/without digit 9
        if (str_starts_with($localPart, '54')) {
            $length = strlen($localPart);
            
            // If has 11 digits with 9, also search without 9
            if ($length === 11 && isset($localPart[2]) && $localPart[2] === '9') {
                $withoutNine = substr($localPart, 0, 2) . substr($localPart, 3);
                $searchPhones[] = $withoutNine;  // Without 9, without DDI
                $searchPhones[] = '55' . $withoutNine;  // Without 9, with DDI
            }
            
            // If has 10 digits, also search with 9
            if ($length === 10) {
                $withNine = substr($localPart, 0, 2) . '9' . substr($localPart, 2);
                $searchPhones[] = $withNine;  // With 9, without DDI
                $searchPhones[] = '55' . $withNine;  // With 9, with DDI
            }
        }
        
        $searchPhones = array_unique($searchPhones);
        
        if (!empty($searchPhones)) {
            Log::info('Searching driver with phone variations', [
                'cleanPhone' => $cleanPhone,
                'searchPhones' => $searchPhones,
            ]);
            
            $driver = Driver::with($with)
                ->whereNotNull('phone_e164')
                ->whereIn('phone_e164', $searchPhones)
                ->first();
                
            if ($driver) {
                Log::info('Driver found with variations', [
                    'driver_id' => $driver->id,
                    'phone_e164' => $driver->phone_e164,
                    'matched_phone' => $driver->phone_e164,
                ]);
                return $driver;
            }
        }
        
        Log::warning('Driver not found with any phone variation', [
            'cleanPhone' => $cleanPhone,
            'searchPhones' => $searchPhones,
        ]);
        
        return null;
    }

    protected function ensureCanRequestCode(Driver $driver, string $phone): void
    {
        $recentCode = DriverLoginCode::where('driver_id', $driver->id)
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

    protected function resolveDriverForAssignment(string $rawPhone, string $normalizedPhone, ?DriverTenantAssignment $assignment): ?Driver
    {
        if ($assignment && $assignment->driver) {
            return $assignment->driver;
        }

        return $this->findDriverByPhone($rawPhone, $normalizedPhone, ['tenant', 'user']);
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
        // The test that worked used +555497092223, so we need to add +55 prefix
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
            Log::error('Driver login code WhatsApp dispatch failed', [
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

    protected function buildCodeMessage(Driver $driver, Tenant $tenant, string $code): string
    {
        $company = $tenant->name ?? 'Equipe de Transporte';
        $expiresAt = CarbonImmutable::now()->addMinutes(self::CODE_TTL_MINUTES)->locale('pt_BR');

        $formattedExpiry = $expiresAt->translatedFormat('H:i');

        $greeting = $driver->name ? Str::of($driver->name)->words(2, '')->title() : 'Motorista';

        return "🚛 *{$company}*\n\n"
            . "Olá, {$greeting}!\n"
            . "Seu código de acesso é *{$code}*.\n\n"
            . "Ele expira às {$formattedExpiry}. Não compartilhe este código.\n\n"
            . "Se você não solicitou, informe imediatamente o gestor.";
    }
}


