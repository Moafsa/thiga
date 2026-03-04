<div class="wizard-container">
    <!-- Progress Bar -->
    <div class="wizard-steps mb-8">
        <div class="step {{ $step >= 1 ? 'active' : '' }}">
            <div class="step-icon">1</div>
            <span>Dados Básicos</span>
        </div>
        <div class="line {{ $step >= 2 ? 'active' : '' }}"></div>
        <div class="step {{ $step >= 2 ? 'active' : '' }}">
            <div class="step-icon">2</div>
            <span>Cargas</span>
        </div>
        <div class="line {{ $step >= 3 ? 'active' : '' }}"></div>
        <div class="step {{ $step >= 3 ? 'active' : '' }}">
            <div class="step-icon">3</div>
            <span>Resumo</span>
        </div>
    </div>

    <!-- Step 1: Basic Info -->
    @if($step == 1)
        <div class="reco-card animate-in">
            <div class="reco-card-header">
                <span class="reco-title"><i class="fas fa-info-circle mr-2"></i> Informações da Rota</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="filter-group">
                    <label class="block text-sm font-semibold mb-2">Nome da Rota *</label>
                    <input type="text" wire:model="name" class="filter-input w-full" placeholder="Ex: Rota Matinal Centro">
                    @error('name') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="filter-group">
                    <label class="block text-sm font-semibold mb-2">Data Programada *</label>
                    <input type="date" wire:model="scheduled_date" class="filter-input w-full">
                </div>
                <div class="filter-group">
                    <label class="block text-sm font-semibold mb-2">Motorista</label>
                    <select wire:model="driver_id" class="filter-input w-full">
                        <option value="">Selecione um motorista</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="block text-sm font-semibold mb-2">Veículo</label>
                    <select wire:model="vehicle_id" class="filter-input w-full">
                        <option value="">Selecione um veículo</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->plate }} - {{ $vehicle->model }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <button wire:click="nextStep" class="btn-filter">Próximo: Selecionar Cargas <i
                        class="fas fa-arrow-right ml-2"></i></button>
            </div>
        </div>
    @endif

    <!-- Step 2: Shipments -->
    @if($step == 2)
        <div class="reco-card animate-in">
            <div class="reco-card-header">
                <span class="reco-title"><i class="fas fa-box mr-2"></i> Selecionar Cargas e Entregas</span>
            </div>

            <div class="mb-6">
                <h3 class="text-sm font-bold uppercase opacity-60 mb-4">Cargas Disponíveis (Shipments)</h3>
                <div class="overflow-x-auto">
                    <table class="reco-table">
                        <thead>
                            <tr>
                                <th width="40"></th>
                                <th>Rastreio</th>
                                <th>Destinatário</th>
                                <th style="text-align: right;">Peso</th>
                                <th style="text-align: right;">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($availableShipments as $shipment)
                                <tr>
                                    <td><input type="checkbox" wire:model="selectedShipments" value="{{ $shipment->id }}"></td>
                                    <td>{{ $shipment->tracking_number }}</td>
                                    <td>{{ $shipment->receiver_name }}</td>
                                    <td style="text-align: right;">{{ $shipment->weight }}kg</td>
                                    <td style="text-align: right;">R$ {{ number_format($shipment->value, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-between mt-8">
                <button wire:click="prevStep" class="btn-filter" style="background: rgba(255,255,255,0.1); color: #fff;"><i
                        class="fas fa-arrow-left mr-2"></i> Voltar</button>
                <button wire:click="nextStep" class="btn-filter">Próximo: Revisar Rota <i
                        class="fas fa-arrow-right ml-2"></i></button>
            </div>
        </div>
    @endif

    <!-- Step 3: Summary -->
    @if($step == 3)
        <div class="reco-card animate-in">
            <div class="reco-card-header">
                <span class="reco-title"><i class="fas fa-check-circle mr-2"></i> Resumo e Confirmação</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="summary-box">
                    <span class="label">Total de Cargas</span>
                    <span class="value">{{ count($selectedShipments) + count($selectedCargo) }}</span>
                </div>
                <!-- More summary boxes... -->
            </div>

            <div class="flex justify-between">
                <button wire:click="prevStep" class="btn-filter" style="background: rgba(255,255,255,0.1); color: #fff;"><i
                        class="fas fa-arrow-left mr-2"></i> Ajustar Cargas</button>
                <button wire:click="save" class="btn-filter" style="background: #4caf50;">Criar Rota Agora <i
                        class="fas fa-rocket ml-2"></i></button>
            </div>
        </div>
    @endif

    <style>
        .wizard-steps {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 2;
            position: relative;
        }

        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--cor-secundaria);
            border: 2px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
            transition: all 0.3s;
        }

        .step.active .step-icon {
            background: var(--cor-acento);
            border-color: var(--cor-acento);
            color: var(--cor-principal);
        }

        .step span {
            font-size: 0.75em;
            font-weight: 600;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.4);
        }

        .step.active span {
            color: #fff;
        }

        .line {
            flex: 1;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
            margin: 0 15px;
            margin-bottom: 20px;
            position: relative;
        }

        .line.active {
            background: var(--cor-acento);
            opacity: 0.5;
        }

        .animate-in {
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .summary-box {
            background: rgba(255, 255, 255, 0.03);
            padding: 15px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
        }

        .summary-box .label {
            font-size: 0.7em;
            text-transform: uppercase;
            opacity: 0.5;
            margin-bottom: 5px;
        }

        .summary-box .value {
            font-size: 1.4em;
            font-weight: 700;
            color: var(--cor-acento);
        }
    </style>
</div>