<?php $__env->startSection('title', 'Dashboard Motorista - TMS SaaS'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .route-status-card {
        background: linear-gradient(135deg, var(--cor-acento) 0%, #ff8c5a 100%);
        color: var(--cor-principal);
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    .route-status-card h2 {
        font-size: 1.3em;
        margin-bottom: 10px;
    }

    .route-status-card p {
        opacity: 0.9;
        font-size: 0.9em;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .shipment-card {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .shipment-card-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .shipment-info h3 {
        font-size: 1.1em;
        color: var(--cor-acento);
        margin-bottom: 5px;
    }

    .shipment-info p {
        font-size: 0.9em;
        color: rgba(245, 245, 245, 0.7);
        margin: 3px 0;
    }

    .shipment-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 15px;
    }

    .btn-action {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-action.pickup {
        background-color: rgba(33, 150, 243, 0.2);
        color: #2196F3;
        border: 2px solid #2196F3;
    }

    .btn-action.delivered {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
        border: 2px solid #4caf50;
    }

    .btn-action.exception {
        background-color: rgba(244, 67, 54, 0.2);
        color: #f44336;
        border: 2px solid #f44336;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .status-badge.pending {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .status-badge.picked_up {
        background-color: rgba(33, 150, 243, 0.2);
        color: #2196F3;
    }

    .status-badge.in_transit {
        background-color: rgba(156, 39, 176, 0.2);
        color: #9c27b0;
    }

    .status-badge.delivered {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: rgba(245, 245, 245, 0.7);
    }

    .empty-state i {
        font-size: 4em;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 25px;
        max-width: 500px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-header h3 {
        color: var(--cor-acento);
        font-size: 1.3em;
    }

    .close-modal {
        background: none;
        border: none;
        color: var(--cor-texto-claro);
        font-size: 1.5em;
        cursor: pointer;
    }

    .photo-preview {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .file-input-wrapper {
        position: relative;
        margin-bottom: 15px;
    }

    .file-input-wrapper input[type="file"] {
        display: none;
    }

    .file-input-label {
        display: block;
        padding: 15px;
        background-color: var(--cor-principal);
        border: 2px dashed rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-input-label:hover {
        border-color: var(--cor-acento);
        background-color: rgba(255, 107, 53, 0.1);
    }

    /* Wallet Card Styles */
    .wallet-card {
        background: linear-gradient(135deg, #1a3d33 0%, #245a49 100%);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .wallet-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .wallet-header h2 {
        font-size: 1.2em;
        color: var(--cor-texto-claro);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .wallet-balance {
        text-align: center;
        margin-bottom: 20px;
    }

    .wallet-balance-label {
        font-size: 0.9em;
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 5px;
    }

    .wallet-balance-value {
        font-size: 2em;
        font-weight: 700;
        color: var(--cor-acento);
    }

    .wallet-summary {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 20px;
    }

    .wallet-summary-item {
        background-color: rgba(255, 255, 255, 0.05);
        padding: 15px;
        border-radius: 10px;
    }

    .wallet-summary-label {
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 5px;
    }

    .wallet-summary-value {
        font-size: 1.3em;
        font-weight: 600;
        color: var(--cor-texto-claro);
    }

    .wallet-summary-value.received {
        color: #4caf50;
    }

    .wallet-summary-value.spent {
        color: #f44336;
    }

    .wallet-transactions {
        margin-top: 20px;
    }

    .wallet-transactions h3 {
        font-size: 1em;
        color: var(--cor-texto-claro);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .transaction-item {
        background-color: rgba(255, 255, 255, 0.05);
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .transaction-info {
        flex: 1;
    }

    .transaction-route-name {
        font-size: 0.9em;
        font-weight: 600;
        color: var(--cor-texto-claro);
        margin-bottom: 3px;
    }

    .transaction-date {
        font-size: 0.75em;
        color: rgba(245, 245, 245, 0.6);
    }

    .transaction-amounts {
        text-align: right;
    }

    .transaction-received {
        font-size: 0.85em;
        color: #4caf50;
        margin-bottom: 2px;
    }

    .transaction-spent {
        font-size: 0.85em;
        color: #f44336;
        margin-bottom: 2px;
    }

    .transaction-net {
        font-size: 0.9em;
        font-weight: 600;
        color: var(--cor-acento);
        margin-top: 5px;
    }

    .empty-transactions {
        text-align: center;
        padding: 20px;
        color: rgba(245, 245, 245, 0.5);
        font-size: 0.9em;
    }

    .wallet-period-info {
        font-size: 0.8em;
        color: rgba(245, 245, 245, 0.6);
        text-align: center;
        margin-top: 10px;
        padding: 8px;
        background-color: rgba(255, 255, 255, 0.03);
        border-radius: 8px;
    }

    /* Map Container Styles */
    .route-map-container {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .route-map-container h3 {
        color: var(--cor-acento);
        margin-bottom: 15px;
        font-size: 1.2em;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #route-map {
        width: 100%;
        height: 400px;
        border-radius: 10px;
        overflow: hidden;
    }

    .address-info {
        margin-top: 10px;
        padding: 12px;
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        font-size: 0.9em;
        line-height: 1.6;
    }

    .address-info strong {
        color: var(--cor-acento);
        display: block;
        margin-bottom: 5px;
    }

    .address-line {
        color: rgba(245, 245, 245, 0.9);
        margin: 3px 0;
    }

    .address-line i {
        color: var(--cor-acento);
        margin-right: 8px;
        width: 20px;
    }

    /* Route Options Styles */
    /* Route options styles removed - map no longer displayed */
        gap: 10px;
        margin-bottom: 15px;
        flex-wrap: wrap;
        align-items: center;
    }

    .route-option-btn {
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 107, 53, 0.5);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        cursor: pointer;
        font-size: 0.9em;
        transition: all 0.3s ease;
    }

    .route-option-btn:hover {
        background: rgba(255, 107, 53, 0.2);
        border-color: var(--cor-acento);
    }

    .route-option-btn.active {
        background: var(--cor-acento);
        border-color: var(--cor-acento);
        color: var(--cor-principal);
    }

    /* History Trail Styles */
    .history-controls {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        align-items: center;
    }

    .history-toggle {
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        cursor: pointer;
        font-size: 0.9em;
    }

    .history-toggle.active {
        background: rgba(33, 150, 243, 0.3);
        border-color: #2196F3;
        color: #2196F3;
    }

    /* Route Deviation Alert Styles */
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .route-deviation-alert {
        animation: slideInRight 0.3s ease-out;
    }

    /* Notification Styles */
    .proximity-notification {
        position: fixed;
        top: 80px;
        right: 20px;
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        z-index: 2000;
        max-width: 300px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .proximity-notification h4 {
        margin: 0 0 8px 0;
        font-size: 1.1em;
    }

    .proximity-notification p {
        margin: 5px 0;
        font-size: 0.9em;
        opacity: 0.9;
    }

    .close-notification {
        position: absolute;
        top: 5px;
        right: 10px;
        background: none;
        border: none;
        color: white;
        font-size: 1.2em;
        cursor: pointer;
        opacity: 0.8;
    }

    .close-notification:hover {
        opacity: 1;
    }

    /* Navigation Button Styles */
    .nav-btn {
        padding: 10px 16px;
        background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        border: none;
        border-radius: 10px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9em;
        margin-top: 10px;
        width: 100%;
        justify-content: center;
    }

    .nav-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4);
    }

    .nav-btn:active {
        transform: translateY(0);
    }

    .nav-btn i {
        font-size: 1.1em;
    }

    /* Navigation App Selector */
    .nav-app-selector {
        position: relative;
        display: inline-block;
    }

    .nav-app-menu {
        position: absolute;
        bottom: 100%;
        left: 0;
        right: 0;
        background: var(--cor-secundaria);
        border-radius: 10px;
        padding: 10px;
        margin-bottom: 5px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        display: none;
        z-index: 1000;
        min-width: 200px;
    }

    .nav-app-menu.show {
        display: block;
    }

    .nav-app-option {
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--cor-texto-claro);
        transition: background 0.2s ease;
        margin-bottom: 5px;
    }

    .nav-app-option:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .nav-app-option:last-child {
        margin-bottom: 0;
    }

    .nav-app-option i {
        width: 20px;
        text-align: center;
    }

    .nav-app-option.active {
        background: rgba(33, 150, 243, 0.2);
        color: #2196F3;
    }

    /* Navigation settings */
    .nav-settings {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.7);
    }

    .nav-settings-toggle {
        background: none;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 5px;
        padding: 5px 10px;
        color: var(--cor-texto-claro);
        cursor: pointer;
        font-size: 0.9em;
    }

    .nav-settings-toggle:hover {
        background: rgba(255, 255, 255, 0.1);
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<script>
    // Define global functions IMMEDIATELY so they're available when HTML is rendered
    // These functions must be defined before the HTML buttons that use them
    (function() {
        'use strict';
        
        // Helper functions for navigation (defined early)
        window.detectDevice = function() {
            const ua = navigator.userAgent || navigator.vendor || window.opera;
            if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) {
                return 'ios';
            }
            if (/android/i.test(ua)) {
                return 'android';
            }
            return 'desktop';
        };
        
        window.getNavigationUrl = function(latitude, longitude, address, app = null) {
            const appToUse = app || (window.preferredNavApp || 'google');
            const device = window.detectDevice();
            const encodedAddress = encodeURIComponent(address || `${latitude},${longitude}`);
            
            switch (appToUse) {
                case 'waze':
                    return `https://waze.com/ul?ll=${latitude},${longitude}&navigate=yes&q=${encodedAddress}`;
                case 'apple':
                    if (device === 'ios') {
                        return `http://maps.apple.com/?daddr=${latitude},${longitude}&dirflg=d&t=m`;
                    } else {
                        return `https://maps.apple.com/?daddr=${latitude},${longitude}&dirflg=d`;
                    }
                case 'google':
                default:
                    if (device === 'android') {
                        return `google.navigation:q=${latitude},${longitude}`;
                    } else if (device === 'ios') {
                        return `comgooglemaps://?daddr=${latitude},${longitude}&directionsmode=driving`;
                    } else {
                        return `https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}&travelmode=driving`;
                    }
            }
        };
        
        // Open navigation (global scope) - defined early
        window.openNavigation = function(latitude, longitude, address) {
            if (typeof address === 'undefined') address = null;
            const url = window.getNavigationUrl(latitude, longitude, address);
            const link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            
            const device = window.detectDevice();
            if (device !== 'desktop') {
                window.location.href = url;
                setTimeout(function() {
                    const webUrl = window.getNavigationUrl(latitude, longitude, address, 'google');
                    if (webUrl !== url) {
                        window.open(webUrl, '_blank');
                    }
                }, 500);
            } else {
                link.click();
            }
        };
        
        // Switch route mode (global scope) - defined early
        // Route switching removed - map no longer displayed on driver dashboard
    })();
</script>
<?php if($activeRoute): ?>
    <!-- Route Status Card -->
    <div class="route-status-card">
        <h2><i class="fas fa-route"></i> Rota Ativa</h2>
        <p><strong><?php echo e($activeRoute->name); ?></strong></p>
        <p style="margin-top: 5px;"><?php echo e($shipments->count()); ?> entregas</p>
        <div class="action-buttons">
            <?php if($activeRoute->status === 'scheduled'): ?>
            <button class="btn-primary" onclick="startRoute(<?php echo e($activeRoute->id); ?>)">
                <i class="fas fa-play"></i> Iniciar Rota
            </button>
            <?php elseif($activeRoute->status === 'in_progress'): ?>
            <button class="btn-secondary" onclick="finishRoute(<?php echo e($activeRoute->id); ?>)">
                <i class="fas fa-check"></i> Finalizar Rota
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Location Status -->
    <?php if($driver->current_latitude && $driver->current_longitude): ?>
    <div class="driver-card">
        <div class="driver-card-header">
            <div class="driver-card-title">
                <i class="fas fa-map-marker-alt"></i> Localização Ativa
            </div>
            <span class="status-badge delivered">
                <i class="fas fa-check-circle"></i> Online
            </span>
        </div>
        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
            Última atualização: <?php echo e((isset($driver->attributes["last_location_update"]) && $driver->attributes["last_location_update"]) ? \Carbon\Carbon::parse($driver->attributes["last_location_update"])->diffForHumans() : "Nunca"); ?>

        </p>
    </div>
    <?php endif; ?>

    <!-- Route Map -->
    <?php if($activeRoute && $activeRoute->shipments->isNotEmpty()): ?>
    <div class="route-map-container">
        <h3>
            <i class="fas fa-map-marked-alt"></i>
            Mapa da Rota
        </h3>
        <div id="route-map" style="width: 100%; height: 400px; border-radius: 10px; overflow: hidden;"></div>
    </div>
    <?php endif; ?>

    <!-- Shipments List -->
    <div id="shipments">
        <h2 style="color: var(--cor-acento); margin-bottom: 15px; font-size: 1.2em;">
            <i class="fas fa-truck"></i> Entregas (<?php echo e($shipments->count()); ?>)
        </h2>
        
        <?php $__empty_1 = true; $__currentLoopData = $shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="shipment-card" data-shipment-id="<?php echo e($shipment->id); ?>">
            <div class="shipment-card-header">
                <div class="shipment-info">
                    <h3><?php echo e($shipment->tracking_number); ?></h3>
                    <p><?php echo e($shipment->title); ?></p>
                    <?php if($shipment->receiverClient): ?>
                    <p><i class="fas fa-user"></i> <?php echo e($shipment->receiverClient->name); ?></p>
                    <?php endif; ?>
                    <?php if($shipment->delivery_address || $shipment->delivery_city || $shipment->delivery_state || $shipment->delivery_zip_code): ?>
                    <div class="address-info">
                        <strong><i class="fas fa-map-marker-alt"></i> Endereço de Entrega:</strong>
                        <?php if($shipment->delivery_address): ?>
                        <div class="address-line">
                            <i class="fas fa-road"></i><?php echo e($shipment->delivery_address); ?>

                        </div>
                        <?php endif; ?>
                        <div class="address-line">
                            <i class="fas fa-city"></i>
                            <?php if($shipment->delivery_city): ?>
                                <?php echo e($shipment->delivery_city); ?>

                            <?php endif; ?>
                            <?php if($shipment->delivery_state): ?>
                                / <?php echo e($shipment->delivery_state); ?>

                            <?php endif; ?>
                            <?php if($shipment->delivery_zip_code): ?>
                                - CEP: <?php echo e($shipment->delivery_zip_code); ?>

                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <span class="status-badge <?php echo e($shipment->status); ?>">
                    <?php echo e(ucfirst(str_replace('_', ' ', $shipment->status))); ?>

                </span>
            </div>
            
            <?php if($shipment->delivery_latitude && $shipment->delivery_longitude): ?>
            <button class="nav-btn" onclick="openNavigation(<?php echo e($shipment->delivery_latitude); ?>, <?php echo e($shipment->delivery_longitude); ?>, <?php echo e(json_encode($shipment->delivery_address . ', ' . $shipment->delivery_city . '/' . $shipment->delivery_state)); ?>)">
                <i class="fas fa-directions"></i> Abrir Navegação GPS
            </button>
            <?php endif; ?>
            
            
            <?php if($shipment->deliveryProofs && $shipment->deliveryProofs->count() > 0): ?>
            <div class="proof-photos" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
                <h4 style="color: var(--cor-acento); font-size: 0.9em; margin-bottom: 10px;">
                    <i class="fas fa-camera"></i> Fotos de Comprovante
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px;">
                    <?php $__currentLoopData = $shipment->deliveryProofs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proof): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $proof->photo_urls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photoUrl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($photoUrl): ?>
                                <div style="position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; background: var(--cor-principal); border: 2px solid <?php echo e($proof->proof_type === 'pickup' ? '#FFD700' : '#4CAF50'); ?>;">
                                    <img src="<?php echo e($photoUrl); ?>" alt="Comprovante" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" onclick="openPhotoModal('<?php echo e($photoUrl); ?>', '<?php echo e($proof->proof_type === 'pickup' ? 'Coleta' : 'Entrega'); ?>', '<?php echo e($proof->delivery_time->format('d/m/Y H:i')); ?>')">
                                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); padding: 5px; font-size: 0.7em; color: white; text-align: center;">
                                        <?php echo e($proof->proof_type === 'pickup' ? 'Coleta' : 'Entrega'); ?>

                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="shipment-actions">
                <?php if($shipment->status === 'pending' || $shipment->status === 'scheduled'): ?>
                    <?php if(($shipment->shipment_type ?? 'delivery') === 'pickup'): ?>
                        <button class="btn-action pickup" onclick="updateShipmentStatus(<?php echo e($shipment->id); ?>, 'picked_up')">
                            <i class="fas fa-hand-holding"></i> Coletado
                        </button>
                    <?php else: ?>
                        <button class="btn-action delivered" onclick="updateShipmentStatus(<?php echo e($shipment->id); ?>, 'delivered')">
                            <i class="fas fa-check-circle"></i> Entregue
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if($shipment->status === 'picked_up'): ?>
                    <button class="btn-action delivered" onclick="updateShipmentStatus(<?php echo e($shipment->id); ?>, 'delivered')">
                        <i class="fas fa-check-circle"></i> Entregue
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Nenhuma entrega nesta rota</p>
        </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-route"></i>
        <h3 style="color: var(--cor-texto-claro); margin-bottom: 10px;">Nenhuma Rota Ativa</h3>
        <p>Você não tem rotas atribuídas no momento.</p>
    </div>
<?php endif; ?>

<!-- Wallet Card (always visible) -->
<div class="wallet-card">
    <div class="wallet-header">
        <h2><i class="fas fa-wallet"></i> Carteira</h2>
        <div style="display: flex; gap: 10px; align-items: center;">
            <form method="GET" action="<?php echo e(route('driver.dashboard')); ?>" id="period-filter-form" style="display: flex; gap: 5px;">
                <select name="period" id="period-select" onchange="this.form.submit()" style="padding: 8px; border-radius: 8px; background: var(--cor-principal); color: var(--cor-texto-claro); border: 1px solid rgba(255,255,255,0.2); font-size: 0.85em;">
                    <option value="all" <?php echo e(($period ?? 'all') === 'all' ? 'selected' : ''); ?>>Todo Período</option>
                    <option value="week" <?php echo e(($period ?? 'all') === 'week' ? 'selected' : ''); ?>>Esta Semana</option>
                    <option value="month" <?php echo e(($period ?? 'all') === 'month' ? 'selected' : ''); ?>>Este Mês</option>
                    <option value="year" <?php echo e(($period ?? 'all') === 'year' ? 'selected' : ''); ?>>Este Ano</option>
                </select>
            </form>
            <a href="<?php echo e(route('driver.wallet.export', ['period' => $period ?? 'all'])); ?>" class="btn-primary" style="padding: 8px 12px; font-size: 0.85em; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>
    
    <div class="wallet-balance">
        <div class="wallet-balance-label">Saldo Disponível</div>
        <div class="wallet-balance-value" style="color: <?php echo e(($currentBalance ?? 0) >= 0 ? '#4caf50' : '#f44336'); ?>;">
            R$ <?php echo e(number_format($currentBalance ?? 0, 2, ',', '.')); ?>

        </div>
    </div>

    <div class="wallet-summary">
        <div class="wallet-summary-item">
            <div class="wallet-summary-label">Total Recebido</div>
            <div class="wallet-summary-value received">R$ <?php echo e(number_format($totalReceived ?? 0, 2, ',', '.')); ?></div>
        </div>
        <div class="wallet-summary-item">
            <div class="wallet-summary-label">Gastos Comprovados</div>
            <div class="wallet-summary-value spent">R$ <?php echo e(number_format($totalSpent ?? 0, 2, ',', '.')); ?></div>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 15px;">
        <a href="<?php echo e(route('driver.wallet')); ?>" class="btn-primary" style="padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-wallet"></i> Ver Carteira Completa
        </a>
    </div>

    <?php if($recentFinancialRoutes && $recentFinancialRoutes->count() > 0): ?>
    <div class="wallet-transactions">
        <h3><i class="fas fa-history"></i> Histórico Recente</h3>
        <?php $__currentLoopData = $recentFinancialRoutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="transaction-item">
            <div class="transaction-info">
                <div class="transaction-route-name"><?php echo e($transaction['description']); ?></div>
                <div class="transaction-date"><?php echo e($transaction['date']->format('d/m/Y')); ?></div>
                <?php if(isset($transaction['expense']) && $transaction['expense']->expense_type): ?>
                <div style="font-size: 0.8em; color: rgba(245,245,245,0.6); margin-top: 3px;">
                    <i class="fas fa-tag"></i> <?php echo e($transaction['expense']->expense_type_label); ?>

                </div>
                <?php endif; ?>
            </div>
            <div class="transaction-amounts">
                <?php if($transaction['is_positive']): ?>
                <div class="transaction-received" style="color: #4caf50; font-weight: 600;">
                    + R$ <?php echo e(number_format($transaction['amount'], 2, ',', '.')); ?>

                </div>
                <?php else: ?>
                <div class="transaction-spent" style="color: #f44336; font-weight: 600;">
                    - R$ <?php echo e(number_format($transaction['amount'], 2, ',', '.')); ?>

                </div>
                <?php endif; ?>
                <div class="transaction-net" style="font-size: 0.9em; color: <?php echo e($transaction['balance'] >= 0 ? '#4caf50' : '#f44336'); ?>; margin-top: 5px;">
                    Saldo: <?php echo e($transaction['balance'] >= 0 ? '+' : ''); ?>R$ <?php echo e(number_format($transaction['balance'], 2, ',', '.')); ?>

                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php else: ?>
    <div class="empty-transactions">
        <i class="fas fa-inbox"></i> Nenhuma transação financeira registrada ainda.
    </div>
    <?php endif; ?>

    <?php if(isset($period) && $period !== 'all'): ?>
    <div class="wallet-period-info">
        <i class="fas fa-calendar"></i> 
        Período: <?php echo e($startDate ? $startDate->format('d/m/Y') : 'Início'); ?> até <?php echo e($endDate->format('d/m/Y')); ?>

    </div>
    <?php endif; ?>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Atualizar Status</h3>
            <button class="close-modal" onclick="closeModal('statusModal')">&times;</button>
        </div>
        <form id="statusForm" onsubmit="submitStatusUpdate(event)">
            <input type="hidden" id="modalShipmentId" name="shipment_id">
            <input type="hidden" id="modalStatus" name="status">
            
            <div class="file-input-wrapper">
                <label for="proofPhoto" class="file-input-label">
                    <i class="fas fa-camera"></i><br>
                    <span>Adicionar Foto de Comprovante</span>
                </label>
                <input type="file" id="proofPhoto" name="photo" accept="image/*" capture="environment" onchange="previewPhoto(this)">
                <img id="photoPreview" class="photo-preview" style="display: none;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Observações (opcional)</label>
                <textarea name="notes" rows="3" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro); resize: none;"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-primary" style="flex: 1;">
                    <i class="fas fa-check"></i> Confirmar
                </button>
                <button type="button" class="btn-secondary" onclick="closeModal('statusModal')" style="flex: 1;">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    let currentShipmentId = null;
    let currentStatus = null;
    let currentRouteMode = 'fastest'; // Default route mode
    let historyPolyline = null; // Polyline for location history path
    let locationUpdateInterval = null; // Interval for polling location updates
    let proximityCheckInterval = null; // Interval for proximity checking
    let notifiedShipments = new Set(); // Track shipments that have been notified for proximity
    let preferredNavApp = 'google'; // Preferred navigation app (google, waze, apple)
    let showHistory = false; // Whether to show route history

    // Global variables for Mapbox - EXACTLY like routes/show.blade.php
    <?php
        $driverLat = $driver->current_latitude ?? null;
        $driverLng = $driver->current_longitude ?? null;
        $routeOriginLat = ($activeRoute && $activeRoute->start_latitude) ? $activeRoute->start_latitude : null;
        $routeOriginLng = ($activeRoute && $activeRoute->start_longitude) ? $activeRoute->start_longitude : null;
        $routeOriginName = ($activeRoute && $activeRoute->branch) ? $activeRoute->branch->name : 'Ponto de Partida';
        $routeId = ($activeRoute && $activeRoute->id) ? $activeRoute->id : null;
        $tenantId = auth()->user()->tenant_id ?? null;
        $driverId = $driver->id ?? null;
    ?>
    window.driverCurrentLat = <?php echo json_encode($driverLat, 15, 512) ?>;
    window.driverCurrentLng = <?php echo json_encode($driverLng, 15, 512) ?>;
    window.routeOriginLat = <?php echo json_encode($routeOriginLat, 15, 512) ?>;
    window.routeOriginLng = <?php echo json_encode($routeOriginLng, 15, 512) ?>;
    window.routeOriginName = <?php echo json_encode($routeOriginName, 15, 512) ?>;
    window.routeId = <?php echo json_encode($routeId, 15, 512) ?>;
    window.tenantId = <?php echo json_encode($tenantId, 15, 512) ?>;
    window.driverId = <?php echo json_encode($driverId, 15, 512) ?>;
    
    // Format shipments EXACTLY like routes/show.blade.php
    <?php
        $shipmentsArray = $shipments->map(function($shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'title' => $shipment->title,
                'pickup_lat' => $shipment->pickup_latitude,
                'pickup_lng' => $shipment->pickup_longitude,
                'delivery_lat' => $shipment->delivery_latitude,
                'delivery_lng' => $shipment->delivery_longitude,
                'status' => $shipment->status,
            ];
        })->values();
    ?>
    window.routeShipments = <?php echo json_encode($shipmentsArray, 15, 512) ?>;
    
    // Debug: Log data availability
    console.log('Driver Dashboard - Route Data:', {
        hasActiveRoute: <?php echo json_encode($activeRoute ? true : false, 15, 512) ?>,
        routeId: window.routeId,
        routeOriginLat: window.routeOriginLat,
        routeOriginLng: window.routeOriginLng,
        driverLat: window.driverCurrentLat,
        driverLng: window.driverCurrentLng,
        shipmentsCount: window.routeShipments ? window.routeShipments.length : 0,
        shipments: window.routeShipments
    });
    
    // Also keep deliveryLocations for backward compatibility
    <?php
        $deliveryLocationsArray = $shipments->filter(function($s) {
            return $s->delivery_latitude && $s->delivery_longitude;
        })->map(function($shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'title' => $shipment->title,
                'address' => ($shipment->delivery_address ?? '') . ', ' . ($shipment->delivery_city ?? '') . '/' . ($shipment->delivery_state ?? ''),
                'lat' => floatval($shipment->delivery_latitude),
                'lng' => floatval($shipment->delivery_longitude),
                'status' => $shipment->status,
            ];
        })->values();
    ?>
    window.deliveryLocations = <?php echo json_encode($deliveryLocationsArray, 15, 512) ?>;

    // Helper function to validate coordinates (must be global to be used in watchPosition)
    function isValidCoordinate(value) {
        return value !== null && value !== undefined && !isNaN(value) && isFinite(value);
    }

    function updateShipmentStatus(shipmentId, status) {
        currentShipmentId = shipmentId;
        currentStatus = status;
        document.getElementById('modalShipmentId').value = shipmentId;
        document.getElementById('modalStatus').value = status;
        document.getElementById('statusModal').classList.add('active');
    }

    function showExceptionModal(shipmentId) {
        currentShipmentId = shipmentId;
        currentStatus = 'exception';
        document.getElementById('modalShipmentId').value = shipmentId;
        document.getElementById('modalStatus').value = 'exception';
        document.getElementById('statusModal').classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.getElementById('photoPreview').style.display = 'none';
        document.getElementById('proofPhoto').value = '';
        document.getElementById('statusForm').reset();
    }

    function openPhotoModal(photoUrl, type, date) {
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; display: flex; align-items: center; justify-content: center;';
        modal.innerHTML = `
            <div style="position: relative; max-width: 90%; max-height: 90%;">
                <button onclick="this.parentElement.parentElement.remove()" style="position: absolute; top: -40px; right: 0; background: rgba(255,255,255,0.2); color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-size: 1.5em;">&times;</button>
                <img src="${photoUrl}" alt="${type}" style="max-width: 100%; max-height: 90vh; border-radius: 10px;">
                <div style="color: white; text-align: center; margin-top: 10px;">
                    <p style="margin: 5px 0;">${type} - ${date}</p>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }

    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('photoPreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function submitStatusUpdate(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const shipmentId = formData.get('shipment_id');
        const status = formData.get('status');
        
        // Get current location if available
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                formData.append('latitude', position.coords.latitude);
                formData.append('longitude', position.coords.longitude);
                formData.append('accuracy', position.coords.accuracy);
                
                submitForm(formData, shipmentId);
            }, function(error) {
                console.warn('Geolocation not available, submitting without location');
                submitForm(formData, shipmentId);
            });
        } else {
            submitForm(formData, shipmentId);
        }
    }
    
    function submitForm(formData, shipmentId) {
        fetch(`/driver/shipments/${shipmentId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            },
            body: formData
        })
        .then(async response => {
            // Check if response is JSON before trying to parse
            const contentType = response.headers.get('content-type') || '';
            const isJson = contentType.includes('application/json');
            
            if (!response.ok) {
                if (isJson) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || errorData.message || 'Erro ao atualizar status');
                } else {
                    // Server returned HTML error page, don't try to parse as JSON
                    throw new Error('Erro ao atualizar status. Tente novamente.');
                }
            }
            
            if (!isJson) {
                throw new Error('Resposta inválida do servidor. Tente novamente.');
            }
            
            return response.json();
        })
        .then(data => {
            if (data.message) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Erro ao atualizar status: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Erro ao atualizar status. Tente novamente.');
        });
    }

    function startRoute(routeId) {
        if (confirm('Deseja iniciar esta rota?')) {
            fetch(`/driver/routes/${routeId}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Erro ao iniciar rota');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.message) {
                    window.location.reload();
                } else {
                    alert('Erro ao iniciar rota: ' + (data.error || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao iniciar rota: ' + error.message);
            });
        }
    }

    function finishRoute(routeId) {
        if (confirm('Deseja finalizar esta rota? Todas as entregas devem estar concluídas.')) {
            fetch(`/driver/routes/${routeId}/finish`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Erro ao finalizar rota');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.message) {
                    window.location.reload();
                } else {
                    alert('Erro ao finalizar rota: ' + (data.error || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao finalizar rota: ' + error.message);
            });
        }
    }

    // Auto-update location from browser geolocation
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(function(position) {
            const routeId = window.routeId || null;
            
            // Update location on server using web endpoint (session auth)
            fetch('/driver/location/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                },
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    route_id: routeId,
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Location updated on server:', data);
            })
            .catch(err => {
                console.error('Error updating location on server:', err);
            });
            
            // Update map marker immediately for better UX
            if (window.routeMap) {
                const lat = parseFloat(position.coords.latitude);
                const lng = parseFloat(position.coords.longitude);
                
                // Validate coordinates before using
                if (!isValidCoordinate(lat) || !isValidCoordinate(lng)) {
                    console.warn('Invalid geolocation coordinates:', position.coords);
                    return;
                }
                
                const newPosition = { lat: lat, lng: lng };
                
                // Update global driver location variables
                window.driverCurrentLat = lat;
                window.driverCurrentLng = lng;
                
                // Update marker - Mapbox only
                if (window.driverMarker && window.routeMap) {
                    if (typeof window.routeMap.updateMarker === 'function') {
                        // Mapbox - use updateMarker method
                        window.routeMap.updateMarker(window.driverMarker, newPosition);
                    } else if (typeof window.driverMarker.setPosition === 'function') {
                        // Fallback for other map types
                        window.driverMarker.setPosition(newPosition);
                    }
                } else if (window.routeMap && typeof window.routeMap.addMarker === 'function') {
                    // Create Mapbox marker if it doesn't exist
                    window.driverMarker = window.routeMap.addMarker(newPosition, {
                        title: 'Sua Localização',
                        color: '#2196F3',
                        size: 28
                    });
                }
            }
        }, function(error) {
            console.error('Geolocation error:', error);
            if (error.code === 1) {
                console.warn('Geolocation permission denied');
            } else if (error.code === 2) {
                console.warn('Geolocation position unavailable');
            } else if (error.code === 3) {
                console.warn('Geolocation timeout');
            }
        }, {
            enableHighAccuracy: true,
            timeout: 10000, // Increased timeout
            maximumAge: 0
        });
    }

    // Map functionality removed - no longer displaying map on driver dashboard

    // Detect device type

    // Detect device type
    function detectDevice() {
        const ua = navigator.userAgent || navigator.vendor || window.opera;
        
        if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) {
            return 'ios';
        }
        
        if (/android/i.test(ua)) {
            return 'android';
        }
        
        return 'desktop';
    }

    // Get navigation URL based on app preference and device
    function getNavigationUrl(latitude, longitude, address, app = null) {
        const appToUse = app || preferredNavApp;
        const device = detectDevice();
        
        // Format address for URL encoding
        const encodedAddress = encodeURIComponent(address || `${latitude},${longitude}`);
        
        switch (appToUse) {
            case 'waze':
                return `https://waze.com/ul?ll=${latitude},${longitude}&navigate=yes&q=${encodedAddress}`;
            
            case 'apple':
                if (device === 'ios') {
                    // Apple Maps URL scheme for iOS
                    return `http://maps.apple.com/?daddr=${latitude},${longitude}&dirflg=d&t=m`;
                } else {
                    // Fallback to web Apple Maps
                    return `https://maps.apple.com/?daddr=${latitude},${longitude}&dirflg=d`;
                }
            
            case 'google':
            default:
                if (device === 'android') {
                    // Try to open Google Maps app directly
                    return `google.navigation:q=${latitude},${longitude}`;
                } else if (device === 'ios') {
                    // Use Google Maps URL scheme for iOS
                    return `comgooglemaps://?daddr=${latitude},${longitude}&directionsmode=driving`;
                } else {
                    // Web fallback
                    return `https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}&travelmode=driving`;
                }
        }
    }

    // openNavigation is already defined at the top of the file
    // This is kept for backward compatibility but the main definition is at the top

    // Set preferred navigation app
    function setNavApp(app) {
        preferredNavApp = app;
        localStorage.setItem('preferredNavApp', app);
        
        // Update UI
        const labels = {
            'google': 'Google Maps',
            'waze': 'Waze',
            'apple': 'Apple Maps'
        };
        
        const labelEl = document.getElementById('nav-app-label');
        if (labelEl) {
            labelEl.textContent = labels[app] || 'Google Maps';
        }
        
        // Update active option
        document.querySelectorAll('.nav-app-option').forEach(opt => opt.classList.remove('active'));
        const clickedOption = event.target.closest('.nav-app-option');
        if (clickedOption) {
            clickedOption.classList.add('active');
        }
        
        // Close menu
        toggleNavAppMenu();
    }

    // Toggle navigation app menu
    function toggleNavAppMenu() {
        const menu = document.getElementById('nav-app-menu');
        if (menu) {
            menu.classList.toggle('show');
        }
    }

    // Close navigation app menu when clicking outside
    document.addEventListener('click', function(event) {
        const selector = document.querySelector('.nav-app-selector');
        const menu = document.getElementById('nav-app-menu');
        
        if (selector && menu && !selector.contains(event.target)) {
            menu.classList.remove('show');
        }
    });

    // Cache key generator
    function getCacheKey(mode, origin, destinations) {
        const destStr = destinations.map(d => `${d.lat},${d.lng}`).join('|');
        return `${mode}_${origin.lat},${origin.lng}_${destStr}`;
    }

    // Route map functions removed - map no longer displayed on driver dashboard

    // Poll driver location in real-time
    function startLocationPolling() {
        console.log('Starting location polling...');
        
        // Clear any existing interval
        if (locationUpdateInterval) {
            clearInterval(locationUpdateInterval);
        }

        // Poll every 5 seconds (more frequent for real-time tracking)
        locationUpdateInterval = setInterval(function() {
            updateDriverLocation();
            // Check for route deviation every 30 seconds
            if (!window.lastDeviationCheck || (Date.now() - window.lastDeviationCheck) > 30000) {
                checkRouteDeviation();
                window.lastDeviationCheck = Date.now();
            }
        }, 5000);

        // Also update immediately
        updateDriverLocation();
    }

    // Stop location polling
    function stopLocationPolling() {
        if (locationUpdateInterval) {
            clearInterval(locationUpdateInterval);
            locationUpdateInterval = null;
        }
    }

    // Check for route deviation and show alert
    let lastDeviationAlert = null;
    function checkRouteDeviation() {
        const routeId = window.routeId || null;
        if (!routeId) return;

        fetch(`/monitoring/routes/${routeId}/deviation-costs`)
            .then(response => response.json())
            .then(data => {
                if (data.has_deviation && data.off_route_distance_km > 0.5) {
                    // Only show alert if we haven't shown one in the last 2 minutes
                    const now = Date.now();
                    if (!lastDeviationAlert || (now - lastDeviationAlert) > 120000) {
                        showRouteDeviationAlert(data);
                        lastDeviationAlert = now;
                    }
                }
            })
            .catch(error => {
                // Silently fail - don't spam console
            });
    }

    // Show route deviation alert
    function showRouteDeviationAlert(data) {
        // Remove existing alert
        const existing = document.querySelector('.route-deviation-alert');
        if (existing) existing.remove();

        const alert = document.createElement('div');
        alert.className = 'route-deviation-alert';
        alert.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #FF0000 0%, #CC0000 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.5);
            z-index: 10000;
            max-width: 400px;
            animation: slideInRight 0.3s ease-out;
        `;
        alert.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                <h4 style="margin: 0; font-size: 1.2em;">
                    <i class="fas fa-exclamation-triangle"></i> Desvio de Rota Detectado!
                </h4>
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="background: none; border: none; color: white; font-size: 1.5em; cursor: pointer; padding: 0; margin-left: 10px;">
                    &times;
                </button>
            </div>
            <p style="margin: 5px 0; font-size: 0.95em;">
                Você está <strong>${data.off_route_distance_km.toFixed(2)} km</strong> fora da rota planejada.
            </p>
            <p style="margin: 5px 0; font-size: 0.9em; opacity: 0.9;">
                Custo extra estimado: <strong>R$ ${data.total_extra_cost.toFixed(2)}</strong>
            </p>
            <p style="margin: 10px 0 0 0; font-size: 0.85em; opacity: 0.8;">
                <i class="fas fa-info-circle"></i> Retorne à rota planejada para evitar custos extras.
            </p>
        `;

        document.body.appendChild(alert);

        // Request browser notification permission and show
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Desvio de Rota Detectado', {
                body: `Você está ${data.off_route_distance_km.toFixed(2)} km fora da rota. Retorne à rota planejada.`,
                icon: '/favicon.ico',
                tag: 'route-deviation',
                requireInteraction: false,
            });
        } else if ('Notification' in window && Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    new Notification('Desvio de Rota Detectado', {
                        body: `Você está ${data.off_route_distance_km.toFixed(2)} km fora da rota. Retorne à rota planejada.`,
                        icon: '/favicon.ico',
                        tag: 'route-deviation',
                    });
                }
            });
        }

        // Vibrate if supported
        if (navigator.vibrate) {
            navigator.vibrate([200, 100, 200, 100, 200]);
        }

        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, 10000);
    }

    // Update driver location from server - simplified since map is no longer displayed
    function updateDriverLocation() {
        fetch('/driver/location/current', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json',
            }
        })
            .then(async response => {
                // Check if response is JSON before trying to parse
                const contentType = response.headers.get('content-type') || '';
                const isJson = contentType.includes('application/json');
                
                if (!response.ok) {
                    if (isJson) {
                        const errorData = await response.json();
                        throw new Error(errorData.error || errorData.message || `HTTP error! status: ${response.status}`);
                    } else {
                        const text = await response.text();
                        throw new Error(`Server error (${response.status}): Received HTML instead of JSON`);
                    }
                }
                
                if (!isJson) {
                    throw new Error('Invalid response format: Expected JSON but received ' + contentType);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Location data received:', data);
                
                if (data.driver && data.driver.current_location) {
                    const lat = parseFloat(data.driver.current_location.lat);
                    const lng = parseFloat(data.driver.current_location.lng);
                    
                    // Validate coordinates before using
                    if (!isValidCoordinate(lat) || !isValidCoordinate(lng)) {
                        console.warn('Invalid coordinates received:', data.driver.current_location);
                        return;
                    }
                    
                    // Update global driver location variables (used for proximity checking)
                    window.driverCurrentLat = lat;
                    window.driverCurrentLng = lng;
                    
                    console.log('Driver location updated:', { lat, lng });
                } else {
                    console.warn('No location data in response:', data);
                }
            })
            .catch(error => {
                console.error('Error fetching driver location:', error);
                // Don't show alert for location errors, just log them
            });
    }

    // Calculate distance using Haversine formula (returns km)
    function calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Earth radius in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    // Check proximity to delivery points
    function checkProximity() {
        const driverLat = <?php echo json_encode($driver->current_latitude ?? null, 15, 512) ?>;
        const driverLng = <?php echo json_encode($driver->current_longitude ?? null, 15, 512) ?>;
        <?php
            $proximityLocationsArray = $shipments->filter(function($s) {
                return $s->delivery_latitude && $s->delivery_longitude && !in_array($s->status, ['delivered', 'exception', 'cancelled']);
            })->map(function($shipment) {
                return [
                    'id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'title' => $shipment->title,
                    'lat' => floatval($shipment->delivery_latitude),
                    'lng' => floatval($shipment->delivery_longitude),
                ];
            })->values();
        ?>
        const deliveryLocations = <?php echo json_encode($proximityLocationsArray, 15, 512) ?>;

        if (!driverLat || !driverLng || !deliveryLocations || deliveryLocations.length === 0) return;

        deliveryLocations.forEach(shipment => {
            if (notifiedShipments.has(shipment.id)) return;

            const distance = calculateDistance(
                driverLat, driverLng,
                shipment.lat, shipment.lng
            );

            // Notify when within 500 meters
            if (distance <= 0.5) {
                showProximityNotification(shipment, distance);
                notifiedShipments.add(shipment.id);
            }
        });
    }

    // Show proximity notification
    function showProximityNotification(shipment, distance) {
        // Remove existing notification
        const existing = document.querySelector('.proximity-notification');
        if (existing) existing.remove();

        const notification = document.createElement('div');
        notification.className = 'proximity-notification';
        const navLat = parseFloat(shipment.lat) || 0;
        const navLng = parseFloat(shipment.lng) || 0;
        notification.innerHTML = `
            <button class="close-notification" onclick="this.parentElement.remove()">&times;</button>
            <h4><i class="fas fa-map-marker-alt"></i> Próximo do Destino!</h4>
            <p><strong>${shipment.tracking_number}</strong></p>
            <p>${shipment.title}</p>
            <p>Distância: ${(distance * 1000).toFixed(0)} metros</p>
            <button onclick="window.openNavigation(${navLat}, ${navLng}); this.parentElement.remove();" 
                    style="margin-top: 10px; padding: 8px 16px; background: white; color: #4CAF50; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-weight: 600;">
                <i class="fas fa-directions"></i> Abrir Navegação
            </button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 10000);

        // Vibrate if supported
        if (navigator.vibrate) {
            navigator.vibrate([200, 100, 200]);
        }
    }

    // Start proximity checking
    function startProximityChecking() {
        if (proximityCheckInterval) return;
        
        // Check every 30 seconds
        proximityCheckInterval = setInterval(checkProximity, 30000);
        // Also check immediately
        checkProximity();
    }

    // Stop proximity checking
    function stopProximityChecking() {
        if (proximityCheckInterval) {
            clearInterval(proximityCheckInterval);
            proximityCheckInterval = null;
        }
    }

    // updateRouteSummary function removed - map no longer displayed on driver dashboard

    // loadRoute function removed - map no longer displayed on driver dashboard

    // Initialize navigation app preference on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-detect and set best navigation app based on device
        const device = detectDevice();
        if (device === 'ios' && !localStorage.getItem('preferredNavApp')) {
            preferredNavApp = 'apple';
            localStorage.setItem('preferredNavApp', 'apple');
            const labelEl = document.getElementById('nav-app-label');
            if (labelEl) labelEl.textContent = 'Apple Maps';
        } else if (!localStorage.getItem('preferredNavApp')) {
            preferredNavApp = 'google';
            localStorage.setItem('preferredNavApp', 'google');
        } else {
            preferredNavApp = localStorage.getItem('preferredNavApp');
        }
        
        // Update label
        const labels = {
            'google': 'Google Maps',
            'waze': 'Waze',
            'apple': 'Apple Maps'
        };
        const labelEl = document.getElementById('nav-app-label');
        if (labelEl) {
            labelEl.textContent = labels[preferredNavApp] || 'Google Maps';
        }
        
        // Update active option in menu - removed since navigation menu is handled by event listeners
    });

    // Initialize route map with Mapbox (similar to routes/show.blade.php)
    let routeMap;
    
    async function initRouteMapWithMapbox() {
        // Prevent multiple initializations
        if (window.routeMapInitialized) {
            console.log('Map already initialized, skipping...');
            return;
        }
        
        const mapContainer = document.getElementById('route-map');
        if (!mapContainer || typeof MapboxHelper === 'undefined') {
            console.error('MapboxHelper not available');
            return;
        }

        let center = [-46.6333, -23.5505]; // São Paulo default [lng, lat]
        if (window.routeOriginLat && window.routeOriginLng) {
            center = [parseFloat(window.routeOriginLng), parseFloat(window.routeOriginLat)];
        } else if (window.driverCurrentLat && window.driverCurrentLng) {
            center = [parseFloat(window.driverCurrentLng), parseFloat(window.driverCurrentLat)];
        }

        const authToken = document.querySelector('meta[name="api-token"]')?.content || localStorage.getItem('auth_token');
        
        routeMap = new MapboxHelper('route-map', {
            center: center,
            zoom: 12,
            accessToken: window.mapboxAccessToken,
            apiBaseUrl: '/api/maps',
            authToken: authToken,
            onLoad: async (map) => {
                window.routeMapInitialized = true; // Mark as initialized
                window.routeMap = routeMap; // Make it globally available
                await addRouteMarkersAndPolyline();
            }
        });

        async function addRouteMarkersAndPolyline() {
            console.log('Adding markers and route...', {
                routeOriginLat: window.routeOriginLat,
                routeOriginLng: window.routeOriginLng,
                driverLat: window.driverCurrentLat,
                driverLng: window.driverCurrentLng,
                shipmentsCount: window.routeShipments?.length || 0,
                shipments: window.routeShipments
            });
            
            // Origin marker (depot/branch)
            if (window.routeOriginLat && window.routeOriginLng) {
                routeMap.addMarker({
                    lat: parseFloat(window.routeOriginLat),
                    lng: parseFloat(window.routeOriginLng)
                }, {
                    title: window.routeOriginName || 'Ponto de Partida',
                    color: '#FF6B35',
                    size: 32
                });
            }

            // Driver's current location marker
            if (window.driverCurrentLat && window.driverCurrentLng) {
                window.driverMarker = routeMap.addMarker({
                    lat: parseFloat(window.driverCurrentLat),
                    lng: parseFloat(window.driverCurrentLng)
                }, {
                    title: 'Sua Localização',
                    color: '#2196F3',
                    size: 28
                });
            }

            // Shipment markers
            if (!window.routeShipments || window.routeShipments.length === 0) {
                console.warn('No shipments found for route');
                // Fit bounds to show at least origin and driver location
                const positions = [];
                if (window.routeOriginLat && window.routeOriginLng) {
                    positions.push({ lat: parseFloat(window.routeOriginLat), lng: parseFloat(window.routeOriginLng) });
                }
                if (window.driverCurrentLat && window.driverCurrentLng) {
                    positions.push({ lat: parseFloat(window.driverCurrentLat), lng: parseFloat(window.driverCurrentLng) });
                }
                if (positions.length > 0) routeMap.fitBounds(positions);
                return;
            }
            
            window.routeShipments.forEach(shipment => {
                if (shipment.pickup_lat && shipment.pickup_lng) {
                    routeMap.addMarker({
                        lat: parseFloat(shipment.pickup_lat),
                        lng: parseFloat(shipment.pickup_lng)
                    }, {
                        title: `Coleta: ${shipment.tracking_number}`,
                        color: '#2196F3',
                        size: 24
                    });
                }
                
                if (shipment.delivery_lat && shipment.delivery_lng) {
                    routeMap.addMarker({
                        lat: parseFloat(shipment.delivery_lat),
                        lng: parseFloat(shipment.delivery_lng)
                    }, {
                        title: `Entrega: ${shipment.tracking_number}`,
                        color: '#4CAF50',
                        size: 28
                    });
                }
            });

            // Draw route
            if (window.routeOriginLat && window.routeOriginLng && window.routeShipments.length > 0) {
                const origin = {
                    lat: parseFloat(window.routeOriginLat),
                    lng: parseFloat(window.routeOriginLng)
                };
                
                // Filter shipments with valid delivery coordinates
                const deliveries = window.routeShipments
                    .filter(s => {
                        const hasCoords = s.delivery_lat && s.delivery_lng && 
                                         !isNaN(parseFloat(s.delivery_lat)) && 
                                         !isNaN(parseFloat(s.delivery_lng));
                        if (!hasCoords) {
                            console.warn('Shipment without valid delivery coordinates:', {
                                id: s.id,
                                tracking_number: s.tracking_number,
                                delivery_lat: s.delivery_lat,
                                delivery_lng: s.delivery_lng
                            });
                        }
                        return hasCoords;
                    })
                    .map(s => ({ 
                        lat: parseFloat(s.delivery_lat), 
                        lng: parseFloat(s.delivery_lng),
                        tracking_number: s.tracking_number,
                        id: s.id
                    }));
                
                console.log('Route drawing data:', {
                    origin,
                    totalShipments: window.routeShipments.length,
                    deliveriesCount: deliveries.length,
                    deliveries
                });
                
                if (deliveries.length > 0) {
                    // For routes with multiple deliveries, create a sequential route
                    // Origin -> Delivery 1 -> Delivery 2 -> ... -> Last Delivery -> Return to Origin
                    const waypoints = deliveries; // All deliveries as waypoints
                    const returnDestination = origin; // Return to origin
                    
                    console.log('Drawing route with return to base:', { 
                        origin, 
                        destination: returnDestination,
                        waypointsCount: waypoints.length
                    });
                    
                    try {
                        await routeMap.drawRoute(origin, returnDestination, waypoints, {
                            color: '#FF6B35',
                            width: 6
                        });
                        console.log('Route drawn successfully with', deliveries.length, 'delivery points and return to base');
                    } catch (error) {
                        console.error('Route drawing error:', error);
                    }
                } else {
                    console.error('No valid delivery coordinates found!');
                }
            } else {
                console.warn('Cannot draw route - missing data:', {
                    hasOrigin: !!(window.routeOriginLat && window.routeOriginLng),
                    hasShipments: window.routeShipments?.length > 0
                });
            }

            // Fit bounds to show all markers
            const positions = [];
            if (window.routeOriginLat && window.routeOriginLng) {
                positions.push({ lat: parseFloat(window.routeOriginLat), lng: parseFloat(window.routeOriginLng) });
            }
            if (window.driverCurrentLat && window.driverCurrentLng) {
                positions.push({ lat: parseFloat(window.driverCurrentLat), lng: parseFloat(window.driverCurrentLng) });
            }
            window.routeShipments.forEach(s => {
                if (s.pickup_lat && s.pickup_lng) positions.push({ lat: parseFloat(s.pickup_lat), lng: parseFloat(s.pickup_lng) });
                if (s.delivery_lat && s.delivery_lng) positions.push({ lat: parseFloat(s.delivery_lat), lng: parseFloat(s.delivery_lng) });
            });
            if (positions.length > 0) routeMap.fitBounds(positions);
        }
    }
    
    // Initialize map when page loads
    function initRouteMap() {
        const mapContainer = document.getElementById('route-map');
        if (!mapContainer) return;

        // Use Mapbox if available
        if (typeof MapboxHelper !== 'undefined' && window.mapboxAccessToken) {
            if (!window.mapboxRouteMapInitialized) {
                console.log('Using Mapbox for route map on driver dashboard');
                window.mapboxRouteMapInitialized = true;
            }
            initRouteMapWithMapbox();
            return;
        }
        
        // Fallback: Show message if Mapbox not available
        mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>⚠️ Mapa não disponível</p><p style="font-size: 0.9em; opacity: 0.8;">Mapbox não configurado.</p></div>';
    }

    // Initialize map when page loads (only once and only if route map container exists)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            const mapContainer = document.getElementById('route-map');
            if (mapContainer && !window.routeMapInitialized && !window.routeMapInitializing) {
                window.routeMapInitializing = true;
                setTimeout(() => {
                    initRouteMap();
                }, 500); // Small delay to ensure MapboxHelper is loaded
            }
        });
    } else {
        const mapContainer = document.getElementById('route-map');
        if (mapContainer && !window.routeMapInitialized && !window.routeMapInitializing) {
            window.routeMapInitializing = true;
            setTimeout(() => {
                initRouteMap();
            }, 500);
        }
    }
    
    // Start proximity checking after map loads (driver-specific)
    setTimeout(() => {
        if (typeof startProximityChecking === 'function') {
            startProximityChecking();
        }
    }, 2000);

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (typeof stopProximityChecking === 'function') {
            stopProximityChecking();
        }
        if (typeof stopLocationPolling === 'function') {
            stopLocationPolling();
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('driver.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/driver/dashboard.blade.php ENDPATH**/ ?>