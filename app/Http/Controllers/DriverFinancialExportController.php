<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Dompdf\Dompdf;
use Dompdf\Options;

class DriverFinancialExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Export financial history to Excel
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not registered as a driver.');
        }

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Get all financial history (no pagination for export)
        $payments = $driver->getRecentPayments($startDate, $endDate, 10000)->items();
        $expenses = $driver->getRecentExpenses($startDate, $endDate, 10000)->items();

        $history = collect()
            ->merge(collect($payments)->map(function ($payment) {
                return [
                    'type' => 'Pagamento',
                    'date' => $payment['date']->format('d/m/Y'),
                    'description' => "Pagamento - {$payment['route_name']}",
                    'amount' => $payment['amount'],
                    'details' => "{$payment['diarias_count']} diária(s) × R$ " . number_format($payment['diaria_value'], 2, ',', '.'),
                ];
            }))
            ->merge(collect($expenses)->map(function ($expense) {
                return [
                    'type' => 'Despesa',
                    'date' => $expense['date']->format('d/m/Y'),
                    'description' => $expense['description'],
                    'amount' => -$expense['amount'],
                    'details' => $expense['category'] ?? 'Sem categoria',
                ];
            }))
            ->sortByDesc(function ($item) {
                return $item['date'];
            })
            ->values();

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Histórico Financeiro');

        // Headers
        $sheet->setCellValue('A1', 'Data');
        $sheet->setCellValue('B1', 'Tipo');
        $sheet->setCellValue('C1', 'Descrição');
        $sheet->setCellValue('D1', 'Valor');
        $sheet->setCellValue('E1', 'Detalhes');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        // Add data
        $row = 2;
        foreach ($history as $item) {
            $sheet->setCellValue('A' . $row, $item['date']);
            $sheet->setCellValue('B' . $row, $item['type']);
            $sheet->setCellValue('C' . $row, $item['description']);
            $sheet->setCellValue('D' . $row, number_format(abs($item['amount']), 2, ',', '.'));
            $sheet->setCellValue('E' . $row, $item['details']);

            // Style amount column based on type
            $amountStyle = [
                'font' => ['color' => ['rgb' => $item['amount'] >= 0 ? '006100' : 'C00000']],
            ];
            $sheet->getStyle('D' . $row)->applyFromArray($amountStyle);

            $row++;
        }

        // Add summary
        $totalPayments = collect($payments)->sum('amount');
        $totalExpenses = collect($expenses)->sum('amount');
        $balance = $totalPayments - $totalExpenses;

        $row++;
        $sheet->setCellValue('C' . $row, 'Total Recebido:');
        $sheet->setCellValue('D' . $row, 'R$ ' . number_format($totalPayments, 2, ',', '.'));
        $row++;
        $sheet->setCellValue('C' . $row, 'Total Despesas:');
        $sheet->setCellValue('D' . $row, 'R$ ' . number_format($totalExpenses, 2, ',', '.'));
        $row++;
        $sheet->setCellValue('C' . $row, 'Saldo:');
        $sheet->setCellValue('D' . $row, 'R$ ' . number_format($balance, 2, ',', '.'));
        $sheet->getStyle('C' . ($row - 2) . ':D' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);
        $filename = 'historico_financeiro_' . $driver->id . '_' . date('Y-m-d') . '.xlsx';

        // Save to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $writer->save($tempFile);

        return Response::download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Export financial history to PDF
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not registered as a driver.');
        }

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Get all financial history
        $payments = $driver->getRecentPayments($startDate, $endDate, 10000)->items();
        $expenses = $driver->getRecentExpenses($startDate, $endDate, 10000)->items();

        $totalPayments = collect($payments)->sum('amount');
        $totalExpenses = collect($expenses)->sum('amount');
        $balance = $totalPayments - $totalExpenses;

        $history = collect()
            ->merge(collect($payments)->map(function ($payment) {
                return [
                    'type' => 'Pagamento',
                    'date' => $payment['date']->format('d/m/Y'),
                    'description' => "Pagamento - {$payment['route_name']}",
                    'amount' => $payment['amount'],
                    'details' => "{$payment['diarias_count']} diária(s) × R$ " . number_format($payment['diaria_value'], 2, ',', '.'),
                ];
            }))
            ->merge(collect($expenses)->map(function ($expense) {
                return [
                    'type' => 'Despesa',
                    'date' => $expense['date']->format('d/m/Y'),
                    'description' => $expense['description'],
                    'amount' => -$expense['amount'],
                    'details' => $expense['category'] ?? 'Sem categoria',
                ];
            }))
            ->sortByDesc(function ($item) {
                return $item['date'];
            })
            ->values();

        // Generate HTML
        $html = view('driver.financial-export-pdf', compact(
            'driver',
            'history',
            'totalPayments',
            'totalExpenses',
            'balance',
            'startDate',
            'endDate'
        ))->render();

        // Configure PDF options
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        // Create PDF
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'historico_financeiro_' . $driver->id . '_' . date('Y-m-d') . '.pdf';

        return Response::make($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}















