<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura #{{ $invoice->invoice_number }} - Contas a Receber</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-2xl font-bold text-green-600">
                        <i class="fas fa-truck mr-2"></i>
                        TMS SaaS
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('accounts.receivable.index') }}" class="text-gray-700 hover:text-gray-900">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar
                    </a>
                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Invoice Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Fatura #{{ $invoice->invoice_number }}</h1>
                    <p class="text-gray-600">Cliente: <span class="font-semibold">{{ $invoice->client->name }}</span></p>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-semibold 
                    {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 
                       ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                    @if($invoice->status === 'paid')
                        Paga
                    @elseif($invoice->status === 'overdue')
                        Vencida
                    @else
                        Aberta
                    @endif
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div>
                    <p class="text-sm text-gray-600">Data de Emissão</p>
                    <p class="font-semibold text-gray-900">{{ $invoice->issue_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Data de Vencimento</p>
                    <p class="font-semibold text-gray-900">{{ $invoice->due_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Valor Total</p>
                    <p class="font-semibold text-gray-900">R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Saldo Restante</p>
                    <p class="font-semibold {{ $invoice->remaining_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                        R$ {{ number_format($invoice->remaining_balance, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Itens da Fatura</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rastreamento</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->description }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 font-mono">
                                    {{ $item->shipment->tracking_number ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">
                                    R$ {{ number_format($item->total_price, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payments -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Pagamentos</h2>
                @if($invoice->status !== 'paid' && $invoice->remaining_balance > 0)
                    <button onclick="openPaymentModal()" 
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-plus mr-2"></i>Registrar Pagamento
                    </button>
                @endif
            </div>

            @if($invoice->payments->count() > 0)
                <div class="space-y-3">
                    @foreach($invoice->payments as $payment)
                        <div class="bg-gray-50 rounded-lg p-4 flex justify-between items-center">
                            <div>
                                <p class="font-semibold text-gray-900">R$ {{ number_format($payment->amount, 2, ',', '.') }}</p>
                                <p class="text-sm text-gray-600">
                                    {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y') : $payment->due_date->format('d/m/Y') }}
                                    @if($payment->payment_method)
                                        - {{ $payment->payment_method }}
                                    @endif
                                </p>
                                @if($payment->description)
                                    <p class="text-sm text-gray-500 mt-1">{{ $payment->description }}</p>
                                @endif
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                {{ $payment->isPaid() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $payment->isPaid() ? 'Pago' : 'Pendente' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Nenhum pagamento registrado</p>
            @endif
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Registrar Pagamento</h3>
                    <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('accounts.receivable.payment', $invoice) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Valor * (Máximo: R$ {{ number_format($invoice->remaining_balance, 2, ',', '.') }})
                            </label>
                            <input type="number" 
                                   name="amount" 
                                   step="0.01" 
                                   min="0.01" 
                                   max="{{ $invoice->remaining_balance }}" 
                                   value="{{ $invoice->remaining_balance }}"
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data do Pagamento *</label>
                            <input type="date" 
                                   name="paid_at" 
                                   value="{{ date('Y-m-d') }}" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pagamento *</label>
                            <select name="payment_method" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Selecione...</option>
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="PIX">PIX</option>
                                <option value="Transferência Bancária">Transferência Bancária</option>
                                <option value="Boleto">Boleto</option>
                                <option value="Cartão de Crédito">Cartão de Crédito</option>
                                <option value="Cartão de Débito">Cartão de Débito</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                            <textarea name="description" rows="3" 
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closePaymentModal()" 
                                class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                            <i class="fas fa-check mr-2"></i>Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-check mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <script>
        function openPaymentModal() {
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        // Auto-hide success messages
        setTimeout(() => {
            const messages = document.querySelectorAll('.fixed');
            messages.forEach(msg => msg.remove());
        }, 5000);
    </script>
</body>
</html>






















