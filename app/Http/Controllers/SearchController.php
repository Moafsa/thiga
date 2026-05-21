<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Global search across Clients, Shipments, Drivers, Routes.
     * Returns JSON for AJAX or full page for direct GET.
     */
    public function index(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $tenant = Auth::user()->tenant;

        if (!$tenant || strlen($q) < 2) {
            if ($request->expectsJson()) {
                return response()->json(['results' => [], 'query' => $q]);
            }
            return view('search.index', ['results' => collect(), 'query' => $q]);
        }

        $tenantId = $tenant->id;
        $results  = collect();

        // Clients
        $clients = Client::where('tenant_id', $tenantId)
            ->where(function ($query) use ($q) {
                $query->where('name', 'ilike', "%{$q}%")
                      ->orWhere('email', 'ilike', "%{$q}%")
                      ->orWhere('cnpj', 'ilike', "%{$q}%")
                      ->orWhere('phone', 'ilike', "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'email', 'cnpj'])
            ->map(fn($c) => [
                'type'     => 'client',
                'icon'     => 'fa-user-friends',
                'label'    => $c->name,
                'sublabel' => $c->cnpj ?? $c->email ?? '',
                'url'      => route('clients.show', $c->id),
            ]);

        $results = $results->merge($clients);

        // Shipments (by tracking number or title)
        $shipments = Shipment::where('tenant_id', $tenantId)
            ->where(function ($query) use ($q) {
                $query->where('tracking_number', 'ilike', "%{$q}%")
                      ->orWhere('title', 'ilike', "%{$q}%")
                      ->orWhere('tracking_code', 'ilike', "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'tracking_number', 'title', 'status'])
            ->map(fn($s) => [
                'type'     => 'shipment',
                'icon'     => 'fa-truck-loading',
                'label'    => $s->tracking_number,
                'sublabel' => $s->title . ' · ' . ucfirst(str_replace('_', ' ', $s->status)),
                'url'      => route('shipments.show', $s->id),
            ]);

        $results = $results->merge($shipments);

        // Drivers
        $drivers = Driver::where('tenant_id', $tenantId)
            ->where(function ($query) use ($q) {
                $query->where('name', 'ilike', "%{$q}%")
                      ->orWhere('cpf', 'ilike', "%{$q}%")
                      ->orWhere('phone', 'ilike', "%{$q}%");
            })
            ->limit(3)
            ->get(['id', 'name', 'phone'])
            ->map(fn($d) => [
                'type'     => 'driver',
                'icon'     => 'fa-user-tie',
                'label'    => $d->name,
                'sublabel' => $d->phone ?? '',
                'url'      => route('drivers.show', $d->id),
            ]);

        $results = $results->merge($drivers);

        // Routes
        $routes = Route::where('tenant_id', $tenantId)
            ->where('name', 'ilike', "%{$q}%")
            ->limit(3)
            ->get(['id', 'name', 'status'])
            ->map(fn($r) => [
                'type'     => 'route',
                'icon'     => 'fa-route',
                'label'    => $r->name,
                'sublabel' => 'Rota · ' . ucfirst(str_replace('_', ' ', $r->status)),
                'url'      => route('routes.show', $r->id),
            ]);

        $results = $results->merge($routes);

        if ($request->expectsJson()) {
            return response()->json(['results' => $results->values(), 'query' => $q]);
        }

        return view('search.index', ['results' => $results, 'query' => $q]);
    }
}
