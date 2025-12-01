<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Client;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display reports page
     */
    public function index()
    {
        $tenant = Auth::user()->tenant;
        $clients = Client::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        
        return view('reports.index', compact('clients'));
    }

    /**
     * Generate and download shipment report
     */
    public function shipments(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'status' => $request->get('status'),
            'client_id' => $request->get('client_id'),
        ];

        $query = Shipment::where('tenant_id', $tenant->id)
            ->with(['senderClient', 'receiverClient', 'route', 'driver']);

        if ($filters['date_from']) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }
        if ($filters['client_id']) {
            $query->where(function($q) use ($filters) {
                $q->where('sender_client_id', $filters['client_id'])
                  ->orWhere('receiver_client_id', $filters['client_id']);
            });
        }

        $shipments = $query->orderBy('created_at', 'desc')->get();

        $format = $request->get('format', 'pdf');

        if ($format === 'excel') {
            return $this->exportShipmentsExcel($shipments, $filters);
        } else {
            return $this->exportShipmentsPdf($shipments, $filters);
        }
    }

    /**
     * Generate and download financial report
     */
    public function financial(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'type' => $request->get('type', 'all'), // all, revenue, expenses
        ];

        $format = $request->get('format', 'pdf');

        if ($format === 'excel') {
            return $this->exportFinancialExcel($tenant, $filters);
        } else {
            return $this->exportFinancialPdf($tenant, $filters);
        }
    }

    /**
     * Export shipments to PDF (HTML view for printing)
     */
    protected function exportShipmentsPdf($shipments, $filters)
    {
        return view('reports.shipments-pdf', [
            'shipments' => $shipments,
            'filters' => $filters,
            'tenant' => Auth::user()->tenant,
        ]);
    }

    /**
     * Export shipments to Excel (CSV format)
     */
    protected function exportShipmentsExcel($shipments, $filters)
    {
        $filename = 'shipments-report-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($shipments) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Tracking Number',
                'Title',
                'Sender',
                'Receiver',
                'Status',
                'Pickup Date',
                'Delivery Date',
                'Value',
                'Weight (kg)',
                'Created At'
            ], ';');

            // Data
            foreach ($shipments as $shipment) {
                fputcsv($file, [
                    $shipment->tracking_number,
                    $shipment->title,
                    $shipment->senderClient->name ?? 'N/A',
                    $shipment->receiverClient->name ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $shipment->status)),
                    $shipment->pickup_date ? $shipment->pickup_date->format('d/m/Y') : 'N/A',
                    $shipment->delivery_date ? $shipment->delivery_date->format('d/m/Y') : 'N/A',
                    $shipment->value ? number_format($shipment->value, 2, ',', '.') : '0,00',
                    $shipment->weight ? number_format($shipment->weight, 2, ',', '.') : '0,00',
                    $shipment->created_at->format('d/m/Y H:i')
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export financial to PDF (HTML view for printing)
     */
    protected function exportFinancialPdf($tenant, $filters)
    {
        $invoices = Invoice::where('tenant_id', $tenant->id);
        $expenses = Expense::where('tenant_id', $tenant->id);

        if ($filters['date_from']) {
            $invoices->where('created_at', '>=', $filters['date_from']);
            $expenses->where('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $invoices->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
            $expenses->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        $invoicesData = $invoices->with('client')->orderBy('created_at', 'desc')->get();
        $expensesData = $expenses->orderBy('created_at', 'desc')->get();

        return view('reports.financial-pdf', [
            'invoices' => $invoicesData,
            'expenses' => $expensesData,
            'filters' => $filters,
            'tenant' => $tenant,
        ]);
    }

    /**
     * Export financial to Excel (CSV format)
     */
    protected function exportFinancialExcel($tenant, $filters)
    {
        $filename = 'financial-report-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($tenant, $filters) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            if ($filters['type'] !== 'expenses') {
                // Invoices Section
                fputcsv($file, ['=== INVOICES (REVENUE) ==='], ';');
                fputcsv($file, [
                    'Invoice Number',
                    'Client',
                    'Amount',
                    'Status',
                    'Due Date',
                    'Created At'
                ], ';');

                $invoices = Invoice::where('tenant_id', $tenant->id);
                if ($filters['date_from']) {
                    $invoices->where('created_at', '>=', $filters['date_from']);
                }
                if ($filters['date_to']) {
                    $invoices->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
                }
                $invoicesData = $invoices->with('client')->orderBy('created_at', 'desc')->get();

                foreach ($invoicesData as $invoice) {
                    fputcsv($file, [
                        $invoice->invoice_number,
                        $invoice->client->name ?? 'N/A',
                        number_format($invoice->total_amount, 2, ',', '.'),
                        ucfirst($invoice->status),
                        $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A',
                        $invoice->created_at->format('d/m/Y H:i')
                    ], ';');
                }
                
                fputcsv($file, [], ';'); // Empty line
            }

            if ($filters['type'] !== 'revenue') {
                // Expenses Section
                fputcsv($file, ['=== EXPENSES ==='], ';');
                fputcsv($file, [
                    'Description',
                    'Amount',
                    'Category',
                    'Status',
                    'Due Date',
                    'Created At'
                ], ';');

                $expenses = Expense::where('tenant_id', $tenant->id);
                if ($filters['date_from']) {
                    $expenses->where('created_at', '>=', $filters['date_from']);
                }
                if ($filters['date_to']) {
                    $expenses->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
                }
                $expensesData = $expenses->orderBy('created_at', 'desc')->get();

                foreach ($expensesData as $expense) {
                    fputcsv($file, [
                        $expense->description,
                        number_format($expense->amount, 2, ',', '.'),
                        $expense->category ?? 'N/A',
                        ucfirst($expense->status),
                        $expense->due_date ? $expense->due_date->format('d/m/Y') : 'N/A',
                        $expense->created_at->format('d/m/Y H:i')
                    ], ';');
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}

