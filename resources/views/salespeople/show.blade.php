<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $salesperson->name }} - TMS SaaS</title>
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('salespeople.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $salesperson->name }}</h1>
                        <p class="text-gray-600 mt-2">Detalhes do vendedor</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('salespeople.edit', $salesperson) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-edit mr-2"></i>
                        Editar
                    </a>
                    <form method="POST" action="{{ route('salespeople.destroy', $salesperson) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este vendedor?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-trash mr-2"></i>
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Salesperson Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">{{ $salesperson->name }}</h3>
                        <p class="text-gray-600">{{ $salesperson->email }}</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Telefone:</span>
                            <span class="font-medium">{{ $salesperson->phone ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Documento:</span>
                            <span class="font-medium">{{ $salesperson->document ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Comissão:</span>
                            <span class="font-medium text-green-600">{{ $salesperson->formatted_commission_rate }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Desconto Máximo:</span>
                            <span class="font-medium text-orange-600">{{ $salesperson->formatted_max_discount }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="px-2 py-1 rounded-full text-xs {{ $salesperson->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $salesperson->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Proposals -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Propostas Recentes</h3>
                        <a href="{{ route('proposals.create') }}?salesperson_id={{ $salesperson->id }}" class="text-green-600 hover:text-green-800">
                            <i class="fas fa-plus mr-1"></i>
                            Nova Proposta
                        </a>
                    </div>

                    @if($proposals->count() > 0)
                        <div class="space-y-4">
                            @foreach($proposals as $proposal)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $proposal->title }}</h4>
                                            <p class="text-gray-600 text-sm">{{ $proposal->client->name }}</p>
                                            <p class="text-gray-500 text-sm">{{ $proposal->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-lg font-semibold text-gray-900">{{ $proposal->formatted_final_value }}</span>
                                            <div class="mt-1">
                                                <span class="px-2 py-1 rounded-full text-xs {{ 
                                                    $proposal->status === 'accepted' ? 'bg-green-100 text-green-800' : 
                                                    ($proposal->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                                    ($proposal->status === 'sent' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'))
                                                }}">
                                                    {{ $proposal->status_label }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $proposals->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-file-contract text-gray-400 text-4xl mb-4"></i>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Nenhuma proposta encontrada</h4>
                            <p class="text-gray-600 mb-4">Este vendedor ainda não possui propostas</p>
                            <a href="{{ route('proposals.create') }}?salesperson_id={{ $salesperson->id }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Criar Primeira Proposta
                            </a>
                        </div>
                    @endif
                </div>
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
        // Auto-hide success/error messages
        setTimeout(() => {
            const messages = document.querySelectorAll('.fixed');
            messages.forEach(msg => msg.remove());
        }, 5000);
    </script>
</body>
</html>























