@extends('layouts.app')

@section('page-title', 'Meu Plano e Faturas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Current Subscription -->
        <div class="col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-gem"></i> Meu Plano Atual</h6>
                </div>
                <div class="card-body">
                    @if(Auth::user()->tenant && Auth::user()->tenant->currentSubscription())
                        @php
                            $sub = Auth::user()->tenant->currentSubscription();
                        @endphp
                        <div class="row text-center">
                            <div class="col-md-3 border-end">
                                <h4 class="text-muted text-uppercase small">Plano</h4>
                                <h3>{{ $sub->plan->name ?? 'Desconhecido' }}</h3>
                            </div>
                            <div class="col-md-3 border-end">
                                <h4 class="text-muted text-uppercase small">Status</h4>
                                <h3 class="text-{{ $sub->status == 'active' ? 'success' : 'warning' }}">{{ ucfirst($sub->status) }}</h3>
                            </div>
                            <div class="col-md-3 border-end">
                                <h4 class="text-muted text-uppercase small">Valor</h4>
                                <h3>R$ {{ number_format($sub->amount, 2, ',', '.') }} <small class="text-muted" style="font-size: 0.5em;">/{{ $sub->billing_cycle == 'monthly' ? 'mês' : 'ano' }}</small></h3>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-muted text-uppercase small">Próxima Cobrança</h4>
                                <h3>{{ $sub->ends_at ? $sub->ends_at->format('d/m/Y') : 'N/A' }}</h3>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                            <h4>Nenhum plano ativo</h4>
                            <p class="text-muted">Você não possui uma assinatura ativa. Escolha um plano abaixo para liberar todas as funcionalidades do sistema.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Available Plans (Upgrade) -->
    <h4 class="mb-4 mt-4" style="color: var(--cor-texto-claro); font-weight: 700;">Opções de Upgrade</h4>
    <div class="row">
        @php
            $featureLabels = [
                'basic_tracking' => 'Rastreamento Básico',
                'email_support' => 'Suporte por E-mail',
                'basic_reports' => 'Relatórios Básicos',
                'user_management' => 'Gestão de Usuários',
                'advanced_tracking' => 'Rastreamento Avançado',
                'whatsapp_ai' => 'Assistente IA no WhatsApp',
                'fiscal_integration' => 'Integração Fiscal',
                'api_access' => 'Acesso via API',
                'advanced_reports' => 'Relatórios Avançados',
                'route_optimization' => 'Otimização de Rotas',
                'priority_support' => 'Suporte Prioritário',
                'all_features' => 'Todas as Funcionalidades',
                'custom_integrations' => 'Integrações Customizadas',
                'white_label' => 'White Label / Personalizado',
                'dedicated_support' => 'Gerente de Contas Dedicado',
                'custom_reports' => 'Relatórios Customizados',
                'advanced_analytics' => 'Analytics Avançado'
            ];
        @endphp

        @foreach($plans as $plan)
        <div class="col-lg-4 mb-4">
            <div class="card h-100 d-flex flex-column" style="background-color: var(--cor-secundaria); border: 1px solid {{ $plan->is_popular ? 'var(--cor-acento)' : 'rgba(255,255,255,0.1)' }}; border-radius: 15px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); position: relative; transition: transform 0.3s ease;">
                @if($plan->is_popular)
                    <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--cor-acento); color: var(--cor-principal); padding: 4px 15px; border-radius: 20px; font-size: 0.8em; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 10px rgba(255,107,53,0.3);">
                        MAIS POPULAR
                    </div>
                @endif
                <div class="text-center d-flex flex-column flex-grow-1" style="margin-top: {{ $plan->is_popular ? '15px' : '0' }};">
                    <h2 style="color: var(--cor-acento); font-size: 24px; font-weight: 700; margin-bottom: 10px;">{{ $plan->name }}</h2>
                    <h3 style="color: var(--cor-texto-claro); font-size: 28px; font-weight: 700; margin-bottom: 15px;">
                        R$ {{ number_format($plan->price, 0, ',', '.') }}<small style="font-size: 0.5em; opacity: 0.7;">/mês</small>
                    </h3>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 20px; min-height: 40px;">{{ $plan->description }}</p>
                    <hr style="border-color: rgba(255,255,255,0.1); margin-bottom: 20px;">
                    <ul class="list-unstyled text-start mb-4 flex-grow-1" style="color: var(--cor-texto-claro); font-size: 0.95em; padding-left: 0;">
                        @foreach($plan->features as $feature)
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-check" style="color: #4caf50; margin-right: 8px;"></i> 
                                <span>{{ $featureLabels[$feature] ?? ucfirst(str_replace('_', ' ', $feature)) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div style="margin-top: auto;">
                        @if(!Auth::user()->tenant || !Auth::user()->tenant->currentSubscription())
                            <a href="{{ route('subscriptions.show', $plan) }}" class="btn-primary" style="width: 100%; text-align: center; justify-content: center; padding: 12px; display: inline-flex;">Assinar Agora</a>
                        @else
                            @if(Auth::user()->tenant->currentSubscription()->plan_id == $plan->id)
                                <button class="btn-secondary" style="width: 100%; text-align: center; justify-content: center; opacity: 0.5; cursor: not-allowed; display: inline-flex;" disabled>Plano Atual</button>
                            @else
                                <a href="{{ route('subscriptions.show', $plan) }}" class="btn-primary" style="width: 100%; text-align: center; justify-content: center; padding: 12px; display: inline-flex;">Fazer Upgrade</a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection























