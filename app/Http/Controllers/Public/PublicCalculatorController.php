<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\Tenant;
use App\Services\FreightCalculationService;
use App\Services\MapsService;
use App\Services\WuzApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PublicCalculatorController extends Controller
{
    protected FreightCalculationService $freightService;
    protected MapsService $mapsService;
    protected WuzApiService $wuzApiService;

    public function __construct(
        FreightCalculationService $freightService,
        MapsService $mapsService,
        WuzApiService $wuzApiService
    ) {
        $this->freightService = $freightService;
        $this->mapsService = $mapsService;
        $this->wuzApiService = $wuzApiService;
    }

    public function show(string $domain)
    {
        $tenant = Tenant::where('domain', $domain)->where('is_active', true)->firstOrFail();

        return view('public.calculator', [
            'tenant' => $tenant
        ]);
    }

    public function calculate(Request $request, string $domain)
    {
        $tenant = Tenant::where('domain', $domain)->where('is_active', true)->firstOrFail();

        $request->validate([
            'client_name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'origin' => 'required|string',
            'destination' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
            'invoice_value' => 'required|numeric|min:0',
            'otp_verified' => 'required|boolean|accepted', // Ensure frontend validated OTP
        ]);

        try {
            $result = $this->freightService->calculate(
                $tenant,
                $request->destination,
                $request->weight,
                0, // Cubage
                $request->invoice_value
            );

            // 1. Create/Update Client (Auto-register or update info)
            $phone = Client::normalizePhone($request->whatsapp);
            $client = Client::where('tenant_id', $tenant->id)
                ->where('phone', $phone)
                ->first();

            if (!$client) {
                // If checking by email too
                if ($request->email) {
                    $client = Client::where('tenant_id', $tenant->id)
                        ->where('email', $request->email)
                        ->first();
                }
            }

            if (!$client) {
                $client = Client::create([
                    'tenant_id' => $tenant->id,
                    'name' => $request->client_name,
                    'phone' => $phone,
                    'email' => $request->email,
                    'is_active' => true,
                    // Default marker/status if needed
                ]);
            } else {
                // optional: update name/email if missing
                if (!$client->email && $request->email) {
                    $client->update(['email' => $request->email]);
                }
            }

            // 2. Create Proposal (History)
            $proposal = Proposal::create([
                'tenant_id' => $tenant->id,
                'client_id' => $client->id,
                'client_name' => $request->client_name,
                'client_whatsapp' => $phone,
                'client_email' => $request->email,
                'destination_name' => $request->destination,
                'origin_city' => $request->origin, // Storing as city for now
                'weight' => $request->weight,
                'base_value' => $result['total'],
                'final_value' => $result['total'],
                'status' => 'sent', // Or 'draft'/'negotiating'
                'sent_at' => now(),
                'valid_until' => now()->addDays(7),
                'description' => "Cotação Automática via Site: Origem {$request->origin} -> Destino {$request->destination}",
                'attachments' => ['breakdown' => $result['breakdown']],
            ]);

            // 3. Send WhatsApp Notification (Resumo)
            try {
                // Assuming we have a way to get the tenant's WuzAPI token
                // Usually stored in WhatsAppIntegration model, but here using a helper or service logic
                $integration = \App\Models\WhatsAppIntegration::where('tenant_id', $tenant->id)
                    ->where('is_active', true)
                    ->where('status', 'connected') // Assuming 'connected' is the status
                    ->first();

                if ($integration) {
                    // Normalize breakdown text
                    $msg = "📦 *Nova Cotação Realizada* 📦\n\n";
                    $msg .= "Olá, *{$request->client_name}*!\n\n";
                    $msg .= "Aqui está o resumo da sua simulação:\n";
                    $msg .= "📍 *Origem:* {$request->origin}\n";
                    $msg .= "📍 *Destino:* {$request->destination}\n";
                    $msg .= "⚖️ *Peso:* {$request->weight} kg\n";
                    $msg .= "💰 *Valor NF:* R$ " . number_format($request->invoice_value, 2, ',', '.') . "\n\n";
                    $msg .= "🚛 *Frete Estimado: R$ " . number_format($result['total'], 2, ',', '.') . "*\n\n";
                    $msg .= "Nossa equipe entrará em contato em breve para confirmar os detalhes.\n";
                    $msg .= "_Proposta #{$proposal->id}_";

                    $this->wuzApiService->sendTextMessage(
                        $integration->access_token, // Or however we get the token
                        $phone,
                        $msg
                    );
                }
            } catch (\Exception $whatsappError) {
                Log::warning("PublicCalc: Failed to send WhatsApp: " . $whatsappError->getMessage());
                // Don't block the response, just log
            }

            return response()->json([
                'success' => true,
                'total' => number_format($result['total'], 2, ',', '.'),
                'details' => $result,
                'message_sent' => isset($integration)
            ]);

        } catch (\Exception $e) {
            Log::error("Public Calc Error ({$tenant->domain}): " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível calcular o frete para este destino. Entre em contato.'
            ], 422);
        }
    }

    public function sendOtp(Request $request, string $domain)
    {
        $tenant = Tenant::where('domain', $domain)->where('is_active', true)->firstOrFail();

        $request->validate([
            'whatsapp' => 'required|string|min:10',
            'client_name' => 'required|string'
        ]);

        $phone = Client::normalizePhone($request->whatsapp);

        // Generate 6 digit code
        $code = rand(100000, 999999);

        // Cache for 10 minutes
        $key = "otp_auth_{$tenant->id}_{$phone}";
        Cache::put($key, $code, now()->addMinutes(10));

        // Send via WhatsApp
        try {
            $integration = \App\Models\WhatsAppIntegration::where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->first();

            if (!$integration || !$integration->access_token) {
                // Fallback dev mode or error
                Log::info("TOP CODE for {$phone}: {$code} (No integration found)");
                // For now, allow proceed in dev/test if no integration, or return error?
                // Let's pretend specific success but code is in log for dev validation if needed
                if (app()->environment('local')) {
                    return response()->json(['success' => true, 'dev_code' => $code]);
                }
                return response()->json(['success' => false, 'message' => 'Serviço de WhatsApp indisponível no momento.'], 503);
            }

            $msg = "🔐 *Seu Código de Verificação: {$code}*\n\nUlilize este código para visualizar sua cotação de frete na {$tenant->name}.";

            $this->wuzApiService->sendTextMessage($integration->access_token, $phone, $msg);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error("OTP Send Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao enviar código.'], 500);
        }
    }

    public function verifyOtp(Request $request, string $domain)
    {
        $tenant = Tenant::where('domain', $domain)->where('is_active', true)->firstOrFail();

        $request->validate([
            'whatsapp' => 'required|string',
            'code' => 'required|string',
            'client_name' => 'required|string',
            'email' => 'nullable|email'
        ]);

        $phone = Client::normalizePhone($request->whatsapp);
        $key = "otp_auth_{$tenant->id}_{$phone}";
        $cachedCode = Cache::get($key);

        if (!$cachedCode || $cachedCode != $request->code) {
            return response()->json(['success' => false, 'message' => 'Código inválido ou expirado.'], 422);
        }

        // Valid code!
        // Clear cache
        Cache::forget($key);

        // Auto-register logic moved to calculate? Or do it here?
        // Let's do it here to ensure "Authenticated" state implies registration exists
        // ... well, calculate handles it to update info if needed. 
        // We just return success here effectively "signing" the session for the frontend.

        return response()->json(['success' => true]);
    }
}
