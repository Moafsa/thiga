<?php

namespace App\Http\Controllers;

use App\Models\CteXml;
use App\Models\Shipment;
use App\Services\CteXmlParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CteAiAnalysisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Perform aggregate AI analysis of CT-e XMLs for current tenant
     */
    public function analyze(Request $request, CteXmlParserService $xmlParser)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json(['error' => 'Tenant não encontrado.'], 400);
        }

        $cteQuery = CteXml::where('tenant_id', $tenant->id);

        if ($request->filled('date_from')) {
            $cteQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $cteQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $cteXmls = $cteQuery->orderBy('created_at', 'desc')->take(100)->get();

        if ($cteXmls->isEmpty()) {
            return response()->json([
                'success' => true,
                'analysis' => 'Nenhum CT-e encontrado para análise no período selecionado.',
                'summary' => [
                    'total_ctes' => 0,
                    'total_value' => 0,
                ],
            ]);
        }

        $totalValue = 0;
        $totalTaxes = 0;
        $origins = [];
        $destinations = [];
        $usedCount = 0;
        $unusedCount = 0;
        $parsedDetails = [];

        foreach ($cteXmls as $item) {
            if ($item->is_used) {
                $usedCount++;
            } else {
                $unusedCount++;
            }

            // Attempt to parse stored XML content if available
            $content = $item->xml;
            if (!$content && $item->xml_url && strpos($item->xml_url, 'local:') === 0) {
                $localPath = str_replace('local:', '', $item->xml_url);
                try {
                    $content = \Storage::disk('local')->get($localPath);
                } catch (\Exception $e) {}
            }

            if ($content) {
                try {
                    $parsed = $xmlParser->parseXml($content);
                    $val = $parsed['total_value'] ?? ($parsed['value'] ?? 0);
                    $tax = $parsed['tax_amount'] ?? 0;
                    $totalValue += $val;
                    $totalTaxes += $tax;

                    if (!empty($parsed['origin']['name'])) {
                        $origins[$parsed['origin']['name']] = ($origins[$parsed['origin']['name']] ?? 0) + 1;
                    }
                    if (!empty($parsed['destination']['name'])) {
                        $destinations[$parsed['destination']['name']] = ($destinations[$parsed['destination']['name']] ?? 0) + 1;
                    }
                    $parsedDetails[] = [
                        'number' => $item->cte_number,
                        'value' => $val,
                        'origin' => $parsed['origin']['name'] ?? 'N/I',
                        'destination' => $parsed['destination']['name'] ?? 'N/I',
                    ];
                } catch (\Exception $e) {}
            }
        }

        arsort($origins);
        arsort($destinations);
        $topOrigins = array_slice($origins, 0, 5, true);
        $topDestinations = array_slice($destinations, 0, 5, true);

        // Attempt OpenAI analysis if key is available
        $openAiKey = config('services.openai.api_key') ?? env('OPENAI_API_KEY');
        $aiAnalysisText = null;

        if ($openAiKey && $openAiKey !== 'not-configured') {
            try {
                $prompt = "Você é um especialista em logística e inteligência fiscal de transportadoras. Analise o seguinte resumo de CT-es do tenant '{$tenant->name}':\n" .
                    "- Total de CT-es analisados: {$cteXmls->count()}\n" .
                    "- Valor Total de Fretes: R$ " . number_format($totalValue, 2, ',', '.') . "\n" .
                    "- Valor Total de Impostos: R$ " . number_format($totalTaxes, 2, ',', '.') . "\n" .
                    "- CT-es Vinculados a Rotas: {$usedCount}\n" .
                    "- CT-es Não Utilizados: {$unusedCount}\n" .
                    "- Principais Remetentes: " . implode(', ', array_keys($topOrigins)) . "\n" .
                    "- Principais Destinatários: " . implode(', ', array_keys($topDestinations)) . "\n\n" .
                    "Forneça um relatório conciso com: 1. Resumo Executivo 2. Principais Clientes e Rotas 3. Alertas/Oportunidades de Otimização de Frete.";

                $response = Http::withToken($openAiKey)
                    ->timeout(15)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            ['role' => 'system', 'content' => 'Você é um assistente especialista em TMS e logística de fretes.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => 0.4,
                    ]);

                if ($response->successful()) {
                    $aiAnalysisText = $response->json()['choices'][0]['message']['content'] ?? null;
                }
            } catch (\Exception $e) {
                Log::warning('IA OpenAI indisponível para análise de CT-es', ['error' => $e->getMessage()]);
            }
        }

        // Heuristic analysis generator if OpenAI is not configured
        if (!$aiAnalysisText) {
            $avgValue = $cteXmls->count() > 0 ? $totalValue / $cteXmls->count() : 0;
            $taxPercent = $totalValue > 0 ? ($totalTaxes / $totalValue) * 100 : 0;

            $aiAnalysisText = "### 📊 Relatório Inteligente de Análise de CT-es\n\n";
            $aiAnalysisText .= "**Resumo da Operação:**\n";
            $aiAnalysisText .= "- **Total de CT-es Analisados:** " . $cteXmls->count() . " documento(s)\n";
            $aiAnalysisText .= "- **Valor Acumulado de Fretes:** R$ " . number_format($totalValue, 2, ',', '.') . "\n";
            $aiAnalysisText .= "- **Ticket Médio por CT-e:** R$ " . number_format($avgValue, 2, ',', '.') . "\n";
            $aiAnalysisText .= "- **Estimativa de Impostos:** R$ " . number_format($totalTaxes, 2, ',', '.') . " (" . number_format($taxPercent, 1, ',', '.') . "% do total)\n\n";

            $aiAnalysisText .= "**Status dos Documentos:**\n";
            $aiAnalysisText .= "- **Associados a Rotas:** {$usedCount} CT-e(s)\n";
            $aiAnalysisText .= "- **Disponíveis/Não Alocados:** {$unusedCount} CT-e(s)\n\n";

            if (!empty($topOrigins)) {
                $aiAnalysisText .= "**Principais Origens/Remetentes:**\n";
                foreach ($topOrigins as $orig => $cnt) {
                    $aiAnalysisText .= "- {$orig} ({$cnt} CT-es)\n";
                }
                $aiAnalysisText .= "\n";
            }

            if (!empty($topDestinations)) {
                $aiAnalysisText .= "**Principais Destinos/Recebedores:**\n";
                foreach ($topDestinations as $dest => $cnt) {
                    $aiAnalysisText .= "- {$dest} ({$cnt} CT-es)\n";
                }
                $aiAnalysisText .= "\n";
            }

            $aiAnalysisText .= "**💡 Recomendações da IA:**\n";
            if ($unusedCount > 0) {
                $aiAnalysisText .= "- Existem **{$unusedCount} CT-es pendentes de alocação em rotas**. Recomendamos agrupá-los por destino para otimizar a montagem dos manifestos (MDF-e).\n";
            } else {
                $aiAnalysisText .= "- Todos os CT-es analisados já foram alocados em rotas de transporte.\n";
            }
            $aiAnalysisText .= "- Monitore o ticket médio para identificar variações nas tabelas de frete de clientes com grande volume.\n";
        }

        return response()->json([
            'success' => true,
            'analysis' => $aiAnalysisText,
            'summary' => [
                'total_ctes' => $cteXmls->count(),
                'total_value' => $totalValue,
                'used_count' => $usedCount,
                'unused_count' => $unusedCount,
            ],
        ]);
    }
}
