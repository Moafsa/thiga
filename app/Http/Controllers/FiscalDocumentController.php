<?php

namespace App\Http\Controllers;

use App\Models\FiscalDocument;
use App\Models\Client;
use App\Models\Route;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FiscalDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of CT-e documents
     */
    public function indexCtes(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $query = FiscalDocument::where('tenant_id', $tenant->id)
            ->where('document_type', 'cte')
            ->with(['shipment.senderClient', 'shipment.receiverClient']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by client (sender)
        if ($request->filled('client_id')) {
            $query->whereHas('shipment', function($q) use ($request) {
                $q->where('sender_client_id', $request->client_id);
            });
        }

        // Search by access key or number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('access_key', 'like', "%{$search}%")
                  ->orWhere('mitt_number', 'like', "%{$search}%");
            });
        }

        // Ordering
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        
        $allowedOrderBy = ['created_at', 'authorized_at', 'status', 'mitt_number'];
        if (!in_array($orderBy, $allowedOrderBy)) {
            $orderBy = 'created_at';
        }
        
        $orderDir = strtolower($orderDir) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($orderBy, $orderDir);

        $ctes = $query->paginate(20)->withQueryString();

        // Get clients for filter dropdown
        $clients = Client::where('tenant_id', $tenant->id)
            ->listed()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Status options for filter
        $statusOptions = [
            'pending' => 'Pending',
            'validating' => 'Validating',
            'processing' => 'Processing',
            'authorized' => 'Authorized',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            'error' => 'Error',
        ];

        return view('fiscal.ctes.index', compact('ctes', 'clients', 'statusOptions'));
    }

    /**
     * Display the specified CT-e document
     */
    public function showCte(FiscalDocument $fiscalDocument)
    {
        $this->authorizeAccess($fiscalDocument);

        if (!$fiscalDocument->isCte()) {
            abort(404, 'Document is not a CT-e');
        }

        $fiscalDocument->load([
            'shipment.senderClient',
            'shipment.receiverClient',
            'shipment.route.driver',
            'shipment.route.vehicle',
        ]);

        return view('fiscal.ctes.show', compact('fiscalDocument'));
    }

    /**
     * Filter CT-es via AJAX
     */
    public function filterCtes(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = FiscalDocument::where('tenant_id', $tenant->id)
            ->where('document_type', 'cte')
            ->with(['shipment.senderClient', 'shipment.receiverClient']);

        // Apply same filters as index method
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('client_id')) {
            $query->whereHas('shipment', function($q) use ($request) {
                $q->where('sender_client_id', $request->client_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('access_key', 'like', "%{$search}%")
                  ->orWhere('mitt_number', 'like', "%{$search}%");
            });
        }

        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        
        $allowedOrderBy = ['created_at', 'authorized_at', 'status', 'mitt_number'];
        if (!in_array($orderBy, $allowedOrderBy)) {
            $orderBy = 'created_at';
        }
        
        $orderDir = strtolower($orderDir) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($orderBy, $orderDir);

        $ctes = $query->paginate(20);

        return response()->json([
            'html' => view('fiscal.ctes.partials.cte-table', compact('ctes'))->render(),
            'pagination' => [
                'current_page' => $ctes->currentPage(),
                'last_page' => $ctes->lastPage(),
                'total' => $ctes->total(),
            ],
        ]);
    }

    /**
     * Display a listing of MDF-e documents
     */
    public function indexMdfes(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $query = FiscalDocument::where('tenant_id', $tenant->id)
            ->where('document_type', 'mdfe')
            ->with(['route.driver', 'route.vehicle']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by driver
        if ($request->filled('driver_id')) {
            $query->whereHas('route', function($q) use ($request) {
                $q->where('driver_id', $request->driver_id);
            });
        }

        // Filter by route
        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        // Search by access key or number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('access_key', 'like', "%{$search}%")
                  ->orWhere('mitt_number', 'like', "%{$search}%");
            });
        }

        // Ordering
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        
        $allowedOrderBy = ['created_at', 'authorized_at', 'status', 'mitt_number'];
        if (!in_array($orderBy, $allowedOrderBy)) {
            $orderBy = 'created_at';
        }
        
        $orderDir = strtolower($orderDir) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($orderBy, $orderDir);

        $mdfes = $query->paginate(20)->withQueryString();

        // Get drivers for filter dropdown
        $drivers = Driver::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get routes for filter dropdown
        $routes = Route::where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        // Status options for filter
        $statusOptions = [
            'pending' => 'Pending',
            'validating' => 'Validating',
            'processing' => 'Processing',
            'authorized' => 'Authorized',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            'error' => 'Error',
        ];

        return view('fiscal.mdfes.index', compact('mdfes', 'drivers', 'routes', 'statusOptions'));
    }

    /**
     * Display the specified MDF-e document
     */
    public function showMdfe(FiscalDocument $fiscalDocument)
    {
        $this->authorizeAccess($fiscalDocument);

        if (!$fiscalDocument->isMdfe()) {
            abort(404, 'Document is not a MDF-e');
        }

        $fiscalDocument->load([
            'route.driver',
            'route.vehicle',
            'route.shipments.senderClient',
            'route.shipments.receiverClient',
        ]);

        // Get CT-es linked to this MDF-e (CT-es from shipments in the route)
        $ctes = FiscalDocument::where('tenant_id', $fiscalDocument->tenant_id)
            ->where('document_type', 'cte')
            ->whereHas('shipment', function($q) use ($fiscalDocument) {
                $q->where('route_id', $fiscalDocument->route_id);
            })
            ->with(['shipment.senderClient', 'shipment.receiverClient'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('fiscal.mdfes.show', compact('fiscalDocument', 'ctes'));
    }

    /**
     * Filter MDF-es via AJAX
     */
    public function filterMdfes(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = FiscalDocument::where('tenant_id', $tenant->id)
            ->where('document_type', 'mdfe')
            ->with(['route.driver', 'route.vehicle']);

        // Apply same filters as index method
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('driver_id')) {
            $query->whereHas('route', function($q) use ($request) {
                $q->where('driver_id', $request->driver_id);
            });
        }

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('access_key', 'like', "%{$search}%")
                  ->orWhere('mitt_number', 'like', "%{$search}%");
            });
        }

        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        
        $allowedOrderBy = ['created_at', 'authorized_at', 'status', 'mitt_number'];
        if (!in_array($orderBy, $allowedOrderBy)) {
            $orderBy = 'created_at';
        }
        
        $orderDir = strtolower($orderDir) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($orderBy, $orderDir);

        $mdfes = $query->paginate(20);

        return response()->json([
            'html' => view('fiscal.mdfes.partials.mdfe-table', compact('mdfes'))->render(),
            'pagination' => [
                'current_page' => $mdfes->currentPage(),
                'last_page' => $mdfes->lastPage(),
                'total' => $mdfes->total(),
            ],
        ]);
    }

    /**
     * Authorize access to fiscal document
     */
    protected function authorizeAccess(FiscalDocument $fiscalDocument)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant || $fiscalDocument->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized access to this fiscal document.');
        }
    }
}

