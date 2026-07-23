<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverExpense;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverAiController extends Controller
{
    /**
     * Handle driver queries, expense logging, and route optimization.
     */
    public function query(Request $request)
    {
        $prompt = trim($request->input('message', ''));
        $user = Auth::user();
        
        // Find associated driver profile
        $driver = null;
        if (Auth::guard('driver')->check()) {
            $driver = Auth::guard('driver')->user();
        } elseif ($user) {
            $driver = Driver::where('phone', $user->phone)->orWhere('cpf', $user->cpf)->first() ?? Driver::first();
        }

        if (!$driver) {
            $driver = Driver::first();
        }

        if (!$driver) {
            return response()->json([
                'success' => false,
                'reply' => '⚠️ Nenhum perfil de motorista cadastrado ainda no sistema.',
            ]);
        }

        // Active route
        $activeRoute = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['in_progress', 'scheduled', 'picked_up'])
            ->orderBy('created_at', 'desc')
            ->first();

        $promptLower = mb_strtolower($prompt);

        // SECURITY & PRIVACY GUARD: Block sensitive company financials from drivers
        $forbiddenKeywords = ['faturamento', 'receita', 'fatura', 'contas a receber', 'lucro da empresa', 'saldo da transportadora', 'outro motorista', 'quanto a empresa ganha'];
        foreach ($forbiddenKeywords as $badWord) {
            if (str_contains($promptLower, $badWord)) {
                return response()->json([
                    'success' => true,
                    'reply' => "🔒 **Informação Restrita**\n\nComo assistente do motorista, posso ajudar apenas com assuntos da **sua rota ativa**, **seus gastos de viagem** e **dicas de economia**. Dados financeiros gerais da transportadora são restritos à administração.",
                ]);
            }
        }

        // INTENT 1: Register Route Expense (Gasolina, Pedágio, Comida, Pernoite, Manutenção)
        if (preg_match('/(?:gastei|gasto|paguei|comprei|valor|r\$)\s*([\d\.,]+)/i', $prompt, $matches) || 
            str_contains($promptLower, 'combustível') || str_contains($promptLower, 'gasolina') || 
            str_contains($promptLower, 'pedágio') || str_contains($promptLower, 'alimentação') || str_contains($promptLower, 'comida')) {

            // Extract amount
            preg_match('/(?:r\$|gastei|paguei|valor)?\s*([\d\.,]+)/i', $prompt, $mVal);
            $amount = isset($mVal[1]) ? floatval(str_replace(['.', ','], ['', '.'], $mVal[1])) : 50.00;

            // Determine expense category
            $category = 'other';
            if (str_contains($promptLower, 'combustível') || str_contains($promptLower, 'gasolina') || str_contains($promptLower, 'diesel')) {
                $category = 'fuel';
            } elseif (str_contains($promptLower, 'pedágio') || str_contains($promptLower, 'pedagio')) {
                $category = 'toll';
            } elseif (str_contains($promptLower, 'alimentação') || str_contains($promptLower, 'comida') || str_contains($promptLower, 'almoço') || str_contains($promptLower, 'janta')) {
                $category = 'food';
            } elseif (str_contains($promptLower, 'pernoite') || str_contains($promptLower, 'hotel') || str_contains($promptLower, 'pousada')) {
                $category = 'lodging';
            } elseif (str_contains($promptLower, 'pneu') || str_contains($promptLower, 'oficina') || str_contains($promptLower, 'manutenção')) {
                $category = 'maintenance';
            }

            // Description / Location
            $description = "Gasto informado via IA: " . mb_substr($prompt, 0, 100);

            // Register DriverExpense
            $expense = DriverExpense::create([
                'driver_id' => $driver->id,
                'route_id' => $activeRoute ? $activeRoute->id : null,
                'expense_type' => $category,
                'description' => $description,
                'amount' => $amount,
                'expense_date' => now(),
                'status' => 'pending',
            ]);

            // Calculate total expenses for this route to enforce budget control
            $routeTotalExpenses = 0;
            if ($activeRoute) {
                $routeTotalExpenses = DriverExpense::where('route_id', $activeRoute->id)->sum('amount');
            }

            $categoryLabels = [
                'fuel' => 'Combustível', 'toll' => 'Pedágio', 'food' => 'Alimentação',
                'lodging' => 'Pernoite / Hotel', 'maintenance' => 'Manutenção', 'other' => 'Outros'
            ];

            $reply = "✅ **Gasto registrado com sucesso!**\n\n";
            $reply .= "📌 **Categoria:** " . ($categoryLabels[$category] ?? 'Outros') . "\n";
            $reply .= "💰 **Valor:** R$ " . number_format($amount, 2, ',', '.') . "\n";
            $reply .= "🛣️ **Rota:** " . ($activeRoute ? $activeRoute->name : 'Sem rota ativa') . "\n";
            $reply .= "📊 **Total gasto nesta rota até agora:** R$ " . number_format($routeTotalExpenses, 2, ',', '.') . "\n\n";

            // Optimization & Expense Warnings
            if ($category === 'fuel' && $amount > 300) {
                $reply .= "⚠️ **Alerta de Economia:** O valor registrado para combustível (R$ " . number_format($amount, 2, ',', '.') . ") está elevado. Lembre-se de priorizar postos conveniados credenciados.\n\n";
            } elseif ($category === 'food' && $amount > 80) {
                $reply .= "💡 **Dica de Gastos:** O teto recomendado para alimentação por refeição é de R$ 45,00.\n\n";
            }

            $reply .= "📸 **Próximo Passo:** Por favor, tire uma foto do **comprovante/nota fiscal** e faça o upload no botão de comprovante para validar a prestação de contas.";

            return response()->json([
                'success' => true,
                'reply' => $reply,
                'expense' => $expense,
                'require_receipt' => true,
            ]);
        }

        // INTENT 2: Route status & instructions
        if (str_contains($promptLower, 'minha rota') || str_contains($promptLower, 'onde ir') || str_contains($promptLower, 'status') || str_contains($promptLower, 'entrega')) {
            if (!$activeRoute) {
                return response()->json([
                    'success' => true,
                    'reply' => "🚛 **Olá, {$driver->name}!**\n\nNo momento você não possui nenhuma rota ativa em andamento. Assim que a central designar uma nova rota, você receberá a notificação aqui.",
                ]);
            }

            $shipments = $activeRoute->shipments;
            $reply = "🛣️ **Sua Rota Ativa: {$activeRoute->name}**\n\n";
            $reply .= "📅 **Data Programada:** " . date('d/m/Y', strtotime($activeRoute->scheduled_date)) . "\n";
            $reply .= "📦 **Cargas/Entregas:** " . $shipments->count() . " pendente(s)\n\n";
            $reply .= "💡 **Dica para economizar na rota:** Mantenha velocidade constante entre 80 km/h e 90 km/h para economizar até 15% de combustível.";

            return response()->json(['success' => true, 'reply' => $reply]);
        }

        // Default Driver Response
        $reply = "🚛 **Assistente do Motorista**\n\n";
        $reply .= "Olá, {$driver->name}! Como posso ajudar na sua viagem hoje?\n\n";
        $reply .= "• ⛽ *'Gastei 150 em combustível no Posto Shell'*\n";
        $reply .= "• 🛣️ *'Qual o status da minha rota?'*\n";
        $reply .= "• 🍲 *'Gastei 40 no almoço'*\n";
        $reply .= "• 🧾 *'Como enviar comprovante de gasto?'*";

        return response()->json(['success' => true, 'reply' => $reply]);
    }
}
