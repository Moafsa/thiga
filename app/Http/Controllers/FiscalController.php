<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Route;
use App\Models\FiscalDocument;
use App\Services\FiscalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FiscalController extends Controller
{
    protected FiscalService $fiscalService;

    public function __construct(FiscalService $fiscalService)
    {
        $this->middleware('auth');
        $this->fiscalService = $fiscalService;
    }

    /**
     * Request CT-e issuance for a shipment
     */
    public function issueCte(Request $request, Shipment $shipment)
    {
        $this->authorizeAccess($shipment);

        try {
            $fiscalDocument = $this->fiscalService->requestCteIssuance($shipment);

            return redirect()->route('shipments.show', $shipment)
                ->with('success', 'CT-e emission requested. Processing in background...');
        } catch (\Exception $e) {
            return redirect()->route('shipments.show', $shipment)
                ->withErrors(['error' => 'Failed to request CT-e: ' . $e->getMessage()]);
        }
    }

    /**
     * Request MDF-e issuance for a route
     */
    public function issueMdfe(Request $request, Route $route)
    {
        $this->authorizeAccess($route);

        try {
            $fiscalDocument = $this->fiscalService->requestMdfeIssuance($route);

            return redirect()->route('routes.show', $route)
                ->with('success', 'MDF-e emission requested. Processing in background...');
        } catch (\Exception $e) {
            return redirect()->route('routes.show', $route)
                ->withErrors(['error' => 'Failed to request MDF-e: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel CT-e
     */
    public function cancelCte(Request $request, FiscalDocument $fiscalDocument)
    {
        $this->authorizeAccess($fiscalDocument);

        $request->validate([
            'justification' => 'required|string|min:15|max:255',
        ]);

        try {
            $this->fiscalService->cancelCte($fiscalDocument, $request->justification);

            return back()->with('success', 'CT-e cancelled successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to cancel CT-e: ' . $e->getMessage()]);
        }
    }

    /**
     * Get fiscal document status (AJAX)
     */
    public function getStatus(FiscalDocument $fiscalDocument)
    {
        $this->authorizeAccess($fiscalDocument);

        return response()->json([
            'id' => $fiscalDocument->id,
            'type' => $fiscalDocument->document_type,
            'status' => $fiscalDocument->status,
            'status_label' => $fiscalDocument->status_label,
            'access_key' => $fiscalDocument->access_key,
            'mitt_number' => $fiscalDocument->mitt_number,
            'pdf_url' => $fiscalDocument->pdf_url,
            'xml_url' => $fiscalDocument->xml_url,
            'error_message' => $fiscalDocument->error_message,
            'authorized_at' => $fiscalDocument->authorized_at?->toIso8601String(),
            'created_at' => $fiscalDocument->created_at->toIso8601String(),
        ]);
    }

    /**
     * Sync CT-e from Mitt
     */
    public function syncCte(Request $request, Shipment $shipment)
    {
        $this->authorizeAccess($shipment);

        try {
            $fiscalDocument = $this->fiscalService->syncShipmentCte($shipment);

            return redirect()->route('shipments.show', $shipment)
                ->with('success', 'CT-e synchronized successfully from Mitt.');
        } catch (\Exception $e) {
            return redirect()->route('shipments.show', $shipment)
                ->withErrors(['error' => 'Failed to sync CT-e: ' . $e->getMessage()]);
        }
    }

    /**
     * Sync MDF-e from Mitt
     */
    public function syncMdfe(Request $request, Route $route)
    {
        $this->authorizeAccess($route);

        try {
            $fiscalDocument = $this->fiscalService->syncRouteMdfe($route);

            return redirect()->route('routes.show', $route)
                ->with('success', 'MDF-e synchronized successfully from Mitt.');
        } catch (\Exception $e) {
            return redirect()->route('routes.show', $route)
                ->withErrors(['error' => 'Failed to sync MDF-e: ' . $e->getMessage()]);
        }
    }

    /**
     * Authorize access to shipment
     */
    protected function authorizeAccess($resource)
    {
        $tenant = Auth::user()->tenant;
        
        if ($resource instanceof Shipment) {
            if ($resource->tenant_id !== $tenant->id) {
                abort(403, 'Unauthorized access to this shipment.');
            }
        } elseif ($resource instanceof Route) {
            if ($resource->tenant_id !== $tenant->id) {
                abort(403, 'Unauthorized access to this route.');
            }
        } elseif ($resource instanceof FiscalDocument) {
            if ($resource->tenant_id !== $tenant->id) {
                abort(403, 'Unauthorized access to this fiscal document.');
            }
        }
    }
}







