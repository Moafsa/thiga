<div>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--cor-acento);">
                <i class="fas fa-bullseye mr-2"></i> CRM & Funil de Vendas
            </h1>
            <p style="color: var(--cor-texto-claro); opacity: 0.7;">Gerencie suas oportunidades de negócio (Pipeline: {{ $pipeline->name }})</p>
        </div>
        <div class="w-full md:w-1/3">
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nome, fone, conteúdo..." class="w-full bg-gray-800 text-white rounded-lg pl-10 pr-4 py-2 border border-gray-700 focus:border-green-500 focus:outline-none shadow-sm" style="background-color: rgba(255,255,255,0.05); color: var(--cor-texto-claro);">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400" style="opacity: 0.6;"></i>
                
                <div wire:loading wire:target="search" class="absolute right-3 top-3">
                    <i class="fas fa-spinner fa-spin text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="flex space-x-4 overflow-x-auto pb-4" style="height: calc(100vh - 220px); align-items: flex-start;">
        @foreach($stages as $stage)
            <div class="flex-shrink-0 w-80 rounded-lg flex flex-col" style="background-color: var(--cor-secundaria); max-height: 100%;"
                 x-data
                 @dragover.prevent="$el.style.backgroundColor = 'rgba(255,255,255,0.05)'"
                 @dragleave.prevent="$el.style.backgroundColor = 'var(--cor-secundaria)'"
                 @drop.prevent="
                    $el.style.backgroundColor = 'var(--cor-secundaria)';
                    const dealId = event.dataTransfer.getData('text/plain');
                    @this.updateDealStage(dealId, {{ $stage->id }});
                 ">
                
                <div class="p-3 border-b border-gray-700 flex justify-between items-center" style="border-top: 3px solid {{ $stage->color ?? '#3498db' }}; border-radius: 8px 8px 0 0;">
                    <h3 class="font-bold text-lg" style="color: var(--cor-texto-claro);">{{ $stage->name }}</h3>
                    <span class="px-2 py-1 text-xs rounded-full" style="background-color: var(--cor-principal); color: var(--cor-texto-claro);">
                        {{ $stage->deals->count() }}
                    </span>
                </div>

                <div class="p-3 flex-1 space-y-3 overflow-y-auto custom-scrollbar">
                    @forelse($stage->deals as $deal)
                        <div class="p-3 rounded-lg cursor-pointer transition-transform transform hover:-translate-y-1 shadow-lg bg-gray-800 {{ $this->getDealColorClass($deal) }}"
                             style="background-color: var(--cor-principal);"
                             draggable="true"
                             @dragstart="event.dataTransfer.setData('text/plain', {{ $deal->id }})"
                             wire:click="openDealModal({{ $deal->id }})">
                            
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-sm" style="color: var(--cor-texto-claro);">{{ $deal->title }}</h4>
                                <i class="fab fa-{{ strtolower($deal->contact_channel) == 'whatsapp' ? 'whatsapp text-green-500' : 'envelope text-blue-400' }}"></i>
                            </div>

                            @if($deal->client)
                                <div class="text-xs mb-2 truncate" style="color: rgba(245, 245, 245, 0.7);">
                                    <i class="fas fa-user mr-1"></i> {{ $deal->client->name }}
                                </div>
                            @endif

                            <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-700">
                                <div class="font-bold text-sm" style="color: var(--cor-acento);">
                                    R$ {{ number_format($deal->lead_value, 2, ',', '.') }}
                                </div>
                                @if($deal->next_action_date)
                                    <div class="text-xs {{ \Carbon\Carbon::parse($deal->next_action_date)->isPast() && !\Carbon\Carbon::parse($deal->next_action_date)->isToday() ? 'text-red-400 font-bold' : 'text-gray-400' }}">
                                        <i class="far fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($deal->next_action_date)->format('d/m') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-sm border-2 border-dashed border-gray-700 rounded-lg" style="color: rgba(245, 245, 245, 0.4);">
                            Nenhum card
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Deal 360 -->
    @if($showDealModal && $selectedDeal)
        <div class="fixed inset-0 z-[1050] flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none bg-black bg-opacity-75">
            <div class="relative w-full max-w-5xl mx-auto my-6">
                <!-- Modal content -->
                <div class="relative flex flex-col w-full rounded-lg shadow-lg outline-none focus:outline-none" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255,255,255,0.1);">
                    
                    <!-- Header -->
                    <div class="flex items-start justify-between p-5 border-b border-solid rounded-t" style="border-color: rgba(255,255,255,0.1);">
                        <div>
                            <h3 class="text-2xl font-semibold" style="color: var(--cor-texto-claro);">
                                {{ $selectedDeal->title }}
                            </h3>
                            <div class="text-sm mt-1" style="color: rgba(245, 245, 245, 0.7);">
                                @if($selectedDeal->client)
                                    Cliente: <strong>{{ $selectedDeal->client->name }}</strong> | 
                                @endif
                                Estágio Atual: <strong style="color: {{ $selectedDeal->stage->color ?? 'var(--cor-acento)' }}">{{ $selectedDeal->stage->name }}</strong>
                            </div>
                        </div>
                        <button class="p-1 ml-auto border-0 text-white float-right text-3xl leading-none font-semibold outline-none focus:outline-none opacity-50 hover:opacity-100" wire:click="closeDealModal">
                            <span class="block w-6 h-6 text-2xl outline-none focus:outline-none">×</span>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="relative p-6 flex-auto">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <!-- Left Col: Details & Insight -->
                            <div class="md:col-span-1 space-y-6">
                                <!-- Info Panel -->
                                <div class="p-4 rounded-lg" style="background-color: var(--cor-principal);">
                                    <h4 class="font-bold mb-3 border-b border-gray-700 pb-2" style="color: var(--cor-acento);">Detalhes da Oportunidade</h4>
                                    
                                    <div class="mb-2">
                                        <label class="text-xs uppercase" style="color: rgba(245, 245, 245, 0.5);">Valor Estimado</label>
                                        <div class="font-bold text-lg text-green-400">R$ {{ number_format($selectedDeal->lead_value, 2, ',', '.') }}</div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <label class="text-xs uppercase" style="color: rgba(245, 245, 245, 0.5);">Próxima Ação</label>
                                        <div class="text-sm" style="color: var(--cor-texto-claro);">
                                            {{ $selectedDeal->next_action_date ? \Carbon\Carbon::parse($selectedDeal->next_action_date)->format('d/m/Y') : 'Não definida' }}
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="text-xs uppercase" style="color: rgba(245, 245, 245, 0.5);">Canal de Contato</label>
                                        <div class="text-sm capitalize" style="color: var(--cor-texto-claro);">
                                            <i class="fab fa-{{ strtolower($selectedDeal->contact_channel) == 'whatsapp' ? 'whatsapp text-green-500' : 'envelope text-blue-400' }}"></i> {{ $selectedDeal->contact_channel }}
                                        </div>
                                    </div>
                                </div>

                                <!-- AI Insight Panel -->
                                <div class="p-4 rounded-lg border border-purple-500" style="background-color: rgba(156, 39, 176, 0.1);">
                                    <h4 class="font-bold mb-2 flex items-center" style="color: #e040fb;">
                                        <i class="fas fa-robot mr-2"></i> Insight da IA
                                    </h4>
                                    <p class="text-sm" style="color: var(--cor-texto-claro); line-height: 1.6;">
                                        {{ $selectedDeal->ai_summary ?: 'A IA ainda não gerou um resumo para esta negociação. O resumo é gerado automaticamente após interações via WhatsApp.' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Right Col: Interactions/History -->
                            <div class="md:col-span-2">
                                <div class="p-4 rounded-lg h-full" style="background-color: var(--cor-principal);">
                                    <h4 class="font-bold mb-4 border-b border-gray-700 pb-2 flex justify-between items-center" style="color: var(--cor-acento);">
                                        <span><i class="fas fa-history mr-2"></i> Histórico de Interações</span>
                                        <span class="text-xs px-2 py-1 rounded bg-gray-700">{{ $selectedDeal->interactions->count() }} registros</span>
                                    </h4>
                                    
                                    <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                                        @forelse($selectedDeal->interactions as $interaction)
                                            <div class="p-3 rounded-lg border border-gray-700 {{ $interaction->sender_type == 'client' ? 'ml-0 mr-8 bg-gray-800' : 'ml-8 mr-0 bg-blue-900 bg-opacity-20' }}">
                                                <div class="flex justify-between items-center mb-1 text-xs">
                                                    <span class="font-bold" style="color: {{ $interaction->sender_type == 'client' ? 'var(--cor-texto-claro)' : 'var(--cor-acento)' }};">
                                                        @if($interaction->sender_type == 'client')
                                                            {{ $selectedDeal->client->name ?? 'Cliente' }}
                                                        @elseif($interaction->sender_type == 'ai')
                                                            <i class="fas fa-robot text-purple-400"></i> Agente IA
                                                        @else
                                                            <i class="fas fa-user-tie"></i> {{ $interaction->user->name ?? 'Sistema' }}
                                                        @endif
                                                    </span>
                                                    <span style="color: rgba(245, 245, 245, 0.5);">{{ $interaction->created_at->format('d/m/Y H:i') }}</span>
                                                </div>
                                                <div class="text-sm whitespace-pre-wrap" style="color: var(--cor-texto-claro);">{{ $interaction->content }}</div>
                                            </div>
                                        @empty
                                            <div class="text-center py-8" style="color: rgba(245, 245, 245, 0.4);">
                                                <i class="fas fa-comment-slash text-4xl mb-3"></i>
                                                <p>Nenhuma interação registrada ainda.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fixed inset-0 z-[1040] bg-black opacity-50"></div>
    @endif

    @push('scripts')
        <script src="https://cdn.tailwindcss.com"></script>
    @endpush
</div>
