@extends('layouts.app')

@section('title', 'Nova Proposta Comercial - TMS SaaS')
@section('page-title', 'Nova Proposta Comercial')

@push('styles')
@include('shared.styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

    .proposal-container {
        max-width: 900px;
        margin: 0 auto;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .proposal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 30px 40px;
        background-color: #f8f9fa;
        border-bottom: 4px solid var(--cor-acento);
    }

    .proposal-header img { 
        max-height: 70px; 
    }

    .proposal-header-info { 
        text-align: right; 
    }

    .proposal-header-info h1 { 
        margin: 0; 
        color: var(--cor-principal); 
        font-size: 26px; 
        font-weight: 700; 
    }

    .proposal-header-info p { 
        margin: 5px 0 0; 
        font-size: 14px; 
        color: #555; 
    }

    .proposal-content { 
        padding: 30px 40px; 
        color: #333;
    }

    .proposal-content p,
    .proposal-content h2,
    .proposal-content h3 {
        color: #333;
    }

    .calculator-section {
        background-color: #f8f9fa;
        padding: 30px;
        margin: -30px -40px 30px -40px;
        border-bottom: 1px solid #ddd;
    }

    .form-grid { 
        display: grid; 
        grid-template-columns: repeat(2, 1fr); 
        gap: 20px; 
    }

    .form-group { 
        display: flex; 
        flex-direction: column; 
    }

    .form-group label { 
        margin-bottom: 8px; 
        font-weight: 500; 
        color: var(--cor-principal); 
    }

    .form-group input, 
    .form-group select { 
        padding: 12px; 
        border: 1px solid #ccc; 
        border-radius: 4px; 
        font-size: 16px; 
        background-color: #fff;
        color: #333;
        transition: all 0.3s ease;
    }

    .form-group select:hover, 
    .form-group input:hover {
        border-color: var(--cor-acento);
    }

    .form-group select:focus, 
    .form-group input:focus {
        outline: none;
        border-color: var(--cor-acento);
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
    }

    .form-group.full-width { 
        grid-column: 1 / -1; 
    }

    .buttons-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 20px;
    }

    .btn-calculate {
        background-color: var(--cor-acento);
        color: white;
        padding: 15px;
        border: none;
        border-radius: 4px;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s ease, opacity 0.3s ease;
    }

    .btn-calculate:hover { 
        opacity: 0.9;
        filter: brightness(0.95);
    }

    .btn-create {
        background-color: var(--cor-principal);
        color: white;
        padding: 15px;
        border: none;
        border-radius: 4px;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s ease, opacity 0.3s ease;
    }

    .btn-create:hover { 
        opacity: 0.9;
        filter: brightness(0.95);
    }

    .btn-create:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    #result-section {
        margin-top: 20px;
        padding: 20px;
        background-color: #e4f0f7;
        border-left: 5px solid var(--cor-principal);
        border-radius: 4px;
        text-align: center;
        display: none;
    }

    #result-section p { 
        margin: 0; 
        font-size: 18px; 
    }

    #result-section .price { 
        font-size: 28px; 
        font-weight: 700; 
        color: var(--cor-principal); 
    }

    .calculation-details {
        font-size: 14px;
        text-align: left;
        margin-top: 15px;
        background-color: #d4e0e7;
        padding: 10px;
        border-radius: 4px;
    }

    .error-message {
        color: #f44336;
        font-size: 0.9em;
        margin-top: 5px;
    }

    .intro {
        margin-bottom: 30px;
    }

    .intro p {
        font-size: 16px;
    }

    h2 {
        color: var(--cor-principal);
        border-bottom: 2px solid var(--cor-acento);
        padding-bottom: 10px;
        margin-top: 40px;
        margin-bottom: 20px;
        font-size: 22px;
    }

    .price-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 14px;
    }

    .price-table th, 
    .price-table td { 
        border: 1px solid #ddd; 
        padding: 12px; 
        text-align: center; 
        color: #333;
    }

    .price-table td:first-child { 
        text-align: left; 
        font-weight: 500; 
        color: #333;
    }

    .price-table thead { 
        background-color: var(--cor-principal); 
        color: #ffffff; 
        font-weight: 500; 
    }

    .price-table thead th {
        color: #ffffff;
    }

    .price-table tbody {
        background-color: #ffffff;
    }

    .price-table tbody td {
        color: #333;
        background-color: #ffffff;
    }

    .price-table tbody tr:nth-child(even) { 
        background-color: #f9f9f9; 
    }

    .price-table tbody tr:nth-child(even) td {
        background-color: #f9f9f9;
        color: #333;
    }

    .price-table tbody tr:hover { 
        background-color: #fdeee7; 
    }

    .price-table tbody tr:hover td {
        background-color: #fdeee7;
        color: #333;
    }

    .price-table tbody tr.highlighted {
        background-color: rgba(33, 150, 243, 0.2);
        border: 2px solid rgba(33, 150, 243, 0.5);
    }

    .price-table tbody tr.highlighted td {
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .proposal-container { 
            border-radius: 0; 
            box-shadow: none; 
        }

        .proposal-header { 
            flex-direction: column; 
            gap: 20px; 
            text-align: center; 
        }

        .proposal-header-info { 
            text-align: center; 
        }

        .proposal-content { 
            padding: 20px; 
        }

        .calculator-section { 
            margin: -20px -20px 30px -20px; 
            padding: 20px; 
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="proposal-container" id="proposal-content">
    <header class="proposal-header">
        <div>
            <h1 style="color: var(--cor-principal); margin: 0;">Proposta Comercial</h1>
            <p style="margin: 5px 0 0; color: #555;">Proposta Nº: <span id="proposal-number"></span> | Data: <span id="proposal-date"></span></p>
        </div>
    </header>

    <main class="proposal-content">
        <!-- Seção da Calculadora -->
        <section class="calculator-section">
            <h2 style="margin-top: 0;">Calculadora de Frete Aproximado</h2>
            
            <form id="freight-calculator-form" action="{{ route('proposals.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="client_id">Cliente *</label>
                        <select name="client_id" id="client_id" required>
                            <option value="">Selecione um cliente</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" 
                                        {{ ($selectedClient && $selectedClient->id == $client->id) ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="salesperson_id">Vendedor *</label>
                        <select name="salesperson_id" id="salesperson_id" required>
                            <option value="">Selecione um vendedor</option>
                            @foreach($salespeople as $salesperson)
                                <option value="{{ $salesperson->id }}" 
                                        data-max-discount="{{ $salesperson->max_discount_percentage }}">
                                    {{ $salesperson->name }} (Desconto Máx: {{ number_format($salesperson->max_discount_percentage, 2) }}%)
                                </option>
                            @endforeach
                        </select>
                        @error('salesperson_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="destination">Destino *</label>
                        <select name="destination" id="destination" required>
                            <option value="">Selecione um destino</option>
                            @foreach($freightTables as $table)
                                <option value="{{ $table->destination_name }}">
                                    {{ $table->destination_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="origin">Origem</label>
                        <input type="text" id="origin" name="origin" value="São Paulo / SP" disabled>
                    </div>

                    <div class="form-group">
                        <label for="weight">Peso Real (em Kg) *</label>
                        <input type="number" id="weight" name="weight" step="0.01" min="0" placeholder="Ex: 55" required>
                    </div>

                    <div class="form-group">
                        <label for="cubage">Cubagem (m³)</label>
                        <input type="number" id="cubage" name="cubage" step="0.01" min="0" placeholder="Ex: 0.5">
                    </div>

                    <div class="form-group full-width">
                        <label for="invoice_value">Valor da Nota Fiscal (R$) *</label>
                        <input type="number" id="invoice_value" name="invoice_value" step="0.01" min="0" placeholder="Ex: 1500.00" required>
                    </div>

                    <div class="form-group">
                        <label for="discount_percentage">Desconto (%)</label>
                        <input type="number" id="discount_percentage" name="discount_percentage" step="0.01" min="0" max="100" value="0" placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label for="valid_until">Válido até</label>
                        <input type="date" id="valid_until" name="valid_until" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>

                    <div class="form-group full-width">
                        <label for="title">Título da Proposta *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="Ex: Proposta de Frete - Belo Horizonte">
                        @error('title')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Descrição</label>
                        <textarea name="description" id="description" rows="3" placeholder="Descrição da proposta...">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="notes">Observações</label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Observações adicionais..."></textarea>
                    </div>
                </div>

                <div class="buttons-container">
                    <button type="button" id="calculate-btn" class="btn-calculate">Calcular Frete</button>
                    <button type="submit" id="create-proposal-btn" class="btn-create" disabled>Criar Proposta</button>
                </div>

                <div id="result-section"></div>
            </form>
        </section>

        <div class="intro">
            <p style="color: #333;">Apresentamos nossas condições comerciais para a prestação de serviços de transporte rodoviário de suas mercadorias na modalidade "Carga Seca".</p>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: var(--cor-principal);">Tabela de Fretes</h2>
            <a href="{{ route('freight-tables.create') }}" class="btn-create" style="padding: 10px 20px; font-size: 14px; text-decoration: none; display: inline-block; color: white;">
                <i class="fas fa-plus"></i> Adicionar Novo Destino
            </a>
        </div>

        @if($freightTables->isEmpty())
            <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-table" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                <h3 style="color: #666; margin-bottom: 10px;">Nenhuma tabela de frete cadastrada</h3>
                <p style="color: #999; margin-bottom: 20px;">Comece adicionando um novo destino para criar sua tabela de fretes.</p>
                <a href="{{ route('freight-tables.create') }}" class="btn-create" style="padding: 12px 24px; font-size: 16px; text-decoration: none; display: inline-block;">
                    <i class="fas fa-plus"></i> Adicionar Primeiro Destino
                </a>
            </div>
        @else
            <table class="price-table">
                <thead>
                    <tr>
                        <th rowspan="2">DESTINO</th>
                        <th colspan="4">ATÉ 100Kgs</th>
                        <th colspan="2">ACIMA DE 100Kgs</th>
                        <th rowspan="2" style="width: 100px;">AÇÕES</th>
                    </tr>
                    <tr>
                        <th>De 0 à 30kgs</th>
                        <th>De 31 à 50kgs</th>
                        <th>De 51 à 70kgs</th>
                        <th>De 71 à 100kgs</th>
                        <th>FRETE PESO</th>
                        <th>TAXA CTRC</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($freightTables as $table)
                    <tr data-destination="{{ $table->destination_name }}">
                        <td style="color: #333;"><strong>{{ $table->destination_name }}</strong></td>
                        <td style="color: #333;">R$ {{ $table->weight_0_30 ? number_format($table->weight_0_30, 2, ',', '.') : '-' }}</td>
                        <td style="color: #333;">R$ {{ $table->weight_31_50 ? number_format($table->weight_31_50, 2, ',', '.') : '-' }}</td>
                        <td style="color: #333;">R$ {{ $table->weight_51_70 ? number_format($table->weight_51_70, 2, ',', '.') : '-' }}</td>
                        <td style="color: #333;">R$ {{ $table->weight_71_100 ? number_format($table->weight_71_100, 2, ',', '.') : '-' }}</td>
                        <td style="color: #333;">R$ {{ $table->weight_over_100_rate ? number_format($table->weight_over_100_rate, 4, ',', '.') : '-' }}/kg</td>
                        <td style="color: #333;">R$ {{ $table->ctrc_tax ? number_format($table->ctrc_tax, 2, ',', '.') : '-' }}</td>
                        <td style="text-align: center;">
                            <a href="{{ route('freight-tables.edit', $table) }}" 
                               style="color: var(--cor-acento); margin: 0 5px; font-size: 18px;" 
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </main>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const day = String(today.getDate()).padStart(2, '0');
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const year = today.getFullYear();
        const formattedDate = `${day}/${month}/${year}`;
        const proposalNumber = String(Date.now()).slice(-8);

        document.getElementById('proposal-number').textContent = proposalNumber;
        document.getElementById('proposal-date').textContent = formattedDate;

        const calculateBtn = document.getElementById('calculate-btn');
        const createProposalBtn = document.getElementById('create-proposal-btn');
        const resultSection = document.getElementById('result-section');
        const freightForm = document.getElementById('freight-calculator-form');
        let calculatedFreightValue = 0;

        // Calculate freight
        calculateBtn.addEventListener('click', async function() {
            const destination = document.getElementById('destination').value;
            const weight = parseFloat(document.getElementById('weight').value) || 0;
            const cubage = parseFloat(document.getElementById('cubage').value) || 0;
            const invoiceValue = parseFloat(document.getElementById('invoice_value').value) || 0;

            if (!destination) {
                alert("Por favor, selecione um destino.");
                return;
            }

            if (weight <= 0 && cubage <= 0) {
                alert("Por favor, insira um Peso ou Cubagem válido.");
                return;
            }

            if (invoiceValue <= 0) {
                alert("Por favor, insira um Valor de Nota Fiscal válido.");
                return;
            }

            calculateBtn.disabled = true;
            calculateBtn.textContent = 'Calculando...';

            try {
                const response = await fetch('{{ url("/proposals/calculate-freight") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        destination: destination,
                        weight: weight,
                        cubage: cubage,
                        invoice_value: invoiceValue
                    })
                });

                const data = await response.json();

                if (data.success) {
                    calculatedFreightValue = data.data.total;
                    const breakdown = data.data.breakdown;

                    resultSection.innerHTML = `
                        <p>Valor Aproximado do Frete (com taxas):</p>
                        <p class="price" id="freight-result">R$ ${formatCurrency(calculatedFreightValue)}</p>
                        <div class="calculation-details">
                            <strong>Detalhamento do Cálculo:</strong><br>
                            - Peso Taxado: ${formatNumber(breakdown.chargeable_weight)} kg <em style="font-size:12px;">(maior valor entre peso real e cubado)</em><br>
                            - Frete Peso Base: R$ ${formatCurrency(breakdown.freight_weight)}<br>
                            - Ad Valorem (0,40%): R$ ${formatCurrency(breakdown.ad_valorem)}<br>
                            - GRIS (0,30%, mín. R$ 8,70): R$ ${formatCurrency(breakdown.gris)}<br>
                            - Pedágio (R$ 12,95 x fração de 100kg): R$ ${formatCurrency(breakdown.toll)}
                            ${breakdown.minimum_applied ? `<br><em>* Aplicado frete mínimo de 1% do valor da NF (R$ ${formatCurrency(breakdown.minimum_value)})</em>` : ''}
                        </div>
                    `;
                    resultSection.style.display = 'block';
                    createProposalBtn.disabled = false;
                } else {
                    alert('Erro ao calcular frete: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (error) {
                alert('Erro ao calcular frete: ' + error.message);
            } finally {
                calculateBtn.disabled = false;
                calculateBtn.textContent = 'Calcular Frete';
            }
        });

        // Create proposal
        freightForm.addEventListener('submit', function(e) {
            if (calculatedFreightValue <= 0) {
                e.preventDefault();
                alert('Por favor, calcule o frete antes de criar a proposta.');
                return false;
            }

            const discountPercentage = parseFloat(document.getElementById('discount_percentage').value) || 0;
            const salespersonId = document.getElementById('salesperson_id').value;
            const salespersonSelect = document.getElementById('salesperson_id');
            const selectedOption = salespersonSelect.options[salespersonSelect.selectedIndex];
            const maxDiscount = parseFloat(selectedOption.getAttribute('data-max-discount')) || 0;

            if (discountPercentage > maxDiscount) {
                e.preventDefault();
                alert(`Desconto máximo permitido para este vendedor é ${maxDiscount}%`);
                return false;
            }

            // Set base_value hidden field
            if (!document.getElementById('base_value_hidden')) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'base_value_hidden';
                hiddenInput.name = 'base_value';
                hiddenInput.value = calculatedFreightValue;
                freightForm.appendChild(hiddenInput);
            } else {
                document.getElementById('base_value_hidden').value = calculatedFreightValue;
            }

            // Set title if empty
            const titleField = document.getElementById('title');
            if (!titleField.value || titleField.value.trim() === '') {
                titleField.value = `Proposta de Frete - ${document.getElementById('destination').value}`;
            }

            createProposalBtn.disabled = true;
            createProposalBtn.textContent = 'Criando...';
            
            // Form will submit normally
            return true;
        });

        function formatCurrency(value) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value);
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }

        // Highlight table row when destination is selected
        const destinationSelect = document.getElementById('destination');
        const tableRows = document.querySelectorAll('.price-table tbody tr');

        destinationSelect.addEventListener('change', function() {
            // Remove all highlights
            tableRows.forEach(row => row.classList.remove('highlighted'));
            
            // Highlight selected destination row
            if (this.value) {
                const selectedRow = document.querySelector(`tr[data-destination="${this.value}"]`);
                if (selectedRow) {
                    selectedRow.classList.add('highlighted');
                    // Scroll to the highlighted row
                    selectedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
</script>
@endpush
@endsection
