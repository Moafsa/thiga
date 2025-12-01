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

        $driver = Driver::with('tenant')
            ->whereNotNull('phone_e164')
            ->where('phone_e164', $normalizedPhone)
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

        $message = $this->buildCodeMessage($driver, $tenant, $code);
        $this->dispatchWhatsAppMessage($integration, $normalizedPhone, $message);

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

        $driver = Driver::with('tenant', 'user')
            ->whereNotNull('phone_e164')
            ->where('phone_e164', $normalizedPhone)
            ->first();

        if (!$driver) {
            throw ValidationException::withMessages([
                'phone' => __('Telefone não encontrado.'),
            ]);
        }

        $loginCode = DriverLoginCode::where('driver_id', $driver->id)
            ->where('phone_e164', $normalizedPhone)
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

        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            return $digits;
        }

        if (strlen($digits) >= 10 && strlen($digits) <= 11) {
            return '55' . $digits;
        }

        return $digits;
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

        try {
            $this->wuzApiService->sendTextMessage($token, $phone, $message);
        } catch (\Throwable $e) {
            Log::error('Driver login code WhatsApp dispatch failed', [
                'integration_id' => $integration->id,
                'phone' => $phone,
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


