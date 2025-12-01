<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Despesa #{{ $expense->id }} - Contas a Pagar</title>
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
                    <a href="{{ route('accounts.payable.index') }}" class="text-gray-700 hover:text-gray-900">
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
        <!-- Expense Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $expense->description }}</h1>
                    @if($expense->category)
                        <span class="px-3 py-1 text-sm rounded-full inline-block" 
                              style="background-color: {{ $expense->category->color ?? '#e5e7eb' }}20; color: {{ $expense->category->color ?? '#6b7280' }}">
                            {{ $expense->category->name }}
                        </span>
                    @endif
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-semibold 
                    {{ $expense->status === 'paid' ? 'bg-green-100 text-green-800' : 
                       ($expense->isOverdue() ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                    @if($expense->status === 'paid')
                        Paga
                    @elseif($expense->isOverdue())
                        Vencida
                    @else
                        Pendente
                    @endif
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div>
                    <p class="text-sm text-gray-600">Valor</p>
                    <p class="font-semibold text-gray-900 text-lg">R$ {{ number_format($expense->amount, 2, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Data de Vencimento</p>
                    <p class="font-semibold text-gray-900">{{ $expense->due_date->format('d/m/Y') }}</p>
                    @if($expense->isOverdue())
                        <p class="text-sm text-red-600 font-semibold">
                            {{ abs(now()->diffInDays($expense->due_date, false)) }} dias em atraso
                        </p>
                    @endif
                </div>
                @if($expense->isPaid())
                    <div>
                        <p class="text-sm text-gray-600">Data de Pagamento</p>
                        <p class="font-semibold text-gray-900">{{ $expense->paid_at->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Método de Pagamento</p>
                        <p class="font-semibold text-gray-900">{{ $expense->payment_method ?? 'N/A' }}</p>
                    </div>
                @endif
            </div>

            @if($expense->vehicle || $expense->route)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-200">
                @if($expense->vehicle)
                <div>
                    <p class="text-sm text-gray-600">Veículo (Manutenção)</p>
                    <p class="font-semibold text-gray-900">
                        <a href="{{ route('vehicles.show', $expense->vehicle) }}" class="text-green-600 hover:text-green-700">
                            {{ $expense->vehicle->formatted_plate }}
                        </a>
                        @if($expense->vehicle->brand && $expense->vehicle->model)
                            <span class="text-gray-600"> - {{ $expense->vehicle->brand }} {{ $expense->vehicle->model }}</span>
                        @endif
                    </p>
                </div>
                @endif
                @if($expense->route)
                <div>
                    <p class="text-sm text-gray-600">Rota</p>
                    <p class="font-semibold text-gray-900">
                        <a href="{{ route('routes.show', $expense->route) }}" class="text-green-600 hover:text-green-700">
                            {{ $expense->route->name }}
                        </a>
                        <span class="text-gray-600"> - {{ $expense->route->scheduled_date->format('d/m/Y') }}</span>
                    </p>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Notes -->
        @if($expense->notes)
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Observações</h2>
                <p class="text-gray-700 whitespace-pre-line">{{ $expense->notes }}</p>
            </div>
        @endif

        <!-- Payments -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Pagamentos</h2>
                @if($expense->status !== 'paid')
                    <button onclick="openPaymentModal()" 
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-plus mr-2"></i>Registrar Pagamento
                    </button>
                @endif
            </div>

            @if($expense->payments->count() > 0)
                <div class="space-y-3">
                    @foreach($expense->payments as $payment)
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

        <!-- Actions -->
        @if($expense->status !== 'paid')
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('accounts.payable.edit', $expense) }}" 
                       class="bg-yellow-600 text-white px-6 py-2 rounded-lg hover:bg-yellow-700">
                        <i class="fas fa-edit mr-2"></i>Editar
                    </a>
                    <form method="POST" action="{{ route('accounts.payable.destroy', $expense) }}" 
                          onsubmit="return confirm('Tem certeza que deseja excluir esta despesa?')" 
                          class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                            <i class="fas fa-trash mr-2"></i>Excluir
                        </button>
                    </form>
                </div>
            </div>
        @endif
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
                <form method="POST" action="{{ route('accounts.payable.payment', $expense) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Valor * (Máximo: R$ {{ number_format($expense->amount, 2, ',', '.') }})
                            </label>
                            <input type="number" 
                                   name="amount" 
                                   step="0.01" 
                                   min="0.01" 
                                   max="{{ $expense->amount }}" 
                                   value="{{ $expense->amount }}"
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

    @if(session('error'))
        <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <script>
        function openPaymentModal() {
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        // Auto-hide messages
        setTimeout(() => {
            const messages = document.querySelectorAll('.fixed');
            messages.forEach(msg => msg.remove());
        }, 5000);
    </script>
</body>
</html>












