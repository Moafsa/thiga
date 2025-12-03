<?php

namespace App\Http\Controllers;

use App\Models\CteXml;
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

        // Filter by usage status
        if ($request->filled('status')) {
            if ($request->status === 'used') {
                $query->where('is_used', true);
            } elseif ($request->status === 'unused') {
                $query->where('is_used', false);
            }
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by CT-e number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('cte_number', 'like', "%{$search}%")
                  ->orWhere('access_key', 'like', "%{$search}%");
            });
        }

        $cteXmls = $query->orderBy('created_at', 'desc')
            ->orderBy('cte_number', 'desc')
            ->paginate(20);

        return view('cte-xmls.index', compact('cteXmls'));
    }

    /**
     * Store uploaded CT-e XML files
     */
    public function store(Request $request, CteXmlParserService $xmlParser)
    {
        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'cte_xml_files' => 'required|array',
            'cte_xml_files.*' => 'file|mimes:xml,text/xml,application/xml|max:10240',
        ]);

        $uploadedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($request->file('cte_xml_files') as $file) {
            try {
                $xmlContent = file_get_contents($file->getRealPath());
                
                if (empty($xmlContent)) {
                    $errors[] = $file->getClientOriginalName() . ': Empty or invalid XML file';
                    continue;
                }

                // Parse XML to extract CT-e number and access key
                $cteData = $xmlParser->parseXml($xmlContent);
                
                if (empty($cteData['document_number'])) {
                    $errors[] = $file->getClientOriginalName() . ': Could not extract CT-e number from XML';
                    continue;
                }

                $cteNumber = $cteData['document_number'];
                $accessKey = $cteData['access_key'] ?? null;

                // Check if XML with same CT-e number already exists
                $existingXml = CteXml::where('tenant_id', $tenant->id)
                    ->where('cte_number', $cteNumber)
                    ->first();

                // Save XML to storage
                $xmlPath = $this->saveXmlToStorage($xmlContent, $accessKey ?? 'cte-' . $cteNumber, $tenant->id);

                if ($existingXml) {
                    // Update existing XML but preserve is_used status if already used
                    $existingXml->update([
                        'access_key' => $accessKey ?? $existingXml->access_key,
                        'xml' => $xmlPath ? null : $xmlContent,
                        'xml_url' => $xmlPath ?? $existingXml->xml_url,
                        // Keep is_used and used_at if already used
                    ]);
                    $skippedCount++;
                } else {
                    // Create new XML record
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
            } catch (\Exception $e) {
                $errors[] = $file->getClientOriginalName() . ': ' . $e->getMessage();
                \Log::error('Error processing CT-e XML file', [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $message = '';
        if ($uploadedCount > 0) {
            $message .= "{$uploadedCount} XML file(s) uploaded successfully. ";
        }
        if ($skippedCount > 0) {
            $message .= "{$skippedCount} XML file(s) already exist and were updated (usage status preserved). ";
        }
        if (!empty($errors)) {
            $message .= 'Errors: ' . implode('; ', $errors);
        }

        return redirect()->route('cte-xmls.index')
            ->with('success', $message ?: 'No files were processed.');
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

