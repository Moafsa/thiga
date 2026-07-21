<?php

namespace App\Http\Controllers;

use App\Models\CteXml;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\CteXmlParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;

class CteXmlController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of CT-e XMLs
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $query = CteXml::where('tenant_id', $tenant->id);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('cte_number', 'like', "%{$search}%")
                  ->orWhere('access_key', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && !empty($request->status)) {
            if ($request->status === 'used') {
                $query->where('is_used', true);
            } elseif ($request->status === 'unused') {
                $query->where('is_used', false);
            }
        }

        $cteXmls = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('cte-xmls.index', compact('cteXmls'));
    }

    /**
     * Store uploaded CT-e XML files (or ZIP archives containing XMLs)
     */
    public function store(Request $request, CteXmlParserService $xmlParser)
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'cte_xml_files' => 'required|array',
            'cte_xml_files.*' => 'file|mimes:xml,text/xml,application/xml,zip,application/zip,application/x-zip-compressed|max:51200',
        ]);

        $uploadedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($request->file('cte_xml_files') as $file) {
            $xmlEntries = $this->extractXmlContents($file);

            if (empty($xmlEntries)) {
                $errors[] = $file->getClientOriginalName() . ': Nenhum arquivo XML válido encontrado ou extraído.';
                continue;
            }

            foreach ($xmlEntries as $entry) {
                $filename = $entry['name'];
                $xmlContent = $entry['content'];

                try {
                    if (empty($xmlContent)) {
                        $errors[] = $filename . ': Conteúdo XML vazio ou inválido';
                        continue;
                    }

                    // Parse XML to extract CT-e number and access key
                    $cteData = $xmlParser->parseXml($xmlContent);
                    
                    if (empty($cteData['document_number'])) {
                        $errors[] = $filename . ': Não foi possível extrair o número do CT-e do XML';
                        continue;
                    }

                    $cteNumber = $cteData['document_number'];
                    $accessKey = $cteData['access_key'] ?? null;

                    // Find or create sender client (remetente)
                    $client = null;
                    if (!empty($cteData['origin'])) {
                        $client = Client::findOrCreateClient($tenant, $cteData['origin']);
                    }
                    if (!empty($cteData['destination'])) {
                        Client::findOrCreateClient($tenant, $cteData['destination']);
                    }

                    // Check if XML with same CT-e number or access key already exists
                    $existingXml = CteXml::where('tenant_id', $tenant->id)
                        ->where(function($q) use ($cteNumber, $accessKey) {
                            $q->where('cte_number', $cteNumber);
                            if ($accessKey) {
                                $q->orWhere('access_key', $accessKey);
                            }
                        })
                        ->first();

                    // Save XML to storage
                    $xmlPath = $this->saveXmlToStorage($xmlContent, $accessKey ?? 'cte-' . $cteNumber, $tenant->id);

                    if ($existingXml) {
                        $existingXml->update([
                            'access_key' => $accessKey ?? $existingXml->access_key,
                            'xml' => $xmlPath ? null : $xmlContent,
                            'xml_url' => $xmlPath ?? $existingXml->xml_url,
                        ]);
                        $skippedCount++;
                    } else {
                        CteXml::create([
                            'tenant_id' => $tenant->id,
                            'cte_number' => $cteNumber,
                            'access_key' => $accessKey,
                            'xml' => $xmlPath ? null : $xmlContent,
                            'xml_url' => $xmlPath,
                            'is_used' => false,
                        ]);
                        $uploadedCount++;
                    }

                    // Automatic Revenue / Accounts Receivable Invoice generation (Requisito 9)
                    $totalValue = $cteData['total_value'] ?? ($cteData['value'] ?? 0);
                    if ($totalValue > 0 && $client) {
                        $invoiceNumber = 'CTE-' . $cteNumber;
                        $existingInvoice = Invoice::where('tenant_id', $tenant->id)
                            ->where('invoice_number', $invoiceNumber)
                            ->first();

                        if (!$existingInvoice) {
                            Invoice::create([
                                'tenant_id' => $tenant->id,
                                'client_id' => $client->id,
                                'invoice_number' => $invoiceNumber,
                                'issue_date' => now()->toDateString(),
                                'due_date' => now()->addDays(30)->toDateString(),
                                'subtotal' => $totalValue,
                                'tax_amount' => $cteData['tax_amount'] ?? 0,
                                'total_amount' => $totalValue,
                                'status' => 'open',
                                'notes' => 'Receita lançada automaticamente a partir do CT-e #' . $cteNumber,
                                'metadata' => [
                                    'cte_number' => $cteNumber,
                                    'access_key' => $accessKey,
                                    'auto_generated' => true,
                                ],
                            ]);
                        }
                    }

                } catch (\Exception $e) {
                    $errors[] = $filename . ': ' . $e->getMessage();
                    \Log::error('Erro ao processar arquivo CT-e XML', [
                        'file' => $filename,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $message = '';
        if ($uploadedCount > 0) {
            $message .= "{$uploadedCount} arquivo(s) XML importado(s) com sucesso. ";
        }
        if ($skippedCount > 0) {
            $message .= "⚠️ AVISO: {$skippedCount} arquivo(s) XML já existiam no sistema (duplicados) e foram ignorados/atualizados. ";
        }
        if (!empty($errors)) {
            $message .= 'Erros: ' . implode('; ', $errors);
        }

        $sessionKey = ($skippedCount > 0 && $uploadedCount === 0) ? 'warning' : 'success';

        return redirect()->route('cte-xmls.index')
            ->with($sessionKey, $message ?: 'Nenhum arquivo foi processado.');
    }

    /**
     * Helper to extract XML contents from uploaded file (XML or ZIP)
     */
    protected function extractXmlContents($file): array
    {
        $xmlEntries = [];
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'zip') {
            $zip = new \ZipArchive();
            if ($zip->open($file->getRealPath()) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    if ($stat && strtolower(pathinfo($stat['name'], PATHINFO_EXTENSION)) === 'xml') {
                        $content = $zip->getFromIndex($i);
                        if ($content) {
                            $xmlEntries[] = [
                                'name' => basename($stat['name']),
                                'content' => $content,
                            ];
                        }
                    }
                }
                $zip->close();
            }
        } else {
            $content = file_get_contents($file->getRealPath());
            if ($content) {
                $xmlEntries[] = [
                    'name' => $file->getClientOriginalName(),
                    'content' => $content,
                ];
            }
        }

        return $xmlEntries;
    }

    /**
     * Save XML content to storage
     */
    protected function saveXmlToStorage(string $xmlContent, string $accessKey, int $tenantId): ?string
    {
        try {
            $filename = 'cte-' . ($accessKey ?: Str::random(16)) . '.xml';
            $path = "tenants/{$tenantId}/cte-xmls/{$filename}";
            
            Storage::disk('local')->put($path, $xmlContent);
            
            \Log::info('CT-e XML saved to storage', [
                'path' => $path,
                'tenant_id' => $tenantId,
            ]);
            
            return 'local:' . $path;
        } catch (\Exception $e) {
            \Log::warning('Failed to save CT-e XML to storage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tenant_id' => $tenantId,
            ]);
            return null;
        }
    }

    /**
     * Download CT-e XML file
     */
    public function download(CteXml $cteXml)
    {
        $tenant = Auth::user()->tenant;
        
        if ($cteXml->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized access to this CT-e XML.');
        }

        $xmlContent = null;
        
        if ($cteXml->xml_url) {
            try {
                if (strpos($cteXml->xml_url, 'local:') === 0) {
                    $localPath = str_replace('local:', '', $cteXml->xml_url);
                    if (Storage::disk('local')->exists($localPath)) {
                        $xmlContent = Storage::disk('local')->get($localPath);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to get XML from storage', [
                    'xml_url' => $cteXml->xml_url,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        if (!$xmlContent && $cteXml->xml) {
            $xmlContent = $cteXml->xml;
        }
        
        if (!$xmlContent) {
            abort(404, 'XML file not found.');
        }
        
        $filename = 'cte-' . ($cteXml->access_key ?? $cteXml->cte_number) . '.xml';
        
        return response($xmlContent, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export CT-e XMLs to Excel (CSV format) divided by used and unused
     */
    public function export(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            abort(403, 'User does not have an associated tenant.');
        }

        $query = CteXml::where('tenant_id', $tenant->id)->with('route');

        // Apply same filters as index
        if ($request->filled('status')) {
            if ($request->status === 'used') {
                $query->where('is_used', true);
            } elseif ($request->status === 'unused') {
                $query->where('is_used', false);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('cte_number', 'like', "%{$search}%")
                  ->orWhere('access_key', 'like', "%{$search}%");
            });
        }

        $allXmls = $query->orderBy('is_used', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'cte-xmls-export-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($allXmls) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Unused XMLs Section
            $unusedXmls = $allXmls->where('is_used', false);
            if ($unusedXmls->count() > 0) {
                fputcsv($file, ['=== XMLs NÃO USADOS ==='], ';');
                fputcsv($file, [
                    'Número CT-e',
                    'Chave de Acesso',
                    'Criado Em',
                    'Status'
                ], ';');

                foreach ($unusedXmls as $cteXml) {
                    fputcsv($file, [
                        $cteXml->cte_number,
                        $cteXml->access_key ?? 'N/A',
                        $cteXml->created_at->format('d/m/Y H:i'),
                        'Não Usado'
                    ], ';');
                }
                
                fputcsv($file, [], ';'); // Empty line
            }

            // Used XMLs Section
            $usedXmls = $allXmls->where('is_used', true);
            if ($usedXmls->count() > 0) {
                fputcsv($file, ['=== XMLs USADOS ==='], ';');
                fputcsv($file, [
                    'Número CT-e',
                    'Chave de Acesso',
                    'Criado Em',
                    'Usado Em',
                    'Rota',
                    'Status'
                ], ';');

                foreach ($usedXmls as $cteXml) {
                    fputcsv($file, [
                        $cteXml->cte_number,
                        $cteXml->access_key ?? 'N/A',
                        $cteXml->created_at->format('d/m/Y H:i'),
                        $cteXml->used_at ? $cteXml->used_at->format('d/m/Y H:i') : 'N/A',
                        $cteXml->route ? $cteXml->route->name : 'N/A',
                        'Usado'
                    ], ';');
                }
            }

            // Summary
            fputcsv($file, [], ';'); // Empty line
            fputcsv($file, ['=== RESUMO ==='], ';');
            fputcsv($file, ['Total de XMLs', $allXmls->count()], ';');
            fputcsv($file, ['XMLs Não Usados', $unusedXmls->count()], ';');
            fputcsv($file, ['XMLs Usados', $usedXmls->count()], ';');

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Delete a single CT-e XML
     */
    public function destroy(CteXml $cteXml)
    {
        $tenant = Auth::user()->tenant;
        
        if ($cteXml->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized access to this CT-e XML.');
        }

        try {
            // Delete XML file from storage if exists
            if ($cteXml->xml_url) {
                try {
                    if (strpos($cteXml->xml_url, 'local:') === 0) {
                        $localPath = str_replace('local:', '', $cteXml->xml_url);
                        if (Storage::disk('local')->exists($localPath)) {
                            Storage::disk('local')->delete($localPath);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete XML file from storage', [
                        'xml_url' => $cteXml->xml_url,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $cteNumber = $cteXml->cte_number;
            $cteXml->delete();

            return redirect()->route('cte-xmls.index')
                ->with('success', "XML CT-e {$cteNumber} deleted successfully.");
        } catch (\Exception $e) {
            \Log::error('Error deleting CT-e XML', [
                'cte_xml_id' => $cteXml->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('cte-xmls.index')
                ->with('error', 'Error deleting XML. Please try again.');
        }
    }

    /**
     * Delete multiple CT-e XMLs
     */
    public function destroyMultiple(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('cte-xmls.index')
                ->with('error', 'User does not have an associated tenant.');
        }

        $request->validate([
            'xml_ids' => 'required|array',
            'xml_ids.*' => 'required|integer|exists:cte_xmls,id',
        ]);

        $xmlIds = $request->xml_ids;
        $xmls = CteXml::where('tenant_id', $tenant->id)
            ->whereIn('id', $xmlIds)
            ->get();

        if ($xmls->count() === 0) {
            return redirect()->route('cte-xmls.index')
                ->with('error', 'No valid XMLs selected for deletion.');
        }

        $deletedCount = 0;
        $errors = [];

        foreach ($xmls as $cteXml) {
            try {
                // Delete XML file from storage if exists
                if ($cteXml->xml_url) {
                    try {
                        if (strpos($cteXml->xml_url, 'local:') === 0) {
                            $localPath = str_replace('local:', '', $cteXml->xml_url);
                            if (Storage::disk('local')->exists($localPath)) {
                                Storage::disk('local')->delete($localPath);
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete XML file from storage', [
                            'xml_url' => $cteXml->xml_url,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $cteXml->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $errors[] = "CT-e {$cteXml->cte_number}: " . $e->getMessage();
                \Log::error('Error deleting CT-e XML', [
                    'cte_xml_id' => $cteXml->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $message = "{$deletedCount} XML(s) deleted successfully.";
        if (!empty($errors)) {
            $message .= ' Errors: ' . implode('; ', $errors);
        }

        return redirect()->route('cte-xmls.index')
            ->with('success', $message);
    }
}

