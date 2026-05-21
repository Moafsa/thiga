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

        $hasOpenAiKey = !empty(config('services.openai.api_key'));

        $settings = $tenant?->metadata['whatsapp_ai'] ?? [
            'ai_enabled'              => false,
            'notify_on_status_change' => true,
            'notify_proposal_followup'=> false,
            'notify_overdue_invoice'  => false,
            'notify_inactive_client'  => false,
            'ai_persona'              => '',
        ];

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

        // Update OpenAI key in .env if provided (not placeholder)
        $newKey = $request->input('openai_api_key');
        if ($newKey && !str_contains($newKey, '•')) {
            $this->updateEnvKey('OPENAI_API_KEY', $newKey);
        }

        // Save settings to tenant metadata
        $currentMeta = $tenant->metadata ?? [];
        $currentMeta['whatsapp_ai'] = [
            'ai_enabled'               => $request->boolean('ai_enabled'),
            'notify_on_status_change'  => $request->boolean('notify_on_status_change'),
            'notify_proposal_followup' => $request->boolean('notify_proposal_followup'),
            'notify_overdue_invoice'   => $request->boolean('notify_overdue_invoice'),
            'notify_inactive_client'   => $request->boolean('notify_inactive_client'),
            'ai_persona'               => $request->input('ai_persona', ''),
        ];

        $tenant->update(['metadata' => $currentMeta]);

        Artisan::call('config:clear');

        return back()->with('success', 'Configurações do Agente IA salvas com sucesso!');
    }

    private function updateEnvKey(string $key, string $value): void
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) return;

        $content = file_get_contents($envPath);
        $escaped = addcslashes($value, '"');

        if (preg_match("/^{$key}=.*/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}=\"{$escaped}\"", $content);
        } else {
            $content .= "\n{$key}=\"{$escaped}\"";
        }

        file_put_contents($envPath, $content);
    }
}
