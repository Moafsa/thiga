<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppIntegration;
use App\Services\WhatsAppIntegrationManager;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WhatsAppIntegrationController extends Controller
{
    public function __construct(
        protected WhatsAppIntegrationManager $integrationManager
    ) {
        $this->middleware('auth');
    }

    /**
     * Display integrations dashboard.
     */
    public function index(): View
    {
        $this->authorizeTenantAccess();

        $tenant = Auth::user()->tenant;
        $integrations = $tenant->whatsappIntegrations()
            ->latest()
            ->get();

        $exposedToken = session('whatsapp_integration_token');

        return view('settings.integrations.whatsapp.index', compact('integrations', 'exposedToken'));
    }

    /**
     * Store new integration.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTenantAccess();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('whatsapp_integrations', 'name')->where(function ($query) {
                    return $query->where('tenant_id', Auth::user()->tenant_id);
                }),
            ],
            'display_phone' => ['nullable', 'string', 'max:30'],
            'webhook_url' => ['nullable', 'url'],
        ]);

        $tenant = Auth::user()->tenant;

        try {
            $result = $this->integrationManager->createIntegration($tenant, $validated);

            return redirect()
                ->route('settings.integrations.whatsapp.index')
                ->with('status', 'Integração criada com sucesso. Salve o token apresentado abaixo com segurança.')
                ->with('whatsapp_integration_token', $result['token']);
        } catch (Exception $e) {
            Log::error('Falha ao criar integração WhatsApp', [
                'tenant_id' => $tenant->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.integrations.whatsapp.index')
                ->with('error', 'Não foi possível criar a integração. Verifique os logs e tente novamente.');
        }
    }

    /**
     * Sync integration state.
     */
    public function sync(WhatsAppIntegration $whatsappIntegration): RedirectResponse
    {
        $this->authorizeTenantAccess();
        $this->authorizeIntegration($whatsappIntegration);

        try {
            $this->integrationManager->syncSession($whatsappIntegration);

            return redirect()
                ->route('settings.integrations.whatsapp.index')
                ->with('status', 'Status da integração sincronizado com sucesso.');
        } catch (Exception $e) {
            Log::error('Falha ao sincronizar integração WhatsApp', [
                'integration_id' => $whatsappIntegration->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.integrations.whatsapp.index')
                ->with('error', 'Não foi possível sincronizar a integração. Confira se o serviço WuzAPI está acessível.');
        }
    }

    /**
     * Return QR code for integration.
     */
    public function qr(WhatsAppIntegration $whatsappIntegration): JsonResponse
    {
        $this->authorizeTenantAccess();
        $this->authorizeIntegration($whatsappIntegration);

        try {
            $qrSvg = $this->integrationManager->getQrCode($whatsappIntegration);

            return response()->json([
                'qr' => $qrSvg,
            ]);
        } catch (Exception $e) {
            Log::error('Falha ao gerar QR Code do WhatsApp', [
                'integration_id' => $whatsappIntegration->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Não foi possível gerar o QR Code. Tente novamente ou verifique a conexão com o WuzAPI.',
            ], 500);
        }
    }

    /**
     * Remove integration.
     */
    public function destroy(WhatsAppIntegration $whatsappIntegration): RedirectResponse
    {
        $this->authorizeTenantAccess();
        $this->authorizeIntegration($whatsappIntegration);

        try {
            if ($whatsappIntegration->isConnected()) {
                $this->integrationManager->disconnect($whatsappIntegration);
            }

            $whatsappIntegration->delete();

            return redirect()
                ->route('settings.integrations.whatsapp.index')
                ->with('status', 'Integração removida com sucesso.');
        } catch (Exception $e) {
            Log::error('Falha ao remover integração WhatsApp', [
                'integration_id' => $whatsappIntegration->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.integrations.whatsapp.index')
                ->with('error', 'Não foi possível remover a integração. Consulte os logs para mais detalhes.');
        }
    }

    /**
     * Ensure current user can manage settings.
     */
    protected function authorizeTenantAccess(): void
    {
        $user = Auth::user();

        if (!$user || (!$user->isTenantAdmin() && !$user->isSuperAdmin())) {
            abort(403, 'Acesso não autorizado.');
        }
    }

    /**
     * Ensure integration belongs to authenticated tenant.
     */
    protected function authorizeIntegration(WhatsAppIntegration $integration): void
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant || $integration->tenant_id !== $tenant->id) {
            abort(403, 'Integração não pertence ao tenant atual.');
        }
    }
}

