<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rastreamento - <?php echo e($shipment->tracking_number); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .header .tracking-number {
            font-size: 1.2em;
            opacity: 0.9;
            font-family: monospace;
            letter-spacing: 2px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            margin-top: 15px;
            background: rgba(255,255,255,0.2);
        }
        
        .content {
            padding: 30px;
        }
        
        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .info-section h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #666;
            font-weight: 500;
        }
        
        .info-value {
            color: #333;
            font-weight: 600;
        }
        
        .timeline {
            margin-top: 30px;
        }
        
        .timeline h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.2em;
        }
        
        .timeline-item {
            position: relative;
            padding-left: 40px;
            padding-bottom: 30px;
            border-left: 2px solid #e0e0e0;
        }
        
        .timeline-item:last-child {
            border-left: none;
        }
        
        .timeline-item.active {
            border-left-color: #667eea;
        }
        
        .timeline-icon {
            position: absolute;
            left: -12px;
            top: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }
        
        .timeline-item.active .timeline-icon {
            background: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
        }
        
        .timeline-item.past .timeline-icon {
            background: #28a745;
        }
        
        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        
        .timeline-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .timeline-description {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        
        .timeline-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85em;
            color: #999;
            margin-top: 10px;
        }
        
        .empty-timeline {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-timeline i {
            font-size: 3em;
            margin-bottom: 15px;
            opacity: 0.3;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-box"></i> Rastreamento de Encomenda</h1>
            <div class="tracking-number"><?php echo e($shipment->tracking_number); ?></div>
            <div class="status-badge">
                <?php
                    $statusLabels = [
                        'pending' => 'Aguardando Coleta',
                        'scheduled' => 'Agendado',
                        'picked_up' => 'Coletado',
                        'in_transit' => 'Em Trânsito',
                        'delivered' => 'Entregue',
                        'returned' => 'Devolvido',
                        'cancelled' => 'Cancelado',
                    ];
                ?>
                <?php echo e($statusLabels[$shipment->status] ?? ucfirst($shipment->status)); ?>

            </div>
        </div>
        
        <div class="content">
            <div class="info-section">
                <h3><i class="fas fa-info-circle"></i> Informações da Encomenda</h3>
                <div class="info-row">
                    <span class="info-label">Remetente:</span>
                    <span class="info-value"><?php echo e($shipment->senderClient->name ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Destinatário:</span>
                    <span class="info-value"><?php echo e($shipment->receiverClient->name ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Origem:</span>
                    <span class="info-value"><?php echo e($shipment->pickup_city); ?>/<?php echo e($shipment->pickup_state); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Destino:</span>
                    <span class="info-value"><?php echo e($shipment->delivery_city); ?>/<?php echo e($shipment->delivery_state); ?></span>
                </div>
                <?php if($shipment->weight): ?>
                <div class="info-row">
                    <span class="info-label">Peso:</span>
                    <span class="info-value"><?php echo e(number_format($shipment->weight, 2, ',', '.')); ?> kg</span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="timeline">
                <h3><i class="fas fa-history"></i> Histórico de Movimentação</h3>
                
                <?php if($timeline->count() > 0): ?>
                    <?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $isActive = $index === 0;
                            $isPast = $event['event_type'] === 'delivered' || $index > 0;
                        ?>
                        <div class="timeline-item <?php echo e($isActive ? 'active' : ''); ?> <?php echo e($isPast ? 'past' : ''); ?>">
                            <div class="timeline-icon">
                                <?php if($event['event_type'] === 'delivered'): ?>
                                    <i class="fas fa-check"></i>
                                <?php elseif($event['event_type'] === 'in_transit'): ?>
                                    <i class="fas fa-truck"></i>
                                <?php elseif($event['event_type'] === 'collected'): ?>
                                    <i class="fas fa-box-open"></i>
                                <?php elseif($event['event_type'] === 'created'): ?>
                                    <i class="fas fa-plus"></i>
                                <?php else: ?>
                                    <i class="fas fa-circle"></i>
                                <?php endif; ?>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title"><?php echo e($event['event_type_label']); ?></div>
                                <?php if($event['description']): ?>
                                    <div class="timeline-description"><?php echo e($event['description']); ?></div>
                                <?php endif; ?>
                                <div class="timeline-meta">
                                    <?php if($event['location']): ?>
                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo e($event['location']); ?></span>
                                    <?php endif; ?>
                                    <span><i class="fas fa-clock"></i> <?php echo e(\Carbon\Carbon::parse($event['occurred_at'])->format('d/m/Y H:i')); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <div class="empty-timeline">
                        <i class="fas fa-clock"></i>
                        <p>Nenhum evento registrado ainda.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>











<?php /**PATH /var/www/resources/views/tracking/show.blade.php ENDPATH**/ ?>