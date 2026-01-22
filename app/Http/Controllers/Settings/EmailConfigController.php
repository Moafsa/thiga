<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EmailConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display email configuration page.
     */
    public function index(): View
    {
        $this->authorizeTenantAccess();

        $tenant = Auth::user()->tenant;
        
        return view('settings.integrations.email.index', compact('tenant'));
    }

    /**
     * Update email configuration.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorizeTenantAccess();

        $validated = $request->validate([
            'email_provider' => 'nullable|in:postmark,mailchimp,smtp',
            
            // Postmark config
            'postmark_api_token' => 'nullable|required_if:email_provider,postmark|string',
            'postmark_from_email' => 'nullable|required_if:email_provider,postmark|email',
            'postmark_from_name' => 'nullable|string|max:255',
            
            // Mailchimp config
            'mailchimp_api_key' => 'nullable|required_if:email_provider,mailchimp|string',
            'mailchimp_server_prefix' => 'nullable|required_if:email_provider,mailchimp|string',
            'mailchimp_from_email' => 'nullable|required_if:email_provider,mailchimp|email',
            'mailchimp_from_name' => 'nullable|string|max:255',
            
            // SMTP config
            'smtp_host' => 'nullable|required_if:email_provider,smtp|string',
            'smtp_port' => 'nullable|required_if:email_provider,smtp|integer|min:1|max:65535',
            'smtp_username' => 'nullable|required_if:email_provider,smtp|string',
            'smtp_password' => 'nullable|required_if:email_provider,smtp|string',
            'smtp_encryption' => 'nullable|required_if:email_provider,smtp|in:tls,ssl',
            'smtp_from_email' => 'nullable|required_if:email_provider,smtp|email',
            'smtp_from_name' => 'nullable|string|max:255',
        ]);

        $tenant = Auth::user()->tenant;

        try {
            $emailConfig = [];

            // Build email config based on provider
            if ($validated['email_provider'] === 'postmark') {
                $emailConfig = [
                    'api_token' => $validated['postmark_api_token'] ?? null,
                    'from_email' => $validated['postmark_from_email'] ?? null,
                    'from_name' => $validated['postmark_from_name'] ?? null,
                ];
            } elseif ($validated['email_provider'] === 'mailchimp') {
                $emailConfig = [
                    'api_key' => $validated['mailchimp_api_key'] ?? null,
                    'server_prefix' => $validated['mailchimp_server_prefix'] ?? null,
                    'from_email' => $validated['mailchimp_from_email'] ?? null,
                    'from_name' => $validated['mailchimp_from_name'] ?? null,
                ];
            } elseif ($validated['email_provider'] === 'smtp') {
                $emailConfig = [
                    'host' => $validated['smtp_host'] ?? null,
                    'port' => $validated['smtp_port'] ?? null,
                    'username' => $validated['smtp_username'] ?? null,
                    'password' => $validated['smtp_password'] ?? null,
                    'encryption' => $validated['smtp_encryption'] ?? null,
                    'from_email' => $validated['smtp_from_email'] ?? null,
                    'from_name' => $validated['smtp_from_name'] ?? null,
                ];
            }

            $tenant->email_provider = $validated['email_provider'] ?? null;
            $tenant->email_config = !empty($emailConfig) ? $emailConfig : null;
            $tenant->send_proposal_by_email = $request->has('send_proposal_by_email') && $request->input('send_proposal_by_email') == '1';
            $tenant->send_proposal_by_whatsapp = $request->has('send_proposal_by_whatsapp') && $request->input('send_proposal_by_whatsapp') == '1';
            $tenant->save();

            Log::info('Configuração de email atualizada', [
                'tenant_id' => $tenant->id,
                'email_provider' => $tenant->email_provider,
            ]);

            return redirect()
                ->route('settings.integrations.email.index')
                ->with('success', 'Configuração de email atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar configuração de email', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.integrations.email.index')
                ->with('error', 'Erro ao atualizar configuração: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Test email configuration.
     */
    public function test(Request $request): RedirectResponse
    {
        $this->authorizeTenantAccess();

        $request->validate([
            'test_email' => 'required|email',
        ]);

        $tenant = Auth::user()->tenant;

        try {
            $emailService = app(\App\Services\EmailService::class);
            $result = $emailService->sendTestEmail($tenant, $request->test_email);

            if ($result['success']) {
                return redirect()
                    ->route('settings.integrations.email.index')
                    ->with('success', 'Email de teste enviado com sucesso! Verifique sua caixa de entrada.');
            } else {
                return redirect()
                    ->route('settings.integrations.email.index')
                    ->with('error', 'Erro ao enviar email de teste: ' . ($result['message'] ?? 'Erro desconhecido'));
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de teste', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.integrations.email.index')
                ->with('error', 'Erro ao enviar email de teste: ' . $e->getMessage());
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
}
