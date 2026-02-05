<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\FreightCalculationService;
use App\Services\MapsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicCalculatorController extends Controller
{
    protected FreightCalculationService $freightService;
    protected MapsService $mapsService;

    public function __construct(FreightCalculationService $freightService, MapsService $mapsService)
    {
        $this->freightService = $freightService;
        $this->mapsService = $mapsService;
    }

    public function show(string $domain)
    {
        $tenant = Tenant::where('domain', $domain)->where('is_active', true)->firstOrFail();

        return view('public.calculator', [
            'tenant' => $tenant
        ]);
    }

    public function calculate(Request $request, string $domain)
    {
        $tenant = Tenant::where('domain', $domain)->where('is_active', true)->firstOrFail();

        $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
            'invoice_value' => 'required|numeric|min:0',
        ]);

        try {
            // Optional: Check if origin/destination needs geocoding for better accuracy, 
            // but FreightCalculationService usually handles basic string matching or ZIP.
            // If MapsService is needed for distance:
            // $distance = $this->mapsService->getDistanceMatrix($request->origin, $request->destination);

            $result = $this->freightService->calculate(
                $tenant,
                $request->destination,
                $request->weight,
                0, // Cubage not mandatory for public calc (simplification)
                $request->invoice_value
            );

            return response()->json([
                'success' => true,
                'total' => number_format($result['total'], 2, ',', '.'),
                'details' => $result
            ]);

        } catch (\Exception $e) {
            Log::error("Public Calc Error ({$tenant->domain}): " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível calcular o frete para este destino. Entre em contato.'
            ], 422);
        }
    }
}
