<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Salesperson;
use App\Models\Shipment;
use App\Models\Vehicle;
use App\Models\CteXml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Global search across Clients, Salespeople, Drivers, Shipments, Vehicles, Routes and CT-e XMLs.
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
        $like     = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $results  = collect();

        // 1. Clients (by name, email, cnpj, phone)
        $clients = Client::where('tenant_id', $tenantId)
            ->where(function ($query) use ($q, $like) {
                $query->where('name', $like, "%{$q}%")
                      ->orWhere('email', $like, "%{$q}%")
                      ->orWhere('cnpj', $like, "%{$q}%")
                      ->orWhere('phone', $like, "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'email', 'cnpj'])
            ->map(fn($c) => [
                'type'     => 'client',
                'icon'     => 'fa-user-friends',
                'label'    => $c->name,
                'sublabel' => 'Cliente · ' . ($c->cnpj ?? $c->email ?? ''),
                'url'      => route('clients.show', $c->id),
            ]);

        $results = $results->merge($clients);

        // 2. Salespeople (Vendedores by name, email, phone, document)
        $salespeople = Salesperson::where('tenant_id', $tenantId)
            ->where(function ($query) use ($q, $like) {
                $query->where('name', $like, "%{$q}%")
                      ->orWhere('email', $like, "%{$q}%")
                      ->orWhere('phone', $like, "%{$q}%")
                      ->orWhere('document', $like, "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'email', 'phone'])
            ->map(fn($sp) => [
                'type'     => 'salesperson',
                'icon'     => 'fa-store',
                'label'    => $sp->name,
                'sublabel' => 'Vendedor' . ($sp->email ? ' · ' . $sp->email : ($sp->phone ? ' · ' . $sp->phone : '')),
                'url'      => route('salespeople.show', $sp->id),
            ]);

        $results = $results->merge($salespeople);

        // 3. Drivers (by name, document, phone, vehicle_plate, cnh_number)
        $drivers = Driver::where('tenant_id', $tenantId)
            ->where(function ($query) use ($q, $like) {
                $query->where('name', $like, "%{$q}%")
                      ->orWhere('document', $like, "%{$q}%")
                      ->orWhere('phone', $like, "%{$q}%")
                      ->orWhere('vehicle_plate', $like, "%{$q}%")
                      ->orWhere('cnh_number', $like, "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'phone'])
            ->map(fn($d) => [
                'type'     => 'driver',
                'icon'     => 'fa-user-tie',
                'label'    => $d->name,
                'sublabel' => 'Motorista' . ($d->phone ? ' · ' . $d->phone : ''),
                'url'      => route('drivers.show', $d->id),
            ]);

        $results = $results->merge($drivers);

        // 4. Shipments (by tracking_number, title, tracking_code, recipient_name, cte_number, invoice_number, nf_key, delivery_city, pickup_city, or id)
        $shipments = Shipment::where('tenant_id', $tenantId)
            ->where(function ($query) use ($q, $like) {
                if (is_numeric($q)) {
                    $query->where('id', (int)$q);
                }
                $query->orWhere('tracking_number', $like, "%{$q}%")
                      ->orWhere('title', $like, "%{$q}%")
                      ->orWhere('tracking_code', $like, "%{$q}%")
                      ->orWhere('recipient_name', $like, "%{$q}%")
                      ->orWhere('cte_number', $like, "%{$q}%")
                      ->orWhere('invoice_number', $like, "%{$q}%")
                      ->orWhere('nf_key', $like, "%{$q}%")
                      ->orWhere('delivery_city', $like, "%{$q}%")
                      ->orWhere('pickup_city', $like, "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'tracking_number', 'title', 'status', 'recipient_name'])
            ->map(fn($s) => [
                'type'     => 'shipment',
                'icon'     => 'fa-truck-loading',
                'label'    => 'Carga ' . ($s->tracking_number ?? $s->id) . ($s->recipient_name ? ' (' . $s->recipient_name . ')' : ''),
                'sublabel' => $s->title ? ($s->title . ' · ' . ucfirst(str_replace('_', ' ', $s->status))) : ucfirst(str_replace('_', ' ', $s->status)),
                'url'      => route('shipments.show', $s->id),
            ]);

        $results = $results->merge($shipments);

        // 5. Vehicles (by plate, model, brand, renavam, chassis)
        $vehicles = Vehicle::where('tenant_id', $tenantId)
            ->where(function ($query) use ($q, $like) {
                $query->where('plate', $like, "%{$q}%")
                      ->orWhere('model', $like, "%{$q}%")
                      ->orWhere('brand', $like, "%{$q}%")
                      ->orWhere('renavam', $like, "%{$q}%")
                      ->orWhere('chassis', $like, "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'plate', 'model', 'brand'])
            ->map(fn($v) => [
                'type'     => 'vehicle',
                'icon'     => 'fa-truck',
                'label'    => 'Veículo Placa ' . $v->plate,
                'sublabel' => ($v->model ? $v->model : 'Veículo') . ($v->brand ? ' · ' . $v->brand : ''),
                'url'      => route('vehicles.show', $v->id),
            ]);

        $results = $results->merge($vehicles);

        // 6. Routes (by name, start_address, start_city or id)
        $routes = Route::where('tenant_id', $tenantId)
            ->where(function ($query) use ($q, $like) {
                if (is_numeric($q)) {
                    $query->where('id', (int)$q);
                }
                $query->orWhere('name', $like, "%{$q}%")
                      ->orWhere('start_address', $like, "%{$q}%")
                      ->orWhere('start_city', $like, "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'status'])
            ->map(fn($r) => [
                'type'     => 'route',
                'icon'     => 'fa-route',
                'label'    => $r->name ?? ('Rota #' . $r->id),
                'sublabel' => 'Rota · ' . ucfirst(str_replace('_', ' ', $r->status)),
                'url'      => route('routes.show', $r->id),
            ]);

        $results = $results->merge($routes);

        // 7. CT-e XMLs (by cte_number, access_key)
        $cteXmls = CteXml::where('tenant_id', $tenantId)
            ->where(function ($query) use ($q, $like) {
                $query->where('cte_number', $like, "%{$q}%")
                      ->orWhere('access_key', $like, "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'cte_number', 'access_key', 'is_used'])
            ->map(fn($xml) => [
                'type'     => 'cte_xml',
                'icon'     => 'fa-file-code',
                'label'    => 'CT-e Nº ' . $xml->cte_number,
                'sublabel' => ($xml->is_used ? 'Usado em Rota' : 'A Usar (Disponível)') . ($xml->access_key ? ' · Key: ' . substr($xml->access_key, 0, 14) . '...' : ''),
                'url'      => route('cte-xmls.index') . '?search=' . urlencode($xml->cte_number),
            ]);

        $results = $results->merge($cteXmls);

        if ($request->expectsJson()) {
            return response()->json(['results' => $results->values(), 'query' => $q]);
        }

        return view('search.index', ['results' => $results, 'query' => $q]);
    }
}
