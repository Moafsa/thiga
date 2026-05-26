<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SefazIntegrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display SEFAZ configuration page.
     */
    public function index(): View
    {
        $this->authorizeTenantAccess();

        $tenant = Auth::user()->tenant;
        $sefazSettings = $tenant->metadata['sefaz'] ?? [];
        
        return view('settings.integrations.sefaz.index', compact('tenant', 'sefazSettings'));
    }

    /**
     * Update SEFAZ configuration.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorizeTenantAccess();
        $tenant = Auth::user()->tenant;
        $sefazSettings = $tenant->metadata['sefaz'] ?? [];

        $hasCertificate = !empty($sefazSettings['certificate_path']);

        $rules = [
            'sefaz_environment' => 'required|in:homologacao,producao',
            'sefaz_uf' => 'required|string|size:2',
            'sefaz_cnpj' => 'nullable|string|max:18',
        ];

        if (!$hasCertificate) {
            $rules['certificate_file'] = 'required|file|max:5120'; // max 5MB
            $rules['certificate_password'] = 'required|string';
        } else {
            $rules['certificate_file'] = 'nullable|file|max:5120';
            $rules['certificate_password'] = 'nullable|string';
        }

        $validated = $request->validate($rules, [
            'sefaz_environment.required' => 'O ambiente é obrigatório.',
            'sefaz_environment.in' => 'Ambiente inválido.',
            'sefaz_uf.required' => 'A UF da SEFAZ é obrigatória.',
            'sefaz_uf.size' => 'A UF deve conter exatamente 2 caracteres.',
            'certificate_file.required' => 'O arquivo do certificado digital A1 (.pfx) é obrigatório.',
            'certificate_password.required' => 'A senha do certificado digital é obrigatória.',
        ]);

        try {
            $metadata = $tenant->metadata ?? [];
            $sefaz = $metadata['sefaz'] ?? [];

            $sefaz['environment'] = $validated['sefaz_environment'];
            $sefaz['uf'] = strtoupper($validated['sefaz_uf']);
            $sefaz['cnpj'] = $validated['sefaz_cnpj'] ?? null;

            // Handle file upload
            if ($request->hasFile('certificate_file')) {
                $file = $request->file('certificate_file');
                $filename = 'certificado-' . time() . '.pfx';
                $path = "tenants/{$tenant->id}/fiscal/{$filename}";

                // Store securely inside local disk (not public)
                Storage::disk('local')->putFileAs("tenants/{$tenant->id}/fiscal", $file, $filename);

                // If there's an old certificate, delete it
                if (!empty($sefaz['certificate_path'])) {
                    Storage::disk('local')->delete($sefaz['certificate_path']);
                }

                $sefaz['certificate_path'] = $path;
                $sefaz['original_filename'] = $file->getClientOriginalName();
                $sefaz['uploaded_at'] = now()->toDateTimeString();
            }

            // Handle password encryption
            if (!empty($validated['certificate_password'])) {
                $sefaz['certificate_password_encrypted'] = Crypt::encryptString($validated['certificate_password']);
            }

            $metadata['sefaz'] = $sefaz;
            $tenant->metadata = $metadata;
            $tenant->save();

            Log::info('Configuração da SEFAZ e certificado A1 atualizados', [
                'tenant_id' => $tenant->id,
                'environment' => $sefaz['environment'],
                'uf' => $sefaz['uf'],
            ]);

            return redirect()
                ->route('settings.integrations.sefaz.index')
                ->with('success', 'Configuração da SEFAZ e Certificado Digital atualizados com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar configuração da SEFAZ', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.integrations.sefaz.index')
                ->with('error', 'Erro ao salvar configurações: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete certificate file.
     */
    public function destroyCertificate(): RedirectResponse
    {
        $this->authorizeTenantAccess();
        $tenant = Auth::user()->tenant;
        $metadata = $tenant->metadata ?? [];
        $sefaz = $metadata['sefaz'] ?? [];

        try {
            if (!empty($sefaz['certificate_path'])) {
                Storage::disk('local')->delete($sefaz['certificate_path']);
            }

            unset($sefaz['certificate_path']);
            unset($sefaz['certificate_password_encrypted']);
            unset($sefaz['original_filename']);
            unset($sefaz['uploaded_at']);

            $metadata['sefaz'] = $sefaz;
            $tenant->metadata = $metadata;
            $tenant->save();

            Log::info('Certificado digital removido com sucesso', [
                'tenant_id' => $tenant->id,
            ]);

            return redirect()
                ->route('settings.integrations.sefaz.index')
                ->with('success', 'Certificado digital A1 removido com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao remover certificado digital', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settings.integrations.sefaz.index')
                ->with('error', 'Erro ao remover certificado: ' . $e->getMessage());
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
