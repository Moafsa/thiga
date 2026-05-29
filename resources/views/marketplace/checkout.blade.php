@extends('layouts.app')

@section('title', 'TMS LOG - Checkout Co-loading')
@section('page-title', 'Checkout Co-loading')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-lg mb-4" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 107, 53, 0.2) !important;">
                <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0 text-center">
                    <i class="fas fa-shield-alt fa-3x text-warning mb-2"></i>
                    <h4 class="fw-bold text-white mb-1">Pagamento Seguro em Custódia</h4>
                    <p class="text-white-50 mb-0">TMS LOG Compartilhado protege sua transação de ponta a ponta.</p>
                </div>
                
                <div class="card-body p-4">
                    <div class="row g-4 mb-4">
                        <!-- Invoice Breakdown Column -->
                        <div class="col-md-6">
                            <h5 class="fw-bold text-white mb-3">📋 Resumo da Carga</h5>
                            <div class="bg-black p-3 rounded-3 mb-3" style="border: 1px solid rgba(255,255,255,0.05);">
                                <span class="text-white-50 small d-block mb-1">Carga a ser Transportada:</span>
                                <span class="text-white fw-bold d-block mb-3">{{ $booking->cargo_title }}</span>

                                <div class="row mb-2">
                                    <div class="col-6">
                                        <span class="text-white-50 small d-block">Peso total:</span>
                                        <span class="text-white small fw-bold">{{ number_format($booking->booked_weight, 0, ',', '.') }} kg</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-white-50 small d-block">Volume total:</span>
                                        <span class="text-white small fw-bold">{{ number_format($booking->booked_volume, 2, ',', '.') }} m³</span>
                                    </div>
                                </div>

                                <span class="text-white-50 small d-block mb-1">Rotas de Desvio de Coleta:</span>
                                <span class="text-white small fw-bold d-block">{{ $booking->pickup_city }}/{{ $booking->pickup_state }} ➔ {{ $booking->delivery_city }}/{{ $booking->delivery_state }}</span>
                            </div>
                        </div>

                        <!-- Pricing Breakdown Column -->
                        <div class="col-md-6">
                            <h5 class="fw-bold text-white mb-3">💲 Demonstrativo Financeiro</h5>
                            <div class="bg-black p-3 rounded-3" style="border: 1px solid rgba(255,255,255,0.05);">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-white-50 small">Custo Base de Frete</span>
                                    <span class="text-white small font-monospace">R$ {{ number_format($booking->amount_base, 2, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-white-50 small">Taxa de Desvio Km</span>
                                    <span class="text-white small font-monospace">R$ {{ number_format($booking->amount_detour_cost, 2, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-white-50 small">Taxa de Intermediação</span>
                                    <span class="text-white small font-monospace">R$ {{ number_format($booking->amount_platform_fee, 2, ',', '.') }}</span>
                                </div>
                                <hr class="border-secondary my-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-warning fw-bold">Total Final</span>
                                    <span class="text-warning fw-bold font-monospace h5 mb-0">R$ {{ number_format($booking->amount_final, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment methods selection -->
                    <h5 class="fw-bold text-white mb-3">💳 Forma de Pagamento</h5>
                    <form action="{{ route('marketplace.bookings.pay', $booking->id) }}" method="POST">
                        @csrf
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="payment_method" id="pay_pix" value="pix" checked>
                                <label class="btn btn-outline-warning w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-2" for="pay_pix">
                                    <i class="fa-brands fa-pix fa-2x"></i>
                                    <span class="fw-bold small">Pagar com PIX</span>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="payment_method" id="pay_boleto" value="boleto">
                                <label class="btn btn-outline-warning w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-2" for="pay_boleto">
                                    <i class="fas fa-barcode fa-2x"></i>
                                    <span class="fw-bold small">Boleto Bancário</span>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="payment_method" id="pay_card" value="credit_card">
                                <label class="btn btn-outline-warning w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-2" for="pay_card">
                                    <i class="fas fa-credit-card fa-2x"></i>
                                    <span class="fw-bold small">Cartão de Crédito</span>
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 rounded-3 p-3 mb-4" style="background-color: rgba(0, 123, 255, 0.1); color: #007bff;">
                            <i class="fas fa-info-circle me-2"></i><strong>Simulação de Payout Sandbox:</strong> Como o ambiente está rodando de forma local, ao clicar em finalizar o pagamento, a cobrança splitada do Asaas será mockada e confirmada instantaneamente, permitindo que você valide o fluxo completo de custódia e Ledger de capacidades físicas!
                        </div>

                        <button type="submit" class="btn btn-warning w-100 rounded-pill py-3 fw-bold fs-5 shadow-sm">
                            <i class="fas fa-lock me-2"></i>Confirmar Pagamento e Reservar Espaço
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
