<?php

namespace App\Http\Controllers;

use App\Models\FiscalDocument;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class FiscalReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display fiscal reports index page
     */
    public function index()
    {
        $tenant = Auth::user()->tenant;
        $clients = Client::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        $drivers = Driver::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        
        return view('fiscal.reports.index', compact('clients', 'drivers'));
    }

    /**
     * Generate CT-es report
     */
    public function ctes(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'status' => $request->get('status'),
            'client_id' => $request->get('client_id'),
        ];

        $query = FiscalDocument::where('tenant_id', $tenant->id)
            ->where('document_type', 'cte')
            ->with(['shipment.senderClient', 'shipment.receiverClient']);

        if ($filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }
        if ($filters['client_id']) {
            $query->whereHas('shipment', function($q) use ($filters) {
                $q->where('sender_client_id', $filters['client_id']);
            });
        }

        $ctes = $query->orderBy('created_at', 'desc')->get();

        $format = $request->get('format', 'pdf');

        if ($format === 'excel') {
            return $this->exportCtesExcel($ctes, $filters);
        } else {
            return $this->exportCtesPdf($ctes, $filters);
        }
    }

    /**
     * Generate MDF-es report
     */
    public function mdfes(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'status' => $request->get('status'),
            'driver_id' => $request->get('driver_id'),
            'route_id' => $request->get('route_id'),
        ];

        $query = FiscalDocument::where('tenant_id', $tenant->id)
            ->where('document_type', 'mdfe')
            ->with(['route.driver', 'route.vehicle']);

        if ($filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }
        if ($filters['driver_id']) {
            $query->whereHas('route', function($q) use ($filters) {
                $q->where('driver_id', $filters['driver_id']);
            });
        }
        if ($filters['route_id']) {
            $query->where('route_id', $filters['route_id']);
        }

        $mdfes = $query->orderBy('created_at', 'desc')->get();

        $format = $request->get('format', 'pdf');

        if ($format === 'excel') {
            return $this->exportMdfesExcel($mdfes, $filters);
        } else {
            return $this->exportMdfesPdf($mdfes, $filters);
        }
    }

    /**
     * Generate consolidated fiscal report
     */
    public function consolidated(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        $ctesQuery = FiscalDocument::where('tenant_id', $tenant->id)
            ->where('document_type', 'cte');
        
        $mdfesQuery = FiscalDocument::where('tenant_id', $tenant->id)
            ->where('document_type', 'mdfe');

        if ($filters['date_from']) {
            $ctesQuery->whereDate('created_at', '>=', $filters['date_from']);
            $mdfesQuery->whereDate('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $ctesQuery->whereDate('created_at', '<=', $filters['date_to']);
            $mdfesQuery->whereDate('created_at', '<=', $filters['date_to']);
        }

        $ctes = $ctesQuery->with(['shipment.senderClient'])->orderBy('created_at', 'desc')->get();
        $mdfes = $mdfesQuery->with(['route.driver'])->orderBy('created_at', 'desc')->get();

        // Calculate metrics
        $metrics = [
            'total_ctes' => $ctes->count(),
            'authorized_ctes' => $ctes->where('status', 'authorized')->count(),
            'pending_ctes' => $ctes->where('status', 'pending')->count(),
            'rejected_ctes' => $ctes->where('status', 'rejected')->count(),
            'total_mdfes' => $mdfes->count(),
            'authorized_mdfes' => $mdfes->where('status', 'authorized')->count(),
            'pending_mdfes' => $mdfes->where('status', 'pending')->count(),
            'rejected_mdfes' => $mdfes->where('status', 'rejected')->count(),
        ];

        // Prepare data for charts
        $ctesByStatus = $ctes->groupBy('status')->map(function($group) {
            return $group->count();
        })->toArray();

        $mdfesByStatus = $mdfes->groupBy('status')->map(function($group) {
            return $group->count();
        })->toArray();

        // Documents by month
        $ctesByMonth = $ctes->groupBy(function($cte) {
            return $cte->created_at->format('Y-m');
        })->map(function($group) {
            return $group->count();
        })->toArray();

        $mdfesByMonth = $mdfes->groupBy(function($mdfe) {
            return $mdfe->created_at->format('Y-m');
        })->map(function($group) {
            return $group->count();
        })->toArray();

        $format = $request->get('format', 'web');

        if ($format === 'excel') {
            return $this->exportConsolidatedExcel($ctes, $mdfes, $metrics, $filters);
        } elseif ($format === 'pdf') {
            return view('fiscal.reports.consolidated-pdf', [
                'ctes' => $ctes,
                'mdfes' => $mdfes,
                'metrics' => $metrics,
                'filters' => $filters,
                'tenant' => $tenant,
            ]);
        } else {
            return view('fiscal.reports.consolidated', [
                'ctes' => $ctes,
                'mdfes' => $mdfes,
                'metrics' => $metrics,
                'filters' => $filters,
                'tenant' => $tenant,
                'ctesByStatus' => $ctesByStatus,
                'mdfesByStatus' => $mdfesByStatus,
                'ctesByMonth' => $ctesByMonth,
                'mdfesByMonth' => $mdfesByMonth,
            ]);
        }
    }

    /**
     * Export CT-es to PDF
     */
    protected function exportCtesPdf($ctes, $filters)
    {
        return view('fiscal.reports.ctes-pdf', [
            'ctes' => $ctes,
            'filters' => $filters,
            'tenant' => Auth::user()->tenant,
        ]);
    }

    /**
     * Export CT-es to Excel (CSV format)
     */
    protected function exportCtesExcel($ctes, $filters)
    {
        $filename = 'ctes-report-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($ctes) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Number',
                'Access Key',
                'Status',
                'Sender Client',
                'Receiver Client',
                'Tracking Number',
                'Created At',
                'Authorized At',
            ], ';');

            // Data
            foreach ($ctes as $cte) {
                fputcsv($file, [
                    $cte->mitt_number ?? 'N/A',
                    $cte->access_key ?? 'N/A',
                    $cte->status_label,
                    $cte->shipment->senderClient->name ?? 'N/A',
                    $cte->shipment->receiverClient->name ?? 'N/A',
                    $cte->shipment->tracking_number ?? 'N/A',
                    $cte->created_at->format('d/m/Y H:i'),
                    $cte->authorized_at ? $cte->authorized_at->format('d/m/Y H:i') : 'N/A',
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export MDF-es to PDF
     */
    protected function exportMdfesPdf($mdfes, $filters)
    {
        return view('fiscal.reports.mdfes-pdf', [
            'mdfes' => $mdfes,
            'filters' => $filters,
            'tenant' => Auth::user()->tenant,
        ]);
    }

    /**
     * Export MDF-es to Excel (CSV format)
     */
    protected function exportMdfesExcel($mdfes, $filters)
    {
        $filename = 'mdfes-report-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($mdfes) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Number',
                'Access Key',
                'Status',
                'Route',
                'Driver',
                'Vehicle',
                'CT-es Count',
                'Created At',
                'Authorized At',
            ], ';');

            // Data
            foreach ($mdfes as $mdfe) {
                // Count CT-es linked to this MDF-e
                $cteCount = FiscalDocument::where('tenant_id', $mdfe->tenant_id)
                    ->where('document_type', 'cte')
                    ->whereHas('shipment', function($q) use ($mdfe) {
                        $q->where('route_id', $mdfe->route_id);
                    })
                    ->count();

                fputcsv($file, [
                    $mdfe->mitt_number ?? 'N/A',
                    $mdfe->access_key ?? 'N/A',
                    $mdfe->status_label,
                    $mdfe->route->name ?? 'N/A',
                    $mdfe->route->driver->name ?? 'N/A',
                    $mdfe->route->vehicle ? ($mdfe->route->vehicle->plate . ' - ' . $mdfe->route->vehicle->model) : 'N/A',
                    $mdfe->created_at->format('d/m/Y H:i'),
                    $mdfe->authorized_at ? $mdfe->authorized_at->format('d/m/Y H:i') : 'N/A',
                    $cteCount,
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export consolidated report to Excel (CSV format)
     */
    protected function exportConsolidatedExcel($ctes, $mdfes, $metrics, $filters)
    {
        $filename = 'fiscal-consolidated-report-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($ctes, $mdfes, $metrics) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Metrics Section
            fputcsv($file, ['=== FISCAL DOCUMENTS METRICS ==='], ';');
            fputcsv($file, ['CT-es Total', $metrics['total_ctes']], ';');
            fputcsv($file, ['CT-es Authorized', $metrics['authorized_ctes']], ';');
            fputcsv($file, ['CT-es Pending', $metrics['pending_ctes']], ';');
            fputcsv($file, ['CT-es Rejected', $metrics['rejected_ctes']], ';');
            fputcsv($file, ['MDF-es Total', $metrics['total_mdfes']], ';');
            fputcsv($file, ['MDF-es Authorized', $metrics['authorized_mdfes']], ';');
            fputcsv($file, ['MDF-es Pending', $metrics['pending_mdfes']], ';');
            fputcsv($file, ['MDF-es Rejected', $metrics['rejected_mdfes']], ';');
            fputcsv($file, [], ';'); // Empty line

            // CT-es Section
            fputcsv($file, ['=== CT-es ==='], ';');
            fputcsv($file, [
                'Number',
                'Access Key',
                'Status',
                'Sender Client',
                'Created At',
            ], ';');

            foreach ($ctes as $cte) {
                fputcsv($file, [
                    $cte->mitt_number ?? 'N/A',
                    $cte->access_key ?? 'N/A',
                    $cte->status_label,
                    $cte->shipment->senderClient->name ?? 'N/A',
                    $cte->created_at->format('d/m/Y H:i'),
                ], ';');
            }
            
            fputcsv($file, [], ';'); // Empty line

            // MDF-es Section
            fputcsv($file, ['=== MDF-es ==='], ';');
            fputcsv($file, [
                'Number',
                'Access Key',
                'Status',
                'Route',
                'Driver',
                'Created At',
            ], ';');

            foreach ($mdfes as $mdfe) {
                fputcsv($file, [
                    $mdfe->mitt_number ?? 'N/A',
                    $mdfe->access_key ?? 'N/A',
                    $mdfe->status_label,
                    $mdfe->route->name ?? 'N/A',
                    $mdfe->route->driver->name ?? 'N/A',
                    $mdfe->created_at->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}

