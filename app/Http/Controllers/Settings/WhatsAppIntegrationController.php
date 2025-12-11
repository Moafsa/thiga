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
                'trace' => $e->getTraceAsString(),
            ]);

            $message = $e->getMessage();
            
            // Provide more specific error messages
            if (str_contains($message, 'já está conectado') || str_contains($message, 'Already Loggedin')) {
                $userMessage = 'O WhatsApp já está conectado. Faça logout primeiro para gerar um novo QR Code.';
            } elseif (str_contains($message, 'No session') || str_contains($message, 'Not connected')) {
                $userMessage = 'Não foi possível conectar a sessão do WhatsApp. Tente sincronizar a integração primeiro.';
            } else {
                $userMessage = $message ?: 'Não foi possível gerar o QR Code. Tente novamente ou verifique a conexão com o WuzAPI.';
            }

            return response()->json([
                'message' => $userMessage,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get integration status (for polling after QR scan).
     */
    public function status(WhatsAppIntegration $whatsappIntegration): JsonResponse
    {
        $this->authorizeTenantAccess();
        $this->authorizeIntegration($whatsappIntegration);

        try {
            // Get raw status from WuzAPI without updating database
            // This prevents false positives from updating status incorrectly
            $token = $whatsappIntegration->getUserToken();
            if (!$token) {
                return response()->json([
                    'status' => $whatsappIntegration->status,
                    'connected' => false,
                    'isLoggedIn' => false,
                    'isConnected' => false,
                    'error' => 'Token not available',
                ], 200); // Return 200 to prevent "Failed to fetch"
            }

            $wuzApiService = app(\App\Services\WuzApiService::class);
            
            // Try to get status, but handle "No session" gracefully
            try {
                $rawStatus = $wuzApiService->getSessionStatus($token);
            } catch (Exception $statusException) {
                // If "No session", it's normal - session might not be created yet
                if (str_contains($statusException->getMessage(), 'No session')) {
                    $whatsappIntegration->refresh();
                    return response()->json([
                        'status' => $whatsappIntegration->status,
                        'connected' => false,
                        'isLoggedIn' => false,
                        'isConnected' => false,
                    ], 200);
                }
                // Re-throw other exceptions
                throw $statusException;
            }
            
            // Extract actual connection state from WuzAPI response
            $data = $rawStatus['data'] ?? $rawStatus;
            $isLoggedIn = (bool) ($data['LoggedIn'] ?? $data['loggedIn'] ?? false);
            $isConnected = (bool) ($data['Connected'] ?? $data['connected'] ?? false);
            $hasJid = !empty($data['jid'] ?? $data['Jid'] ?? null);
            $jid = $data['jid'] ?? $data['Jid'] ?? null;
            
            // Only consider truly connected if BOTH LoggedIn AND Connected are true
            // This prevents false positives when only websocket is connected but QR wasn't scanned
            $actuallyConnected = $isLoggedIn && $isConnected;
            
            Log::info('WhatsApp status check', [
                'integration_id' => $whatsappIntegration->id,
                'isLoggedIn' => $isLoggedIn,
                'isConnected' => $isConnected,
                'hasJid' => $hasJid,
                'jid' => $jid ? (substr($jid, 0, 20) . '...') : null,
                'actuallyConnected' => $actuallyConnected,
                'current_db_status' => $whatsappIntegration->status,
                'raw_status_keys' => array_keys($data),
            ]);
            
            // Only update database status if it's actually different to avoid false updates
            // Also check if we were recently connected to prevent false disconnections
            $recentlyConnected = $whatsappIntegration->connected_at && 
                                $whatsappIntegration->connected_at->isAfter(now()->subSeconds(60));
            
            if ($actuallyConnected && $whatsappIntegration->status !== WhatsAppIntegration::STATUS_CONNECTED) {
                // Definitely connected - update status
                $this->integrationManager->syncSession($whatsappIntegration);
                $whatsappIntegration->refresh();
            } elseif (!$actuallyConnected && $whatsappIntegration->status === WhatsAppIntegration::STATUS_CONNECTED) {
                // If we think it's connected but WuzAPI says it's not, be cautious if recently connected
                if ($recentlyConnected) {
                    Log::warning('Status check: WuzAPI says not connected but was recently connected - waiting before updating', [
                        'integration_id' => $whatsappIntegration->id,
                        'connected_at' => $whatsappIntegration->connected_at,
                        'isLoggedIn' => $isLoggedIn,
                        'isConnected' => $isConnected,
                    ]);
                    // Don't update immediately - might be a temporary disconnection
                    // Return current status instead
                } else {
                    // Not recently connected, safe to update
                    $this->integrationManager->syncSession($whatsappIntegration);
                    $whatsappIntegration->refresh();
                }
            }

            return response()->json([
                'status' => $whatsappIntegration->status,
                'connected' => $actuallyConnected, // Use actual connection state, not database status
                'isLoggedIn' => $isLoggedIn,
                'isConnected' => $isConnected,
                'last_synced_at' => $whatsappIntegration->last_synced_at?->toIso8601String(),
            ]);
        } catch (Exception $e) {
            Log::error('Falha ao obter status da integração WhatsApp', [
                'integration_id' => $whatsappIntegration->id,
                'error' => $e->getMessage(),
            ]);

            // Return current database status, but mark as not connected
            // Don't return 500 - return 200 with error info to prevent frontend "Failed to fetch"
            $whatsappIntegration->refresh();
            
            // Check if error is "No session" - this is normal when session hasn't been created yet
            $isNoSessionError = str_contains($e->getMessage(), 'No session');
            
            return response()->json([
                'status' => $whatsappIntegration->status,
                'connected' => false,
                'isLoggedIn' => false,
                'isConnected' => false,
                'error' => $isNoSessionError ? null : $e->getMessage(), // Don't show "No session" as error
            ], 200); // Return 200 instead of 500 to prevent "Failed to fetch"
        }
    }

    /**
     * Logout WhatsApp session (force logout to allow new QR generation).
     */
    public function logout(WhatsAppIntegration $whatsappIntegration): RedirectResponse
    {
        $this->authorizeTenantAccess();
        $this->authorizeIntegration($whatsappIntegration);

        try {
            $this->integrationManager->logout($whatsappIntegration);

            return redirect()
                ->route('settings.integrations.whatsapp.index')
                ->with('status', 'Sessão do WhatsApp desconectada. Você pode gerar um novo QR Code agora.');
        } catch (Exception $e) {
            Log::error('Falha ao fazer logout da integração WhatsApp', [
                'integration_id' => $whatsappIntegration->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.integrations.whatsapp.index')
                ->with('error', 'Não foi possível desconectar a sessão. Tente novamente.');
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
            // Delete from WuzAPI and database
            $this->integrationManager->deleteIntegration($whatsappIntegration);

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

