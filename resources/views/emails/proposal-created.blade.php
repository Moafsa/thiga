<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Proposta Comercial</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #ff6b35;
        }
        .header h1 {
            color: #ff6b35;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .proposal-info {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .proposal-info h2 {
            color: #333;
            margin-top: 0;
            font-size: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .value-highlight {
            font-size: 24px;
            font-weight: bold;
            color: #ff6b35;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #ff6b35;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #e55a2b;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚚 Nova Proposta Comercial</h1>
        </div>
        
        <div class="content">
            <p>Olá, <strong>{{ $client->name }}</strong>!</p>
            
            <p>Uma nova proposta comercial foi criada para você:</p>
            
            <div class="proposal-info">
                <h2>{{ $proposal->title }}</h2>
                
                <div class="info-row">
                    <span class="info-label">Número da Proposta:</span>
                    <span class="info-value">{{ $proposal->proposal_number }}</span>
                </div>
                
                @if($proposal->description)
                <div class="info-row">
                    <span class="info-label">Descrição:</span>
                    <span class="info-value">{{ $proposal->description }}</span>
                </div>
                @endif
                
                <div class="info-row">
                    <span class="info-label">Valor Base:</span>
                    <span class="info-value">R$ {{ number_format($proposal->base_value, 2, ',', '.') }}</span>
                </div>
                
                @if($proposal->discount_percentage > 0)
                <div class="info-row">
                    <span class="info-label">Desconto:</span>
                    <span class="info-value">{{ number_format($proposal->discount_percentage, 2, ',', '.') }}% (R$ {{ number_format($proposal->discount_value, 2, ',', '.') }})</span>
                </div>
                @endif
                
                <div class="info-row">
                    <span class="info-label">Valor Final:</span>
                    <span class="info-value value-highlight">R$ {{ number_format($proposal->final_value, 2, ',', '.') }}</span>
                </div>
                
                @if($proposal->valid_until)
                <div class="info-row">
                    <span class="info-label">Válida até:</span>
                    <span class="info-value">{{ $proposal->valid_until->format('d/m/Y') }}</span>
                </div>
                @endif
                
                @if($proposal->weight || $proposal->cubage)
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                    <strong>Dados da Carga:</strong>
                    @if($proposal->weight)
                    <div class="info-row">
                        <span class="info-label">Peso:</span>
                        <span class="info-value">{{ number_format($proposal->weight, 2, ',', '.') }} kg</span>
                    </div>
                    @endif
                    @if($proposal->cubage)
                    <div class="info-row">
                        <span class="info-label">Cubagem:</span>
                        <span class="info-value">{{ number_format($proposal->cubage, 3, ',', '.') }} m³</span>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            
            <div style="text-align: center;">
                <a href="{{ route('client.proposals.show', $proposal) }}" class="button">
                    Ver Proposta Completa
                </a>
            </div>
            
            @if($proposal->notes)
            <div style="margin-top: 20px; padding: 15px; background-color: #fff9e6; border-left: 4px solid #ffc107; border-radius: 5px;">
                <strong>Observações:</strong>
                <p style="margin: 5px 0 0 0;">{{ $proposal->notes }}</p>
            </div>
            @endif
        </div>
        
        <div class="footer">
            <p>Atenciosamente,<br>
            <strong>{{ $tenant->name }}</strong></p>
            <p style="margin-top: 10px;">Este é um email automático, por favor não responda.</p>
        </div>
    </div>
</body>
</html>
