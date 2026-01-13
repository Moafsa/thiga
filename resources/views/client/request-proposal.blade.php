@extends('client.layout')

@section('title', 'Solicitar Proposta - TMS SaaS')

@section('content')
<div class="client-card">
    <h2 class="section-title">
        <i class="fas fa-file-invoice"></i> Solicitar Nova Proposta
    </h2>

    <form method="POST" action="{{ route('client.store-proposal-request') }}">
        @csrf

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Título da Proposta</label>
            <input type="text" name="title" value="{{ old('title') }}" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Descrição</label>
            <textarea name="description" rows="3" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">{{ old('description') }}</textarea>
        </div>

        <h3 style="margin: 20px 0 15px; color: var(--cor-acento);">Origem</h3>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Endereço</label>
            <input type="text" name="pickup_address" value="{{ old('pickup_address') }}" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
        </div>
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 10px; margin-bottom: 15px;">
            <input type="text" name="pickup_city" value="{{ old('pickup_city') }}" placeholder="Cidade" required style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
            <input type="text" name="pickup_state" value="{{ old('pickup_state') }}" placeholder="UF" maxlength="2" required style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
            <input type="text" name="pickup_zip_code" value="{{ old('pickup_zip_code') }}" placeholder="CEP" required style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
        </div>

        <h3 style="margin: 20px 0 15px; color: var(--cor-acento);">Destino</h3>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Endereço</label>
            <input type="text" name="delivery_address" value="{{ old('delivery_address') }}" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
        </div>
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 10px; margin-bottom: 15px;">
            <input type="text" name="delivery_city" value="{{ old('delivery_city') }}" placeholder="Cidade" required style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
            <input type="text" name="delivery_state" value="{{ old('delivery_state') }}" placeholder="UF" maxlength="2" required style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
            <input type="text" name="delivery_zip_code" value="{{ old('delivery_zip_code') }}" placeholder="CEP" required style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
        </div>

        <h3 style="margin: 20px 0 15px; color: var(--cor-acento);">Informações do Frete</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Peso (kg)</label>
                <input type="number" name="weight" value="{{ old('weight') }}" step="0.01" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Volume (m³)</label>
                <input type="number" name="volume" value="{{ old('volume') }}" step="0.01" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Data de Coleta</label>
                <input type="date" name="pickup_date" value="{{ old('pickup_date') }}" min="{{ date('Y-m-d') }}" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Data de Entrega</label>
                <input type="date" name="delivery_date" value="{{ old('delivery_date') }}" min="{{ date('Y-m-d') }}" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
            </div>
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Valor Estimado (R$)</label>
            <input type="number" name="value" value="{{ old('value') }}" step="0.01" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Observações</label>
            <textarea name="notes" rows="3" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">{{ old('notes') }}</textarea>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%;">
            <i class="fas fa-paper-plane"></i> Enviar Solicitação
        </button>
    </form>
</div>
@endsection
