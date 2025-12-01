<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Carga - TMS SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
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

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('shipments.show', $shipment) }}" class="text-green-600 hover:text-green-800 inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar para Detalhes
            </a>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-8">Editar Carga #{{ $shipment->tracking_number }}</h1>

        <form method="POST" action="{{ route('shipments.update', $shipment) }}" class="bg-white rounded-lg shadow-md p-8 space-y-8">
            @csrf
            @method('PUT')

            <!-- Dados da Mercadoria -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Dados da Mercadoria</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Título/Descrição *</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $shipment->title) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantidade</label>
                        <input type="number" id="quantity" name="quantity" value="{{ old('quantity', $shipment->quantity) }}" min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Peso (kg)</label>
                        <input type="number" step="0.01" id="weight" name="weight" value="{{ old('weight', $shipment->weight) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="volume" class="block text-sm font-medium text-gray-700 mb-2">Volume (m³)</label>
                        <input type="number" step="0.01" id="volume" name="volume" value="{{ old('volume', $shipment->volume) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="value" class="block text-sm font-medium text-gray-700 mb-2">Valor Declarado (R$)</label>
                        <input type="number" step="0.01" id="value" name="value" value="{{ old('value', $shipment->value) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select id="status" name="status" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <option value="pending" {{ old('status', $shipment->status) === 'pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="scheduled" {{ old('status', $shipment->status) === 'scheduled' ? 'selected' : '' }}>Agendada</option>
                            <option value="picked_up" {{ old('status', $shipment->status) === 'picked_up' ? 'selected' : '' }}>Coletada</option>
                            <option value="in_transit" {{ old('status', $shipment->status) === 'in_transit' ? 'selected' : '' }}>Em Trânsito</option>
                            <option value="delivered" {{ old('status', $shipment->status) === 'delivered' ? 'selected' : '' }}>Entregue</option>
                            <option value="returned" {{ old('status', $shipment->status) === 'returned' ? 'selected' : '' }}>Devolvida</option>
                            <option value="cancelled" {{ old('status', $shipment->status) === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                        <textarea id="description" name="description" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('description', $shipment->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Rota e Motorista -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Rota e Motorista</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="route_id" class="block text-sm font-medium text-gray-700 mb-2">Rota</label>
                        <select id="route_id" name="route_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <option value="">Sem rota</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}" {{ old('route_id', $shipment->route_id) == $route->id ? 'selected' : '' }}>
                                    {{ $route->name }} - {{ $route->scheduled_date->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="driver_id" class="block text-sm font-medium text-gray-700 mb-2">Motorista</label>
                        <select id="driver_id" name="driver_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <option value="">Sem motorista</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('driver_id', $shipment->driver_id) == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Observações Gerais</label>
                <textarea id="notes" name="notes" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('notes', $shipment->notes) }}</textarea>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t">
                <a href="{{ route('shipments.show', $shipment) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-save mr-2"></i>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</body>
</html>





















