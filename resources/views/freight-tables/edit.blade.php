<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tabela de Frete - TMS SaaS</title>
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
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('freight-tables.show', $freightTable) }}" class="text-gray-500 hover:text-gray-700 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Editar Tabela de Frete</h1>
                    <p class="text-gray-600 mt-2">{{ $freightTable->name }}</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('freight-tables.update', $freightTable) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nome da Tabela *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $freightTable->name) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="destination_type" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Destino *</label>
                        <select id="destination_type" name="destination_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="city" {{ old('destination_type', $freightTable->destination_type) === 'city' ? 'selected' : '' }}>Cidade</option>
                            <option value="region" {{ old('destination_type', $freightTable->destination_type) === 'region' ? 'selected' : '' }}>Região</option>
                            <option value="cep_range" {{ old('destination_type', $freightTable->destination_type) === 'cep_range' ? 'selected' : '' }}>Faixa de CEP</option>
                        </select>
                    </div>

                    <div>
                        <label for="destination_name" class="block text-sm font-medium text-gray-700 mb-2">Nome do Destino *</label>
                        <input type="text" id="destination_name" name="destination_name" value="{{ old('destination_name', $freightTable->destination_name) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('destination_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="destination_state" class="block text-sm font-medium text-gray-700 mb-2">Estado (UF)</label>
                        <input type="text" id="destination_state" name="destination_state" value="{{ old('destination_state', $freightTable->destination_state) }}" maxlength="2"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase">
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('description', $freightTable->description) }}</textarea>
                    </div>

                    <!-- CEP Range -->
                    <div id="cep_range_fields" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 {{ $freightTable->destination_type === 'cep_range' ? '' : 'hidden' }}">
                        <div>
                            <label for="cep_range_start" class="block text-sm font-medium text-gray-700 mb-2">CEP Inicial</label>
                            <input type="text" id="cep_range_start" name="cep_range_start" value="{{ old('cep_range_start', $freightTable->cep_range_start) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label for="cep_range_end" class="block text-sm font-medium text-gray-700 mb-2">CEP Final</label>
                            <input type="text" id="cep_range_end" name="cep_range_end" value="{{ old('cep_range_end', $freightTable->cep_range_end) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>

                    <div class="md:col-span-2 flex items-center">
                        <input type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $freightTable->is_default) ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="is_default" class="ml-2 block text-sm text-gray-700">
                            Definir como tabela padrão
                        </label>
                    </div>
                </div>
            </div>

            <!-- Weight Ranges -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Valores por Faixa de Peso</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="weight_0_30" class="block text-sm font-medium text-gray-700 mb-2">0 a 30 kg (R$)</label>
                        <input type="number" id="weight_0_30" name="weight_0_30" value="{{ old('weight_0_30', $freightTable->weight_0_30) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="weight_31_50" class="block text-sm font-medium text-gray-700 mb-2">31 a 50 kg (R$)</label>
                        <input type="number" id="weight_31_50" name="weight_31_50" value="{{ old('weight_31_50', $freightTable->weight_31_50) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="weight_51_70" class="block text-sm font-medium text-gray-700 mb-2">51 a 70 kg (R$)</label>
                        <input type="number" id="weight_51_70" name="weight_51_70" value="{{ old('weight_51_70', $freightTable->weight_51_70) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="weight_71_100" class="block text-sm font-medium text-gray-700 mb-2">71 a 100 kg (R$)</label>
                        <input type="number" id="weight_71_100" name="weight_71_100" value="{{ old('weight_71_100', $freightTable->weight_71_100) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="weight_over_100_rate" class="block text-sm font-medium text-gray-700 mb-2">Taxa por kg acima de 100kg (R$/kg)</label>
                        <input type="number" id="weight_over_100_rate" name="weight_over_100_rate" value="{{ old('weight_over_100_rate', $freightTable->weight_over_100_rate) }}" step="0.0001" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="ctrc_tax" class="block text-sm font-medium text-gray-700 mb-2">Taxa CTRC acima de 100kg (R$)</label>
                        <input type="number" id="ctrc_tax" name="ctrc_tax" value="{{ old('ctrc_tax', $freightTable->ctrc_tax) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            <!-- Calculation Settings -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Configurações de Cálculo</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="ad_valorem_rate" class="block text-sm font-medium text-gray-700 mb-2">Taxa Ad-Valorem (%)</label>
                        <input type="number" id="ad_valorem_rate" name="ad_valorem_rate" value="{{ old('ad_valorem_rate', $freightTable->ad_valorem_rate * 100) }}" step="0.0001" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <p class="text-xs text-gray-500 mt-1">Padrão: 0,40%</p>
                    </div>
                    <div>
                        <label for="gris_rate" class="block text-sm font-medium text-gray-700 mb-2">Taxa GRIS (%)</label>
                        <input type="number" id="gris_rate" name="gris_rate" value="{{ old('gris_rate', $freightTable->gris_rate * 100) }}" step="0.0001" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <p class="text-xs text-gray-500 mt-1">Padrão: 0,30%</p>
                    </div>
                    <div>
                        <label for="gris_minimum" class="block text-sm font-medium text-gray-700 mb-2">GRIS Mínimo (R$)</label>
                        <input type="number" id="gris_minimum" name="gris_minimum" value="{{ old('gris_minimum', $freightTable->gris_minimum) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="toll_per_100kg" class="block text-sm font-medium text-gray-700 mb-2">Pedágio por 100kg (R$)</label>
                        <input type="number" id="toll_per_100kg" name="toll_per_100kg" value="{{ old('toll_per_100kg', $freightTable->toll_per_100kg) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="cubage_factor" class="block text-sm font-medium text-gray-700 mb-2">Fator de Cubagem (kg/m³)</label>
                        <input type="number" id="cubage_factor" name="cubage_factor" value="{{ old('cubage_factor', $freightTable->cubage_factor) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="min_freight_rate_vs_nf" class="block text-sm font-medium text-gray-700 mb-2">Frete Mínimo vs NF (%)</label>
                        <input type="number" id="min_freight_rate_vs_nf" name="min_freight_rate_vs_nf" value="{{ old('min_freight_rate_vs_nf', $freightTable->min_freight_rate_vs_nf * 100) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            <!-- Additional Services -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Serviços Adicionais (Opcional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="tde_markets" class="block text-sm font-medium text-gray-700 mb-2">TDE Mercados (R$)</label>
                        <input type="number" id="tde_markets" name="tde_markets" value="{{ old('tde_markets', $freightTable->tde_markets) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="tde_supermarkets_cd" class="block text-sm font-medium text-gray-700 mb-2">TDE CD Supermercados (R$)</label>
                        <input type="number" id="tde_supermarkets_cd" name="tde_supermarkets_cd" value="{{ old('tde_supermarkets_cd', $freightTable->tde_supermarkets_cd) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="palletization" class="block text-sm font-medium text-gray-700 mb-2">Paletização por Pallet (R$)</label>
                        <input type="number" id="palletization" name="palletization" value="{{ old('palletization', $freightTable->palletization) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="unloading_tax" class="block text-sm font-medium text-gray-700 mb-2">Taxa de Descarga (R$)</label>
                        <input type="number" id="unloading_tax" name="unloading_tax" value="{{ old('unloading_tax', $freightTable->unloading_tax) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            <!-- Rates -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Taxas Especiais (%)</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="weekend_holiday_rate" class="block text-sm font-medium text-gray-700 mb-2">Fim de Semana/Feriado (%)</label>
                        <input type="number" id="weekend_holiday_rate" name="weekend_holiday_rate" value="{{ old('weekend_holiday_rate', $freightTable->weekend_holiday_rate * 100) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="redelivery_rate" class="block text-sm font-medium text-gray-700 mb-2">Reentrega (%)</label>
                        <input type="number" id="redelivery_rate" name="redelivery_rate" value="{{ old('redelivery_rate', $freightTable->redelivery_rate * 100) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="return_rate" class="block text-sm font-medium text-gray-700 mb-2">Devolução (%)</label>
                        <input type="number" id="return_rate" name="return_rate" value="{{ old('return_rate', $freightTable->return_rate * 100) }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('freight-tables.show', $freightTable) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>

    <script>
        // Show/hide CEP range fields based on destination type
        document.getElementById('destination_type').addEventListener('change', function() {
            const cepFields = document.getElementById('cep_range_fields');
            if (this.value === 'cep_range') {
                cepFields.classList.remove('hidden');
            } else {
                cepFields.classList.add('hidden');
            }
        });

        // Trigger on page load
        document.addEventListener('DOMContentLoaded', function() {
            const destinationType = document.getElementById('destination_type').value;
            if (destinationType === 'cep_range') {
                document.getElementById('cep_range_fields').classList.remove('hidden');
            }
        });
    </script>
</body>
</html>





















