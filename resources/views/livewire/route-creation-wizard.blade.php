<div class="command-center-container">
    <div class="command-grid">
        <!-- Left Pane: Route Specs -->
        <div class="command-pane pane-left">
            <div class="pane-header">
                <h2>Parâmetros da Rota</h2>
                <p>Configure os dados operacionais.</p>
            </div>
            <div class="pane-body">
                <div class="industrial-input-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label style="margin-bottom: 0;">IDENTIFICAÇÃO (NOME) *</label>
                        <button type="button" wire:click="autoGenerateRouteName" style="background: rgba(255,107,53,0.15); color: var(--cor-acento); border: 1px solid rgba(255,107,53,0.3); border-radius: 4px; padding: 2px 8px; font-size: 0.75em; font-weight: 600; cursor: pointer;">
                            ✨ Gerar Nome Automático
                        </button>
                    </div>
                    <input type="text" wire:model="name" placeholder="Ex: Rota Belo Horizonte/MG → São Paulo/SP">
                    @error('name') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="industrial-input-group">
                    <label><i class="fas fa-calendar-alt"></i> DATA E HORA PROGRAMADA *</label>
                    <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 10px;">
                        <div>
                            <span style="font-size: 0.75em; color: rgba(255,255,255,0.6); display: block; margin-bottom: 4px;">Data Agendada</span>
                            <input type="date" wire:model="scheduled_date" style="width: 100%;">
                        </div>
                        <div>
                            <span style="font-size: 0.75em; color: rgba(255,255,255,0.6); display: block; margin-bottom: 4px;">Horário (Hora)</span>
                            <input type="time" wire:model="start_time" style="width: 100%;">
                        </div>
                    </div>
                    @error('scheduled_date') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="industrial-input-group">
                    <label>MOTORISTA RESPONSÁVEL</label>
                    <select wire:model="driver_id">
                        <option value="">Selecione...</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="industrial-input-group">
                    <label>VEÍCULO</label>
                    <select wire:model="vehicle_id">
                        <option value="">Selecione...</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->plate }} ({{ $vehicle->model }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="industrial-input-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label style="margin-bottom: 0;"><i class="fas fa-building"></i> EMPRESA / FILIAL DE ORIGEM</label>
                        <button type="button" wire:click="$toggle('is_custom_origin')" style="background: rgba(255,255,255,0.1); color: var(--cor-acento); border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; padding: 2px 8px; font-size: 0.75em; font-weight: 600; cursor: pointer;">
                            {{ $is_custom_origin ? '🏢 Selecionar da Lista' : '✏️ Digitar Outro Local' }}
                        </button>
                    </div>

                    @if(!$is_custom_origin && count($originOptions) > 0)
                        <select wire:model="origin_branch">
                            <option value="">Selecione a Empresa / Filial de Partida...</option>
                            @foreach($originOptions as $opt)
                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" wire:model.lazy="origin_branch" placeholder="Digite o local de partida (ex: Galpão Betim/MG)...">
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Pane: Cargo & XML -->
        <div class="command-pane pane-right">
            <div class="pane-header flex-between">
                <div>
                    <h2>Gestão de Cargas e CT-es</h2>
                    <p>Digite números de CT-e, faça upload de XMLs ou selecione cargas.</p>
                </div>
                <button type="button" x-data x-on:click="$dispatch('open-manual-modal')" class="btn-primary" style="padding: 8px 15px; font-size: 0.8rem; border-radius: 2px;">
                    <i class="fas fa-plus mr-1"></i> Nova Carga Manual
                </button>
            </div>

            <!-- Inserção Manual de CT-es -->
            <div class="industrial-input-group" style="margin: 15px 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label style="color: var(--cor-acento); font-size: 0.85em; font-weight: 600; margin-bottom: 0;">
                        <i class="fas fa-file-invoice"></i> DIGITAÇÃO MANUAL / COLAR CT-ES
                    </label>
                    <span style="font-size: 0.75em; color: rgba(255,255,255,0.5);">Clique fora para processar e selecionar abaixo</span>
                </div>
                <textarea wire:model.lazy="manual_cte_numbers" 
                          wire:change="processManualCteNumbers"
                          placeholder="Cole ou digite os números dos CT-es (ex: 2506, 2507/2508, 2509). Clique fora para juntar e marcar como selecionadas..." 
                          style="width: 100%; min-height: 60px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 10px; border-radius: 4px; font-size: 0.85em;"></textarea>

                @if(!empty($cteErrorMessage))
                    <div style="background: rgba(255,75,75,0.15); border: 1px solid #ff4b4b; color: #ff6b6b; padding: 12px 15px; border-radius: 6px; margin-top: 10px; font-size: 0.85em; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 1.2em; flex-shrink: 0;"></i>
                        <div>{!! $cteErrorMessage !!}</div>
                    </div>
                @endif
            </div>

            <!-- Manual Cargo Modal (Alpine + Livewire) -->
            <div x-data="{ open: false }" 
                 x-show="open" 
                 x-on:open-manual-modal.window="open = true"
                 x-on:close-manual-modal.window="open = false"
                 style="display: none;" 
                 class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm">
                
                <div class="command-pane w-full max-w-lg" x-on:click.away="open = false">
                    <div class="pane-header flex-between">
                        <h2>Criar Carga Manual</h2>
                        <button type="button" x-on:click="open = false" class="text-white opacity-50 hover:opacity-100"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="pane-body">
                        <div class="industrial-input-group">
                            <label>NOME DO DESTINATÁRIO *</label>
                            <input type="text" wire:model.defer="manual_receiver_name" placeholder="Ex: Cliente Final S/A">
                            @error('manual_receiver_name') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="industrial-input-group">
                                <label>CIDADE DE ENTREGA *</label>
                                <input type="text" wire:model.defer="manual_delivery_city" placeholder="Ex: São Paulo">
                                @error('manual_delivery_city') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="industrial-input-group">
                                <label>ESTADO (UF) *</label>
                                <input type="text" wire:model.defer="manual_delivery_state" maxlength="2" placeholder="Ex: SP">
                                @error('manual_delivery_state') <span class="error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="industrial-input-group">
                                <label>PESO (KG) *</label>
                                <input type="number" step="0.1" wire:model.defer="manual_weight" placeholder="0.0">
                                @error('manual_weight') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            <div class="industrial-input-group">
                                <label>VALOR DA MERCADORIA *</label>
                                <input type="number" step="0.01" wire:model.defer="manual_value" placeholder="0.00">
                                @error('manual_value') <span class="error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="industrial-input-group">
                            <label>DESCRIÇÃO OPCIONAL</label>
                            <input type="text" wire:model.defer="manual_description" placeholder="Ex: 5 Caixas de eletrônicos">
                        </div>

                        <div class="flex justify-end gap-3 mt-4">
                            <button type="button" x-on:click="open = false" class="px-4 py-2 text-sm text-white opacity-60 hover:opacity-100">Cancelar</button>
                            <button type="button" wire:click="createManualShipment" wire:loading.attr="disabled" class="btn-execute" style="padding: 10px 20px; font-size: 0.9rem;">
                                <span wire:loading.remove wire:target="createManualShipment">SALVAR E ADICIONAR</span>
                                <span wire:loading wire:target="createManualShipment">CRIANDO...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pane-body">
                <!-- Dropzone -->
                <!-- Dropzone -->
                <div class="xml-dropzone" x-data="{ isDropping: false }" 
                     x-on:dragover.prevent="isDropping = true" 
                     x-on:dragleave.prevent="isDropping = false" 
                     x-on:drop.prevent="
                         isDropping = false; 
                         let files = $event.dataTransfer.files;
                         let hasZip = false;
                         let xmlCount = 0;
                         for (let i = 0; i < files.length; i++) {
                             if (files[i].name.toLowerCase().endsWith('.zip')) {
                                 hasZip = true;
                             } else {
                                 xmlCount++;
                             }
                         }
                         if (!hasZip && xmlCount > 20) {
                             alert('⚠️ MUITOS ARQUIVOS SELECIONADOS (' + xmlCount + ' XMLs)\n\nPara enviar mais de 20 arquivos de uma só vez, por favor compacte todos os XMLs em um único arquivo .ZIP antes de enviar.');
                             return;
                         }
                         $wire.uploadMultiple('xml_files', files);
                     "
                     x-bind:class="{ 'dropping': isDropping }">
                    <input type="file" id="xml-upload" class="hidden-input" multiple accept=".xml,.zip"
                           x-on:change="
                               let files = $event.target.files;
                               let hasZip = false;
                               let xmlCount = 0;
                               for (let i = 0; i < files.length; i++) {
                                   if (files[i].name.toLowerCase().endsWith('.zip')) {
                                       hasZip = true;
                                   } else {
                                       xmlCount++;
                                   }
                               }
                               if (!hasZip && xmlCount > 20) {
                                   $event.target.value = '';
                                   alert('⚠️ MUITOS ARQUIVOS SELECIONADOS (' + xmlCount + ' XMLs)\n\nPara enviar mais de 20 arquivos de uma só vez, por favor compacte todos os XMLs em um único arquivo .ZIP antes de enviar.');
                                   return;
                               }
                               $wire.uploadMultiple('xml_files', files);
                           ">
                    <label for="xml-upload" class="dropzone-label">
                        <i class="fas fa-file-upload"></i>
                        <span wire:loading.remove wire:target="xml_files">Arraste os arquivos XML ou pacotes ZIP contendo os CT-es aqui ou clique para buscar</span>
                        <span wire:loading wire:target="xml_files">Processando arquivos e descompactando pacotes ZIP...</span>
                    </label>
                </div>

                <!-- Overlay Processamento de XML/ZIP (Livewire) -->
                <div id="xml-processing-overlay" wire:loading wire:target="xml_files" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(13, 27, 42, 0.85); z-index: 10000; align-items: center; justify-content: center; flex-direction: column; gap: 20px; backdrop-filter: blur(5px);">
                    <div style="background: var(--cor-secundaria); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 30px; text-align: center; max-width: 450px; width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
                        <i class="fas fa-file-archive fa-spin" style="font-size: 3.5rem; color: var(--cor-acento); margin-bottom: 20px;"></i>
                        <h3 style="color: var(--cor-texto-claro); font-size: 1.3rem; margin-bottom: 10px;">Processando XMLs</h3>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9rem; margin-bottom: 20px;">Descompactando e Processando Arquivos .ZIP... Por favor aguarde.</p>
                        <div style="width: 100%; height: 12px; background: rgba(255,255,255,0.1); border-radius: 6px; overflow: hidden; position: relative;">
                            <div style="width: 100%; height: 100%; background: repeating-linear-gradient(45deg, var(--cor-acento), var(--cor-acento) 10px, #ff885a 10px, #ff885a 20px); animation: progress-bar-stripes 1s linear infinite; border-radius: 6px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Shipment List -->
                <div class="shipment-list-container mt-6">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 15px;">
                        <div style="position: relative; flex: 1;">
                            <input type="text" 
                                   wire:model.debounce.300ms="searchShipment" 
                                   placeholder="🔍 Buscar por número de CT-e, destinatário ou cidade no sistema..." 
                                   style="width: 100%; padding: 10px 12px 10px 36px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; color: #fff; font-size: 0.85em;">
                            <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.5);"></i>
                        </div>
                        <div style="font-size: 0.8em; color: rgba(255,255,255,0.6); white-space: nowrap;">
                            Exibindo {{ $availableShipments->count() }} cargas disponíveis
                        </div>
                    </div>

                    <div style="max-height: 480px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; background: rgba(0,0,0,0.2);">
                        <table class="industrial-table" style="margin: 0;">
                            <thead style="position: sticky; top: 0; background: #18222d; z-index: 10; shadow: 0 2px 4px rgba(0,0,0,0.5);">
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" wire:model="selectAll" class="industrial-checkbox" title="Selecionar Todos">
                                    </th>
                                    <th>RASTREIO / CT-E</th>
                                    <th>DESTINO</th>
                                    <th class="text-right">PESO</th>
                                    <th class="text-right">VALOR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($availableShipments as $shipment)
                                    <tr class="{{ in_array($shipment->id, $selectedShipments) ? 'selected-row' : '' }}">
                                        <td>
                                            <input type="checkbox" wire:model="selectedShipments" value="{{ $shipment->id }}" class="industrial-checkbox">
                                        </td>
                                        <td class="font-mono">
                                            {{ $shipment->tracking_number }}
                                            @if(in_array($shipment->id, $selectedShipments))
                                                <span class="inline-flex items-center justify-center px-2 py-1 ml-2 text-xs font-bold leading-none text-white bg-green-600 rounded">Add</span>
                                            @endif
                                        </td>
                                        <td>{{ $shipment->receiver_name }}<br><small style="opacity: 0.5;">{{ $shipment->delivery_city }}/{{ $shipment->delivery_state }}</small></td>
                                        <td class="text-right">{{ $shipment->weight }}kg</td>
                                        <td class="text-right text-accent">R$ {{ number_format($shipment->value, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-8 opacity-50">Nenhuma carga pendente no sistema. Faça upload de CT-es para começar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sticky Footer -->
    <div class="command-footer">
        <div class="footer-stats">
            <div class="stat">
                <span class="label">CARGAS</span>
                <span class="value">{{ count($selectedShipments) }}</span>
            </div>
            <div class="stat">
                <span class="label">PESO TOTAL</span>
                <span class="value">{{ number_format($total_weight, 2, ',', '.') }} kg</span>
            </div>
            <div class="stat">
                <span class="label">VALOR DA ROTA</span>
                <span class="value text-accent">R$ {{ number_format($total_value, 2, ',', '.') }}</span>
            </div>
        </div>
        <div class="footer-actions">
            <button wire:click="save" class="btn-execute">
                CONCLUIR E CRIAR ROTA <i class="fas fa-bolt ml-2"></i>
            </button>
        </div>
    </div>

    <style>
        .command-center-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding-bottom: 100px;
            font-family: 'Inter', sans-serif;
        }
        .command-grid {
            display: grid;
            grid-template-columns: 35% 64%;
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 1024px) {
            .command-grid {
                grid-template-columns: 1fr;
            }
        }
        .command-pane {
            background: #111820; /* Dark brutalist bg */
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .pane-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(0, 0, 0, 0.2);
        }
        .pane-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #fff;
            margin: 0;
        }
        .pane-header p {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.4);
            margin: 4px 0 0 0;
        }
        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pane-body {
            padding: 25px;
        }
        .industrial-input-group {
            margin-bottom: 20px;
        }
        .industrial-input-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 8px;
        }
        .industrial-input-group input, 
        .industrial-input-group select {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 12px 15px;
            border-radius: 2px;
            font-family: inherit;
            transition: all 0.2s;
            font-size: 0.95rem;
        }
        .industrial-input-group input:focus, 
        .industrial-input-group select:focus {
            outline: none;
            border-color: var(--cor-acento);
            background: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 0 1px var(--cor-acento);
        }
        .error {
            color: #ff4b4b;
            font-size: 0.75rem;
            margin-top: 5px;
            display: block;
        }
        
        .xml-dropzone {
            border: 2px dashed rgba(255, 255, 255, 0.15);
            padding: 40px 20px;
            text-align: center;
            background: rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            transition: all 0.2s;
            position: relative;
        }
        .xml-dropzone.dropping {
            border-color: var(--cor-acento);
            background: rgba(255, 107, 53, 0.05); /* rgba value of accent */
        }
        .hidden-input {
            opacity: 0;
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            cursor: pointer;
        }
        .dropzone-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.6);
            pointer-events: none;
            font-size: 0.95rem;
        }
        .dropzone-label i {
            font-size: 2.2rem;
            color: var(--cor-acento);
            opacity: 0.8;
        }

        .industrial-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .industrial-table th {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.4);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        .industrial-table td {
            padding: 14px 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            vertical-align: middle;
            transition: background 0.2s;
            color: #ececec;
        }
        .industrial-table tbody tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }
        .selected-row td {
            background: rgba(255, 107, 53, 0.05) !important;
            border-bottom-color: rgba(255, 107, 53, 0.1);
        }
        .font-mono {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            letter-spacing: 0.5px;
            color: #fff;
        }
        .text-accent {
            color: var(--cor-acento);
            font-weight: 700;
        }
        .industrial-checkbox {
            width: 16px;
            height: 16px;
            accent-color: var(--cor-acento);
            cursor: pointer;
        }

        .command-footer {
            position: fixed;
            bottom: 0;
            left: 280px; /* Sidebar width from app.blade.php typical layout */
            right: 0;
            background: #0d1318;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 50;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.5);
        }
        @media (max-width: 991px) {
            .command-footer {
                left: 0;
                padding: 15px 20px;
            }
        }
        @media (max-width: 768px) {
            .command-footer {
                flex-direction: column;
                gap: 15px;
            }
        }
        .footer-stats {
            display: flex;
            gap: 50px;
        }
        .stat {
            display: flex;
            flex-direction: column;
        }
        .stat .label {
            font-size: 0.65rem;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .stat .value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
        }
        .btn-execute {
            background: var(--cor-acento);
            color: #000;
            font-weight: 800;
            font-size: 1rem;
            padding: 16px 35px;
            border: none;
            border-radius: 2px;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.2);
        }
        .btn-execute:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
            background: #ff7f50;
        }
    </style>
</div>