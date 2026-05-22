<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AsaasConfigController extends Controller
{
    public function index()
    {
        $tenant = Auth::user()->tenant;
        return view('settings.integrations.asaas', compact('tenant'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'asaas_api_key' => 'nullable|string',
            'asaas_account_id' => 'nullable|string',
            'uses_own_asaas' => 'boolean'
        ]);

        $tenant = Auth::user()->tenant;
        $tenant->update([
            'asaas_api_key' => $request->asaas_api_key,
            'asaas_account_id' => $request->asaas_account_id,
            'uses_own_asaas' => $request->has('uses_own_asaas')
        ]);

        return back()->with('success', 'Configurações do Asaas atualizadas com sucesso!');
    }
}
