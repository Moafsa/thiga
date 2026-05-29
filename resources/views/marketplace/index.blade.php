@extends('layouts.app')

@section('title', 'TMS LOG Compartilhado - Marketplace de Capacidade')
@section('page-title', 'TMS LOG Compartilhado')

@section('content')
<div class="container-fluid py-4">
    <!-- Header banner -->
    <div class="p-4 mb-4 rounded-4" style="background: linear-gradient(135deg, var(--cor-secundaria) 0%, rgba(255, 107, 53, 0.15) 100%); border: 1px solid rgba(255, 107, 53, 0.25);">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold text-white mb-2">🚛 Marketplace de Co-loading</h2>
                <p class="text-white-50 mb-0">Potencialize suas rotas aproveitando espaços ociosos. Transportadoras parceiras compartilham capacidade física de carga, reduzem custos de frete e aumentam a margem operacional de forma automática e integrada.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('marketplace.my-offers') }}" class="btn btn-outline-warning rounded-pill px-4 me-2">
                    <i class="fas fa-list-ul me-2"></i>Minhas Ofertas
                </a>
                <a href="{{ route('marketplace.bookings') }}" class="btn btn-warning rounded-pill px-4">
                    <i class="fas fa-handshake me-2"></i>Minhas Reservas
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert" style="background-color: rgba(40, 167, 69, 0.15); border: 1px solid rgba(40, 167, 69, 0.3); color: #2ecc71;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert" style="background-color: rgba(220, 53, 69, 0.15); border: 1px solid rgba(220, 53, 69, 0.3); color: #e74c3c;">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Sidebar Search / Listing Panel -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 rounded-4 shadow-sm mb-4" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
                <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-white mb-0">🔍 Encontrar Espaço Compatível</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('marketplace.index') }}" method="GET">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Cidade de Coleta</label>
                            <input type="text" name="pickup_city" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="Ex: Caxias do Sul" value="{{ request('pickup_city') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Estado de Coleta</label>
                            <input type="text" name="pickup_state" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="Ex: RS" value="{{ request('pickup_state') }}" maxlength="2" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Cidade de Entrega</label>
                            <input type="text" name="delivery_city" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="Ex: São Paulo" value="{{ request('delivery_city') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Estado de Entrega</label>
                            <input type="text" name="delivery_state" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="Ex: SP" value="{{ request('delivery_state') }}" maxlength="2" required>
                        </div>
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label text-white-50 small">Peso (kg)</label>
                                <input type="number" name="weight" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="Ex: 500" value="{{ request('weight') }}" min="1" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-white-50 small">Volume (m³)</label>
                                <input type="number" name="volume" step="0.01" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="Ex: 1.5" value="{{ request('volume') }}" min="0.01" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 rounded-pill py-2 fw-semibold">
                            <i class="fas fa-search me-2"></i>Buscar Rotas Compatíveis
                        </button>
                    </form>
                </div>
            </div>

            <!-- Publish capacity space card -->
            <div class="card border-0 rounded-4 shadow-sm" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
                <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-white mb-0">📢 Disponibilizar Espaço Livre</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('marketplace.offers.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Selecione uma Rota Ativa</label>
                            <select name="route_id" class="form-select bg-dark border-secondary text-white rounded-3" required>
                                <option value="">-- Escolha a Rota --</option>
                                @foreach($myRoutes as $myRoute)
                                    <option value="{{ $myRoute->id }}">{{ $myRoute->name }} ({{ $myRoute->start_city }}/{{ $myRoute->start_state }} ➔ {{ $myRoute->end_city }}/{{ $myRoute->end_state }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label text-white-50 small">Peso Disponível (kg)</label>
                                <input type="number" name="offered_weight" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="Ex: 2000" min="1" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-white-50 small">Volume Disp. (m³)</label>
                                <input type="number" name="offered_volume" step="0.01" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="Ex: 8.5" min="0.01" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label text-white-50 small">Preço/kg (R$)</label>
                                <input type="number" name="price_per_kg" step="0.01" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="R$ 1,50" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-white-50 small">Preço/m³ (R$)</label>
                                <input type="number" name="price_per_m3" step="0.01" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="R$ 120,00" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Preço Mínimo de Co-frete (R$)</label>
                            <input type="number" name="min_price" step="0.01" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="Ex: 150.00" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-white-50 small">Restrições ou Observações</label>
                            <textarea name="restrictions" class="form-control bg-dark border-secondary text-white rounded-3" rows="2" placeholder="Ex: Sem cargas inflamáveis ou perecíveis..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-outline-warning w-100 rounded-pill py-2 fw-semibold">
                            <i class="fas fa-bullhorn me-2"></i>Publicar Minha Rota
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Marketplace Feed / Search Results -->
        <div class="col-lg-8">
            @if($isSearch)
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="fw-bold text-white mb-0">🏆 Rotas Encontradas ({{ $searchResults->count() }})</h4>
                    <a href="{{ route('marketplace.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-times me-2"></i>Limpar Filtros
                    </a>
                </div>

                @if($searchResults->isEmpty())
                    <div class="card border-0 rounded-4 text-center py-5 px-4 mb-4" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
                        <i class="fas fa-route fa-3x text-warning mb-3 opacity-50"></i>
                        <h5 class="fw-bold text-white mb-2">Nenhuma rota compatível encontrada</h5>
                        <p class="text-white-50 mb-0">Nenhum transportador com capacidade ociosa compatível atende a essa rota com desvio geográfico inferior a 150 km. Tente alterar as condições físicas ou cidades da busca.</p>
                    </div>
                @else
                    @foreach($searchResults as $item)
                        @php
                            $offer = $item['offer'];
                            $route = $item['route'];
                            $pricing = $item['pricing'];
                            $detour = $item['detour_km'];
                        @endphp
                        <div class="card border-0 rounded-4 shadow-sm mb-3" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 107, 53, 0.25) !important; transition: transform 0.2s;">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill small mb-2 fw-semibold">
                                            <i class="fas fa-check-circle me-1"></i>Compatível (Desvio: {{ $detour }} km)
                                        </span>
                                        <h5 class="fw-bold text-white mb-1">{{ $route->name }}</h5>
                                        <p class="text-white-50 small mb-2">
                                            <i class="fas fa-truck-moving me-2"></i>Transportadora: <strong>{{ $offer->tenant->name }}</strong>
                                        </p>
                                        <div class="d-flex flex-wrap gap-3 mb-3">
                                            <span class="text-white-50 small"><i class="fas fa-map-marker-alt me-1 text-danger"></i>Origem: <strong>{{ $route->start_city }}/{{ $route->start_state }}</strong></span>
                                            <span class="text-white-50 small"><i class="fas fa-flag-checkered me-1 text-success"></i>Destino: <strong>{{ $route->end_city }}/{{ $route->end_state }}</strong></span>
                                        </div>
                                        <div class="p-3 bg-dark rounded-3 mb-3">
                                            <div class="row text-center text-md-start">
                                                <div class="col-md-6 mb-2 mb-md-0">
                                                    <span class="text-white-50 small d-block">Preço Unitário</span>
                                                    <span class="text-white font-monospace small">R$ {{ number_format($offer->price_per_kg, 2, ',', '.') }}/kg · R$ {{ number_format($offer->price_per_m3, 2, ',', '.') }}/m³</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <span class="text-white-50 small d-block">Cálculo de Desvio Geográfico</span>
                                                    <span class="text-white font-monospace small">+ R$ {{ number_format($pricing['amount_detour_cost'], 2, ',', '.') }} (Pedágio/Diesel)</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end border-start border-secondary ps-md-4">
                                        <span class="text-white-50 small d-block mb-1">Custo Final Estimado</span>
                                        <h3 class="fw-extrabold text-warning mb-3">R$ {{ number_format($pricing['amount_final'], 2, ',', '.') }}</h3>
                                        <button class="btn btn-warning rounded-pill w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#bookModal-{{ $offer->id }}" data-detour="{{ $detour }}">
                                            <i class="fas fa-shopping-cart me-2"></i>Reservar Espaço
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Request Modal -->
                        <div class="modal fade" id="bookModal-{{ $offer->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 rounded-4 bg-dark text-white">
                                    <div class="modal-header border-secondary p-4">
                                        <h5 class="modal-title fw-bold">🛒 Confirmar Solicitação de Co-loading</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('marketplace.offers.book', $offer->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body p-4">
                                            <p class="text-white-50 small mb-4">Você está solicitando espaço de frete na rota de <strong>{{ $offer->tenant->name }}</strong>. Abaixo estão os dados consolidados da transação protegidos via custódia e split de pagamentos.</p>
                                            
                                            <div class="mb-3">
                                                <label class="form-label text-white-50 small">Título/Descrição da Carga</label>
                                                <input type="text" name="cargo_title" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="Ex: Caixa de peças automotivas" required>
                                            </div>
                                            
                                            <!-- Hidden parameters filled automatically -->
                                            <input type="hidden" name="booked_weight" value="{{ request('weight') }}">
                                            <input type="hidden" name="booked_volume" value="{{ request('volume') }}">
                                            <input type="hidden" name="pickup_city" value="{{ request('pickup_city') }}">
                                            <input type="hidden" name="pickup_state" value="{{ request('pickup_state') }}">
                                            <input type="hidden" name="delivery_city" value="{{ request('delivery_city') }}">
                                            <input type="hidden" name="delivery_state" value="{{ request('delivery_state') }}">
                                            <input type="hidden" name="detour_km" value="{{ $detour }}">

                                            <div class="bg-black p-3 rounded-3 mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-white-50 small">Carga Física</span>
                                                    <span class="text-white small fw-bold">{{ request('weight') }} kg / {{ request('volume') }} m³</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-white-50 small">Valor Base de Carga</span>
                                                    <span class="text-white small font-monospace">R$ {{ number_format($pricing['amount_base'], 2, ',', '.') }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-white-50 small">Taxa de Desvio ({{ $detour }} km)</span>
                                                    <span class="text-white small font-monospace">R$ {{ number_format($pricing['amount_detour_cost'], 2, ',', '.') }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-white-50 small">Taxa de Intermediação (10%)</span>
                                                    <span class="text-white small font-monospace">R$ {{ number_format($pricing['amount_platform_fee'], 2, ',', '.') }}</span>
                                                </div>
                                                <hr class="border-secondary my-2">
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-warning fw-bold">Valor Total a Pagar</span>
                                                    <span class="text-warning font-monospace fw-bold">R$ {{ number_format($pricing['amount_final'], 2, ',', '.') }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="alert alert-warning border-0 rounded-3 p-3 mb-0" style="background-color: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                                <i class="fas fa-shield-alt me-2"></i><strong>Segurança do Split:</strong> O dinheiro ficará guardado em custódia na plataforma. O repasse para a transportadora dona do veículo só será liberado após você confirmar que a entrega foi realizada com sucesso!
                                            </div>
                                        </div>
                                        <div class="modal-footer border-secondary p-4 bg-transparent pt-0">
                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold">Enviar Solicitação</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endif

            <!-- General Public Feed -->
            <h4 class="fw-bold text-white mb-4 mt-2">📋 Ofertas Disponíveis de Outros Parceiros</h4>
            @if($activeOffers->isEmpty())
                <div class="card border-0 rounded-4 text-center py-5 px-4" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
                    <i class="fas fa-globe-americas fa-3x text-warning mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-white mb-2">Feed do marketplace vazio</h5>
                    <p class="text-white-50 mb-0">Nenhuma transportadora disponibilizou espaço ocioso publicamente hoje. Publique seu próprio espaço de rota na barra lateral para começar a consolidar cargas!</p>
                </div>
            @else
                <div class="row">
                    @foreach($activeOffers as $offer)
                        @php
                            $route = $offer->route;
                            $capacity = $route->getAvailableCapacity();
                        @endphp
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 rounded-4 h-100 shadow-sm" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important; transition: transform 0.2s;">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="badge rounded-pill bg-dark text-warning border border-warning px-3 py-2 small">
                                            <i class="fas fa-tags me-1"></i>Co-loading
                                        </span>
                                        <small class="text-white-50">{{ $offer->created_at->diffForHumans() }}</small>
                                    </div>
                                    
                                    <h5 class="fw-bold text-white mb-1">{{ $route->name }}</h5>
                                    <p class="text-white-50 small mb-3">
                                        <i class="fas fa-truck-moving me-2 text-warning"></i>Transportador: <strong>{{ $offer->tenant->name }}</strong>
                                    </p>
                                    
                                    <div class="p-3 bg-dark rounded-3 mb-3 flex-grow-1">
                                        <div class="d-flex justify-content-between text-white-50 small mb-2">
                                            <span>Origem:</span>
                                            <span class="text-white fw-bold">{{ $route->start_city }}/{{ $route->start_state }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between text-white-50 small mb-3">
                                            <span>Destino:</span>
                                            <span class="text-white fw-bold">{{ $route->end_city }}/{{ $route->end_state }}</span>
                                        </div>
                                        
                                        <!-- Capacity gauges -->
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between text-white-50 small mb-1">
                                                <span>Peso Livre:</span>
                                                <span class="text-white fw-bold">{{ number_format($capacity['weight'], 0, ',', '.') }} kg / {{ number_format($offer->offered_weight, 0, ',', '.') }} kg</span>
                                            </div>
                                            <div class="progress bg-black" style="height: 6px;">
                                                @php $weightPct = min(100, ($capacity['weight'] / max(1, $offer->offered_weight)) * 100); @endphp
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $weightPct }}%"></div>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="d-flex justify-content-between text-white-50 small mb-1">
                                                <span>Volume Livre:</span>
                                                <span class="text-white fw-bold">{{ number_format($capacity['volume'], 2, ',', '.') }} m³ / {{ number_format($offer->offered_volume, 2, ',', '.') }} m³</span>
                                            </div>
                                            <div class="progress bg-black" style="height: 6px;">
                                                @php $volumePct = min(100, ($capacity['volume'] / max(1, $offer->offered_volume)) * 100); @endphp
                                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $volumePct }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary">
                                        <div>
                                            <span class="text-white-50 small d-block">Preço Mínimo</span>
                                            <span class="text-warning fw-bold font-monospace">R$ {{ number_format($offer->min_price, 2, ',', '.') }}</span>
                                        </div>
                                        <button class="btn btn-sm btn-outline-warning rounded-pill px-4" onclick="document.querySelector('[name=pickup_city]').scrollIntoView({ behavior: 'smooth' }); alert('Preencha os dados da sua carga no formulário de busca lateral para calcular o desvio de quilometragem e o preço exato!');">
                                            <i class="fas fa-calculator me-1"></i>Calcular Frete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
