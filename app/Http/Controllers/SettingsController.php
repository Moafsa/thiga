<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Display the settings index page.
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Display the appearance settings page.
     */
    public function appearance()
    {
        $tenant = Auth::user()->tenant;
        return view('settings.appearance', compact('tenant'));
    }

    /**
     * Update appearance settings.
     */
    public function updateAppearance(Request $request)
    {
        $request->validate([
            'primary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ], [
            'primary_color.required' => 'A cor primária é obrigatória.',
            'primary_color.regex' => 'A cor primária deve estar no formato hexadecimal (#RRGGBB).',
            'secondary_color.required' => 'A cor secundária é obrigatória.',
            'secondary_color.regex' => 'A cor secundária deve estar no formato hexadecimal (#RRGGBB).',
            'accent_color.required' => 'A cor de destaque é obrigatória.',
            'accent_color.regex' => 'A cor de destaque deve estar no formato hexadecimal (#RRGGBB).',
        ]);

        $tenant = Auth::user()->tenant;
        $tenant->primary_color = $request->primary_color;
        $tenant->secondary_color = $request->secondary_color;
        $tenant->accent_color = $request->accent_color;
        $tenant->save();

        return redirect()->route('settings.appearance')->with('success', 'Cores atualizadas com sucesso!');
    }
}
