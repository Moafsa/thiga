<?php

namespace App\Http\Controllers;

use App\Services\FastRouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FastRouteController extends Controller
{
    protected $fastRouteService;

    public function __construct(FastRouteService $fastRouteService)
    {
        $this->middleware('auth');
        $this->fastRouteService = $fastRouteService;
    }

    /**
     * Store a new fast route
     */
    public function store(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'available_cargo_ids' => 'required|array|min:1',
            'available_cargo_ids.*' => 'exists:available_cargo,id',
        ]);

        try {
            $route = $this->fastRouteService->createRouteForDriver(
                $request->driver_id,
                $request->available_cargo_ids
            );

            return redirect()->route('routes.show', $route)
                ->with('success', 'Rota criada com sucesso! (Modo Rápido)');
        } catch (\Exception $e) {
            Log::error('Erro ao criar rota rápida', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Erro ao criar rota: ' . $e->getMessage()]);
        }
    }
}
