<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverLoginCode;
use App\Models\Tenant;
use App\Models\WhatsAppIntegration;
use Carbon\CarbonImmutable;
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

    public function requestLoginCode(string $rawPhone, ?string $deviceId = null): DriverLoginCode
    {
        $normalizedPhone = $this->normalizePhone($rawPhone);

        if (!$normalizedPhone) {
            throw ValidationException::withMessages([
                'phone' => __('Informe um telefone válido.'),
            ]);
        }

        // Try to find driver with normalized phone
        // Also try with 55 prefix in case number is stored with country code
        // Also try variations (with/without extra digit) for flexibility
        $driver = Driver::with('tenant')
            ->whereNotNull('phone_e164')
            ->where(function ($query) use ($normalizedPhone) {
                $query->where('phone_e164', $normalizedPhone)
                    ->orWhere('phone_e164', '55' . $normalizedPhone)
                    // Try removing last digit if number has 11 digits (54997092223 -> 5497092223)
                    ->orWhere(function ($q) use ($normalizedPhone) {
                        if (strlen($normalizedPhone) === 11 && str_starts_with($normalizedPhone, '54')) {
                            $withoutLast = substr($normalizedPhone, 0, -1);
                            $q->where('phone_e164', $withoutLast)
                              ->orWhere('phone_e164', '55' . $withoutLast);
                        }
                    })
                    // Try adding digit if number has 10 digits (5497092223 -> 54997092223)
                    ->orWhere(function ($q) use ($normalizedPhone) {
                        if (strlen($normalizedPhone) === 10 && str_starts_with($normalizedPhone, '54')) {
                            // Try adding 9 before last digit (common pattern)
                            $withExtra = substr($normalizedPhone, 0, -1) . '9' . substr($normalizedPhone, -1);
                            $q->where('phone_e164', $withExtra)
                              ->orWhere('phone_e164', '55' . $withExtra);
                        }
                    });
            })
            ->first();

        if (!$driver) {
            throw ValidationException::withMessages([
                'phone' => __('Não encontramos um motorista com este telefone.'),
            ]);
        }

        $this->ensureCanRequestCode($driver, $normalizedPhone);

        $code = (string) random_int(100000, 999999);
        $codeHash = hash('sha256', $code);
        $expiresAt = now()->addMinutes(self::CODE_TTL_MINUTES);

        /** @var Tenant $tenant */
        $tenant = $driver->tenant;

        if (!$tenant) {
            throw new RuntimeException('Driver tenant not found.');
        }

        $integration = $this->resolveIntegration($tenant);

        $message = $this->buildCodeMessage($driver, $tenant, $code);

        // Try to send message first, before creating the code
        // This ensures we don't create a code if message sending fails
        try {
            $this->dispatchWhatsAppMessage($integration, $normalizedPhone, $message);
            Log::info('WhatsApp message sent successfully', [
                'integration_id' => $integration->id,
                'phone' => $normalizedPhone,
            ]);
        } catch (\Throwable $e) {
            // If message sending fails, don't create the code
            Log::error('Failed to send WhatsApp message before creating code', [
                'phone' => $normalizedPhone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Re-throw to be caught by controller
        }

        // Only create code if message was sent successfully
        $loginCode = null;
        DB::transaction(function () use (&$loginCode, $driver, $normalizedPhone, $codeHash, $expiresAt, $deviceId) {
            $loginCode = DriverLoginCode::create([
                'tenant_id' => $driver->tenant_id,
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

    public function verifyLoginCode(string $rawPhone, string $code, ?string $deviceId = null): Driver
    {
        $normalizedPhone = $this->normalizePhone($rawPhone);

        if (!$normalizedPhone) {
            throw ValidationException::withMessages([
                'phone' => __('Informe um telefone válido.'),
            ]);
        }

        // Try to find driver with normalized phone (same flexible search as requestLoginCode)
        $driver = Driver::with('tenant', 'user')
            ->whereNotNull('phone_e164')
            ->where(function ($query) use ($normalizedPhone) {
                $query->where('phone_e164', $normalizedPhone)
                    ->orWhere('phone_e164', '55' . $normalizedPhone)
                    ->orWhere(function ($q) use ($normalizedPhone) {
                        if (strlen($normalizedPhone) === 11 && str_starts_with($normalizedPhone, '54')) {
                            $withoutLast = substr($normalizedPhone, 0, -1);
                            $q->where('phone_e164', $withoutLast)
                              ->orWhere('phone_e164', '55' . $withoutLast);
                        }
                    })
                    ->orWhere(function ($q) use ($normalizedPhone) {
                        if (strlen($normalizedPhone) === 10 && str_starts_with($normalizedPhone, '54')) {
                            $withExtra = substr($normalizedPhone, 0, -1) . '9' . substr($normalizedPhone, -1);
                            $q->where('phone_e164', $withExtra)
                              ->orWhere('phone_e164', '55' . $withExtra);
                        }
                    });
            })
            ->first();

        if (!$driver) {
            throw ValidationException::withMessages([
                'phone' => __('Telefone não encontrado.'),
            ]);
        }

        // Search for login code with normalized phone or with 55 prefix
        // Also try variations (with/without extra digit) for flexibility
        // This handles cases where code was created with one format but driver has another
        $loginCode = DriverLoginCode::where('driver_id', $driver->id)
            ->where(function ($query) use ($normalizedPhone) {
                $query->where('phone_e164', $normalizedPhone)
                    ->orWhere('phone_e164', '55' . $normalizedPhone)
                    // Try removing last digit if number has 11 digits
                    ->orWhere(function ($q) use ($normalizedPhone) {
                        if (strlen($normalizedPhone) === 11 && str_starts_with($normalizedPhone, '54')) {
                            $withoutLast = substr($normalizedPhone, 0, -1);
                            $q->where('phone_e164', $withoutLast)
                              ->orWhere('phone_e164', '55' . $withoutLast);
                        }
                    })
                    // Try adding digit if number has 10 digits
                    ->orWhere(function ($q) use ($normalizedPhone) {
                        if (strlen($normalizedPhone) === 10 && str_starts_with($normalizedPhone, '54')) {
                            $withExtra = substr($normalizedPhone, 0, -1) . '9' . substr($normalizedPhone, -1);
                            $q->where('phone_e164', $withExtra)
                              ->orWhere('phone_e164', '55' . $withExtra);
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

        // First, handle numbers that start with 54
        // If number has 11 digits starting with 54, it might have an extra digit
        // Normalize to 10 digits by removing the extra digit (54997092223 -> 5497092223)
        if (str_starts_with($digits, '54')) {
            if (strlen($digits) === 11) {
                // Remove the extra digit (usually the 4th digit after 54)
                // Pattern: 54997092223 -> 5497092223 (remove the extra 9 at position 3)
                $normalized = substr($digits, 0, 3) . substr($digits, 4);
                return $normalized; // Return normalized: 5497092223
            } elseif (strlen($digits) === 10) {
                return $digits; // Return as is: 5497092223
            }
        }

        // If number starts with 55 and has 12+ digits, remove the leading 55
        // This handles cases like +5554997092223 or 5554997092223 -> 5497092223
        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            $digits = substr($digits, 2);
            // After removing 55, if it now starts with 54, normalize it
            if (str_starts_with($digits, '54')) {
                if (strlen($digits) === 11) {
                    // Remove extra digit
                    $normalized = substr($digits, 0, 3) . substr($digits, 4);
                    return $normalized;
                } elseif (strlen($digits) === 10) {
                    return $digits;
                }
            }
        }

        // Handle numbers without country code (10-11 digits)
        // Example: 4997092223 -> 5497092223
        if (strlen($digits) >= 10 && strlen($digits) <= 11) {
            // If it doesn't start with 54, assume it's a local number and add 54
            if (!str_starts_with($digits, '54')) {
                return '54' . $digits;
            }
            // If it starts with 54 and has 11 digits, remove the extra digit
            if (str_starts_with($digits, '54') && strlen($digits) === 11) {
                return substr($digits, 0, 3) . substr($digits, 4);
            }
            return $digits; // Already starts with 54 and has 10 digits
        }

        // If number is 12+ digits and doesn't start with 55, return as is
        if (strlen($digits) >= 12 && !str_starts_with($digits, '55')) {
            return $digits;
        }

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


