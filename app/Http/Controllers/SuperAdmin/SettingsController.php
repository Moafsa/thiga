<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    private $settingsPath = 'settings.json';

    private function getSettings()
    {
        if (Storage::exists($this->settingsPath)) {
            return json_decode(Storage::get($this->settingsPath), true) ?? [];
        }
        return [];
    }

    public function index()
    {
        $settings = $this->getSettings();
        return view('superadmin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'asaas_api_key' => 'nullable|string',
            'asaas_wallet_id' => 'nullable|string',
        ]);

        $settings = $this->getSettings();
        $settings = array_merge($settings, $validated);

        Storage::put($this->settingsPath, json_encode($settings, JSON_PRETTY_PRINT));

        return back()->with('success', 'Configurações globais atualizadas com sucesso!');
    }
}
