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
    <h4 class="mb-4 mt-4">Opções de Upgrade</h4>
    <div class="row">
        @foreach($plans as $plan)
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100 {{ $plan->is_popular ? 'border-primary' : '' }}">
                @if($plan->is_popular)
                    <div class="card-header bg-primary text-white text-center py-2">
                        <strong>MAIS POPULAR</strong>
                    </div>
                @endif
                <div class="card-body text-center d-flex flex-column">
                    <h2 class="card-title text-primary">{{ $plan->name }}</h2>
                    <h3 class="card-subtitle mb-3 text-muted">
                        R$ {{ number_format($plan->price, 0, ',', '.') }}<small>/mês</small>
                    </h3>
                    <p class="card-text">{{ $plan->description }}</p>
                    <hr>
                    <ul class="list-unstyled text-start mb-4 flex-grow-1">
                        @foreach($plan->features as $feature)
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> {{ ucfirst(str_replace('_', ' ', $feature)) }}</li>
                        @endforeach
                    </ul>
                    @if(!Auth::user()->tenant || !Auth::user()->tenant->currentSubscription())
                        <a href="{{ route('subscriptions.show', $plan) }}" class="btn btn-primary btn-block w-100 mt-auto">Assinar Agora</a>
                    @else
                        @if(Auth::user()->tenant->currentSubscription()->plan_id == $plan->id)
                            <button class="btn btn-secondary btn-block w-100 mt-auto" disabled>Plano Atual</button>
                        @else
                            <a href="{{ route('subscriptions.show', $plan) }}" class="btn btn-outline-primary btn-block w-100 mt-auto">Fazer Upgrade</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection























