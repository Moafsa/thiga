<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <!-- SEO Básico -->
    <title>Rastreio de Carga #{{ $shipment->tracking_number }} | TMS LOG</title>
    <meta name="description" content="Acompanhe o status e a movimentação em tempo real da sua carga de número de rastreamento {{ $shipment->tracking_number }} no portal de rastreamento público do TMS LOG.">
    <meta name="keywords" content="rastreamento de carga, rastreio de encomenda, status da carga, tms log rastreio, {{ $shipment->tracking_number }}">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Rastreio de Carga #{{ $shipment->tracking_number }} | TMS LOG">
    <meta property="og:description" content="Acompanhe o status e a movimentação em tempo real da sua carga de número de rastreamento {{ $shipment->tracking_number }} no portal de rastreamento público do TMS LOG.">
    <meta property="og:image" content="{{ asset('LOGO.svg') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="Rastreio de Carga #{{ $shipment->tracking_number }} | TMS LOG">
    <meta property="twitter:description" content="Acompanhe o status e a movimentação em tempo real da sua carga de número de rastreamento {{ $shipment->tracking_number }} no portal de rastreamento público do TMS LOG.">
    <meta property="twitter:image" content="{{ asset('LOGO.svg') }}">

    <!-- JSON-LD Schema (ParcelDelivery) -->
    @php
        $schemaStatusMap = [
            'pending' => 'https://schema.org/ParcelOrderStatusReceived',
            'scheduled' => 'https://schema.org/ParcelOrderStatusReceived',
            'picked_up' => 'https://schema.org/ParcelOrderStatusPickUp',
            'in_transit' => 'https://schema.org/ParcelInTransit',
            'delivered' => 'https://schema.org/ParcelDelivered',
            'returned' => 'https://schema.org/ParcelOrderStatusReturned',
            'cancelled' => 'https://schema.org/ParcelOrderStatusCancelled',
        ];
        $schemaStatus = $schemaStatusMap[$shipment->status] ?? 'https://schema.org/ParcelOrderStatusReceived';
    @endphp
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "ParcelDelivery",
        "deliveryStatus": "{{ $schemaStatus }}",
        "carrier": {
            "@type": "Organization",
            "name": "TMS LOG",
            "url": "{{ url('/') }}"
        },
        "itemShipped": {
            "@type": "Product",
            "name": "Encomenda #{{ $shipment->tracking_number }}"
        },
        "trackingNumber": "{{ $shipment->tracking_number }}",
        "trackingUrl": "{{ url()->current() }}",
        "originAddress": {
            "@type": "PostalAddress",
            "addressLocality": "{{ $shipment->pickup_city }}",
            "addressRegion": "{{ $shipment->pickup_state }}",
            "addressCountry": "BR"
        },
        "deliveryAddress": {
            "@type": "PostalAddress",
            "addressLocality": "{{ $shipment->delivery_city }}",
            "addressRegion": "{{ $shipment->delivery_state }}",
            "addressCountry": "BR"
        }
    }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg: #0a1628;
            --bg2: #0f1f35;
            --bg3: #162840;
            --accent: #FF6B35;
            --accent2: #FFB347;
            --text: #e2e8f0;
            --muted: #8fa4bd;
            --border: rgba(255,255,255,0.08);
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(circle at 10% 20%, rgba(255, 107, 53, 0.05) 0%, transparent 40%),
                              radial-gradient(circle at 90% 80%, rgba(255, 179, 71, 0.03) 0%, transparent 40%);
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            background: var(--bg2);
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 30px 80px rgba(0,0,0,0.6);
            overflow: hidden;
            animation: fadeUp 600ms ease both;
        }
        
        .header {
            background: linear-gradient(135deg, var(--bg3) 0%, var(--bg2) 100%);
            padding: 40px;
            text-align: center;
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 10%;
            right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
        }
        
        .header h1 {
            font-size: 2.2em;
            font-weight: 800;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--text), #ffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .header h1 i {
            color: var(--accent);
            -webkit-text-fill-color: initial;
            filter: drop-shadow(0 0 10px rgba(255, 107, 53, 0.3));
        }
        
        .header .tracking-number {
            font-size: 1.1em;
            color: var(--muted);
            font-family: monospace;
            letter-spacing: 2px;
            margin-bottom: 15px;
            display: inline-block;
            background: rgba(255, 255, 255, 0.03);
            padding: 4px 14px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 24px;
            border-radius: 100px;
            font-weight: 700;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: rgba(255,107,53,0.12);
            color: var(--accent);
            border: 1px solid rgba(255,107,53,0.25);
            box-shadow: 0 4px 20px rgba(255,107,53,0.1);
        }
        
        .content {
            padding: 40px;
        }
        
        .info-section {
            background: var(--bg3);
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 35px;
            border: 1px solid var(--border);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .info-section h3 {
            color: var(--text);
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 1.15em;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 12px;
        }

        .info-section h3 i {
            color: var(--accent);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            font-size: 0.95em;
            align-items: center;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: var(--muted);
            font-weight: 500;
        }
        
        .info-value {
            color: var(--text);
            font-weight: 600;
            text-align: right;
        }
        
        .timeline {
            margin-top: 40px;
        }
        
        .timeline h3 {
            color: var(--text);
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 1.15em;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 12px;
        }

        .timeline h3 i {
            color: var(--accent);
        }
        
        .timeline-item {
            position: relative;
            padding-left: 45px;
            padding-bottom: 35px;
            border-left: 2px solid rgba(255, 255, 255, 0.05);
        }
        
        .timeline-item:last-child {
            border-left: none;
            padding-bottom: 0;
        }
        
        .timeline-item.active {
            border-left-color: var(--accent);
        }
        
        .timeline-icon {
            position: absolute;
            left: -13px;
            top: 0;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--bg3);
            border: 2px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 10px;
            transition: all 300ms ease;
        }
        
        .timeline-item.active .timeline-icon {
            border-color: var(--accent);
            background: var(--accent);
            color: white;
            box-shadow: 0 0 0 6px rgba(255, 107, 53, 0.15), 0 0 15px var(--accent);
        }
        
        .timeline-item.past .timeline-icon {
            border-color: var(--success);
            background: var(--success);
            color: white;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }
        
        .timeline-content {
            background: var(--bg3);
            border: 1px solid var(--border);
            padding: 20px;
            border-radius: 12px;
            transition: all 300ms ease;
        }

        .timeline-item:hover .timeline-content {
            transform: translateX(5px);
            border-color: rgba(255, 107, 53, 0.2);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }
        
        .timeline-title {
            font-weight: 700;
            color: var(--text);
            font-size: 1.05em;
            margin-bottom: 6px;
        }
        
        .timeline-description {
            color: var(--muted);
            font-size: 0.9em;
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .timeline-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8em;
            color: var(--muted);
            border-top: 1px solid rgba(255,255,255,0.03);
            padding-top: 8px;
            margin-top: 8px;
        }

        .timeline-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .timeline-meta i {
            color: var(--accent);
        }
        
        .empty-timeline {
            text-align: center;
            padding: 50px 20px;
            background: var(--bg3);
            border-radius: 16px;
            border: 1px solid var(--border);
            color: var(--muted);
        }
        
        .empty-timeline i {
            font-size: 3.5em;
            color: var(--accent);
            opacity: 0.5;
            margin-bottom: 20px;
            display: block;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: var(--muted);
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 600px) {
            body {
                padding: 20px 10px;
            }
            .header {
                padding: 30px 20px;
            }
            .header h1 {
                font-size: 1.7em;
            }
            .content {
                padding: 20px;
            }
            .info-section {
                padding: 20px;
            }
            .timeline-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div style="width: 100%; max-width: 800px;">
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-box"></i> Rastreamento de Encomenda</h1>
                <div class="tracking-number">{{ $shipment->tracking_number }}</div>
                <div>
                    <span class="status-badge">
                        @php
                            $statusLabels = [
                                'pending' => 'Aguardando Coleta',
                                'scheduled' => 'Agendado',
                                'picked_up' => 'Coletado',
                                'in_transit' => 'Em Trânsito',
                                'delivered' => 'Entregue',
                                'returned' => 'Devolvido',
                                'cancelled' => 'Cancelado',
                            ];
                        @endphp
                        {{ $statusLabels[$shipment->status] ?? ucfirst($shipment->status) }}
                    </span>
                </div>
            </div>
            
            <div class="content">
                <div class="info-section">
                    <h3><i class="fas fa-info-circle"></i> Informações da Encomenda</h3>
                    <div class="info-row">
                        <span class="info-label">Transportadora:</span>
                        <span class="info-value" style="color: var(--accent); font-weight: 700;">
                            {{ $shipment->tenant->name ?? 'TMS LOG' }}
                        </span>
                    </div>
                    @php
                        $assignedDriver = $shipment->driver ?? ($shipment->route->driver ?? null);
                    @endphp
                    <div class="info-row">
                        <span class="info-label">Motorista Responsável:</span>
                        <span class="info-value">
                            @if($assignedDriver)
                                <i class="fas fa-user-tie" style="font-size: 0.9em; margin-right: 5px; color: var(--accent2); filter: drop-shadow(0 0 4px rgba(255, 179, 71, 0.2));"></i>
                                {{ $assignedDriver->name }}
                            @else
                                <span style="opacity: 0.5; font-style: italic;">Aguardando Escala</span>
                            @endif
                        </span>
                    </div>
                    @if($shipment->route && $shipment->route->vehicle)
                    <div class="info-row">
                        <span class="info-label">Veículo / Placa:</span>
                        <span class="info-value">
                            <i class="fas fa-truck-moving" style="font-size: 0.9em; margin-right: 5px; color: var(--accent2); filter: drop-shadow(0 0 4px rgba(255, 179, 71, 0.2));"></i>
                            {{ $shipment->route->vehicle->vehicle_model ?? 'Veículo' }} 
                            @if($shipment->route->vehicle->plate)
                                <span style="font-family: monospace; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1); margin-left: 5px;">
                                    {{ $shipment->route->vehicle->plate }}
                                </span>
                            @endif
                        </span>
                    </div>
                    @endif
                    @if($shipment->route)
                    <div class="info-row">
                        <span class="info-label">Viagem / MDF-e:</span>
                        <span class="info-value">
                            <span class="status-badge" style="font-size: 0.7em; padding: 2px 8px; font-weight: 600; text-transform: none; letter-spacing: 0; background: rgba(255, 107, 53, 0.08); border-color: rgba(255, 107, 53, 0.15);">
                                Rota #{{ $shipment->route_id }} ({{ $shipment->route->status_label }})
                            </span>
                        </span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Remetente:</span>
                        <span class="info-value">{{ $shipment->senderClient->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Destinatário:</span>
                        <span class="info-value">{{ $shipment->receiverClient->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Origem:</span>
                        <span class="info-value">{{ $shipment->pickup_city }}/{{ $shipment->pickup_state }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Destino:</span>
                        <span class="info-value">{{ $shipment->delivery_city }}/{{ $shipment->delivery_state }}</span>
                    </div>
                    @if($shipment->weight)
                    <div class="info-row">
                        <span class="info-label">Peso:</span>
                        <span class="info-value">{{ number_format($shipment->weight, 2, ',', '.') }} kg</span>
                    </div>
                    @endif
                </div>
                
                <div class="timeline">
                    <h3><i class="fas fa-history"></i> Histórico de Movimentação</h3>
                    
                    @if($timeline->count() > 0)
                        @foreach($timeline as $index => $event)
                            @php
                                $isActive = $index === 0;
                                $isPast = $event['event_type'] === 'delivered' || $index > 0;
                            @endphp
                            <div class="timeline-item {{ $isActive ? 'active' : '' }} {{ $isPast ? 'past' : '' }}">
                                <div class="timeline-icon">
                                    @if($event['event_type'] === 'delivered')
                                        <i class="fas fa-check"></i>
                                    @elseif($event['event_type'] === 'in_transit')
                                        <i class="fas fa-truck"></i>
                                    @elseif($event['event_type'] === 'collected')
                                        <i class="fas fa-box-open"></i>
                                    @elseif($event['event_type'] === 'created')
                                        <i class="fas fa-plus"></i>
                                    @else
                                        <i class="fas fa-circle"></i>
                                    @endif
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">{{ $event['event_type_label'] }}</div>
                                    @if($event['description'])
                                        <div class="timeline-description">{{ $event['description'] }}</div>
                                    @endif
                                    <div class="timeline-meta">
                                        @if($event['location'])
                                            <span><i class="fas fa-map-marker-alt"></i> {{ $event['location'] }}</span>
                                        @endif
                                        <span><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($event['occurred_at'])->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-timeline">
                            <i class="fas fa-clock"></i>
                            <p>Nenhum evento registrado ainda.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="footer">
            © {{ date('Y') }} TMS LOG. Todos os direitos reservados.
        </div>
    </div>
</body>
</html>
