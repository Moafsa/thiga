<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class WhatsAppAiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $tenant = Auth::user()->tenant;

        $whatsappConnected = WhatsAppIntegration::where('tenant_id', $tenant?->id)
            ->where('status', 'connected')
            ->exists();

        $settings = $tenant?->metadata['whatsapp_ai'] ?? [
            'ai_enabled'              => false,
            'notify_on_status_change' => true,
            'notify_proposal_followup'=> false,
            'notify_overdue_invoice'  => false,
            'notify_inactive_client'  => false,
            'ai_persona'              => '',
        ];

        $tenantHasKey = !empty($settings['openai_api_key_encrypted']);
        $hasOpenAiKey = $tenantHasKey || !empty(config('services.openai.api_key'));

        $aiEnabled = $settings['ai_enabled'] ?? false;

        return view('settings.whatsapp-ai', compact(
            'whatsappConnected', 'hasOpenAiKey', 'aiEnabled', 'settings'
        ));
    }

    public function update(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return back()->with('error', 'Tenant não encontrado.');
        }

        $currentMeta = $tenant->metadata ?? [];
        $waSettings = $currentMeta['whatsapp_ai'] ?? [];

        // Save OpenAI key in database
        $newKey = $request->input('openai_api_key');
        if ($newKey && !str_contains($newKey, '•')) {
            $waSettings['openai_api_key_encrypted'] = \Illuminate\Support\Facades\Crypt::encryptString($newKey);
        }

        // Save other settings to tenant metadata
        $waSettings['ai_enabled']               = $request->boolean('ai_enabled');
        $waSettings['notify_on_status_change']  = $request->boolean('notify_on_status_change');
        $waSettings['notify_proposal_followup'] = $request->boolean('notify_proposal_followup');
        $waSettings['notify_overdue_invoice']   = $request->boolean('notify_overdue_invoice');
        $waSettings['notify_inactive_client']   = $request->boolean('notify_inactive_client');
        $waSettings['ai_persona']               = $request->input('ai_persona', '');

        $currentMeta['whatsapp_ai'] = $waSettings;

        $tenant->update(['metadata' => $currentMeta]);

        Artisan::call('config:clear');

        return back()->with('success', 'Configurações do Agente IA salvas com sucesso!');
    }
}
