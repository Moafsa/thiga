<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DriverAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DriverAuthController extends Controller
{
    public function __construct(
        protected DriverAuthService $driverAuthService
    ) {
    }

    public function requestCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'device_id' => ['nullable', 'string', 'max:64'],
        ]);

        $loginCode = $this->driverAuthService->requestLoginCode(
            $validated['phone'],
            $validated['device_id'] ?? null
        );

        return response()->json([
            'message' => __('Código enviado pelo WhatsApp. Verifique suas mensagens.'),
            'expires_at' => $loginCode->expires_at?->toIso8601String(),
        ]);
    }

    public function verifyCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'code' => ['required', 'string', 'size:6'],
            'device_id' => ['nullable', 'string', 'max:64'],
        ]);

        $driver = $this->driverAuthService->verifyLoginCode(
            $validated['phone'],
            $validated['code'],
            $validated['device_id'] ?? null
        );

        if (!$driver->user) {
            throw ValidationException::withMessages([
                'phone' => __('Perfil de acesso do motorista não está configurado. Contate o suporte.'),
            ]);
        }

        $token = $driver->user->createToken(
            name: 'driver-app',
            abilities: ['driver']
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'abilities' => $token->accessToken->abilities,
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'phone' => $driver->phone,
                'status' => $driver->status,
                'tenant_id' => $driver->tenant_id,
            ],
            'tenant' => [
                'id' => $driver->tenant?->id,
                'name' => $driver->tenant?->name,
            ],
        ]);
    }
}
















