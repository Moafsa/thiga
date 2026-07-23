@extends('driver.layout')

@section('title', 'Dashboard Motorista - TMS SaaS')

@push('styles')
<style>
    .route-status-card {
        background: linear-gradient(135deg, var(--cor-acento) 0%, rgba(var(--cor-acento-rgb), 0.8) 100%);
        color: var(--cor-principal);
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(var(--cor-acento-rgb), 0.3);
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
        background-color: rgba(var(--cor-acento-rgb), 0.1);
    }

    /* Wallet Card Styles */
    .wallet-card {
        background: linear-gradient(135deg, var(--cor-secundaria) 0%, var(--cor-principal) 100%);
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
        border: 2px solid rgba(var(--cor-acento-rgb), 0.5);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        cursor: pointer;
        font-size: 0.9em;
        transition: all 0.3s ease;
    }

    .route-option-btn:hover {
        background: rgba(var(--cor-acento-rgb), 0.2);
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

    /* Route History Styles */
    .route-history-section {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .route-history-header {
        margin-bottom: 25px;
    }

    .route-history-header h2 {
        color: var(--cor-acento);
        font-size: 1.4em;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .history-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .stat-item {
        background: rgba(255, 255, 255, 0.05);
        padding: 15px;
        border-radius: 10px;
        text-align: center;
    }

    .stat-label {
        display: block;
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 8px;
    }

    .stat-value {
        display: block;
        font-size: 1.5em;
        font-weight: 700;
        color: var(--cor-acento);
    }

    /* Upcoming Routes Styles */
    .upcoming-routes-section {
        margin-bottom: 30px;
        padding-bottom: 25px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }

    .upcoming-route-card {
        background: rgba(var(--cor-acento-rgb), 0.1);
        border: 2px solid rgba(var(--cor-acento-rgb), 0.3);
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .upcoming-route-card:hover {
        background: rgba(var(--cor-acento-rgb), 0.15);
        border-color: var(--cor-acento);
        transform: translateX(5px);
    }

    .upcoming-route-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 10px;
    }

    .upcoming-route-header h4 {
        color: var(--cor-acento);
        font-size: 1.1em;
        margin: 0;
    }

    .upcoming-route-info {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: rgba(245, 245, 245, 0.8);
        font-size: 0.9em;
    }

    .info-item i {
        color: var(--cor-acento);
    }

    /* Timeline Styles */
    .timeline-container {
        margin-top: 25px;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--cor-acento), rgba(var(--cor-acento-rgb), 0.3));
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }

    .timeline-marker {
        position: absolute;
        left: -37px;
        top: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9em;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        z-index: 2;
    }

    .timeline-content {
        margin-left: 0;
    }

    .route-history-card {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid var(--cor-acento);
        transition: all 0.3s ease;
    }

    .route-history-card:hover {
        background: rgba(255, 255, 255, 0.08);
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .route-history-header-card {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .route-history-header-card h4 {
        color: var(--cor-texto-claro);
        font-size: 1.2em;
        margin: 0 0 5px 0;
    }

    .route-date {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin: 0;
    }

    .efficiency-badge {
        padding: 10px 15px;
        border-radius: 10px;
        text-align: center;
        min-width: 80px;
    }

    .efficiency-badge span {
        display: block;
        color: var(--cor-texto-claro);
    }

    .route-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin: 15px 0;
    }

    .route-stat {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 8px;
    }

    .route-stat i {
        color: var(--cor-acento);
        font-size: 1.2em;
    }

    .route-stat .stat-label {
        display: block;
        font-size: 0.75em;
        color: rgba(245, 245, 245, 0.6);
        margin-bottom: 3px;
    }

    .route-stat .stat-value {
        display: block;
        font-size: 1em;
        font-weight: 600;
        color: var(--cor-texto-claro);
    }

    .achievements-section {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .achievements-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .achievement-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        color: var(--cor-texto-claro);
        font-weight: 500;
    }

    .achievement-badge i {
        font-size: 0.9em;
    }

    @media (max-width: 768px) {
        .history-stats {
            grid-template-columns: 1fr;
        }

        .route-stats-grid {
            grid-template-columns: 1fr;
        }

        .route-history-header-card {
            flex-direction: column;
            gap: 10px;
        }

        .timeline {
            padding-left: 20px;
        }

        .timeline-marker {
            left: -27px;
            width: 24px;
            height: 24px;
            font-size: 0.8em;
        }
    }

    @keyframes slideUpToast {
        from {
            transform: translate(-50%, 50px);
            opacity: 0;
        }
        to {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    }
    
    .status-badge.pending-sync {
        background-color: rgba(var(--cor-acento-rgb), 0.2);
        color: var(--cor-acento);
        border: 2px solid var(--cor-acento);
        border-radius: 4px;
    }
</style>
@endpush

@section('content')
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

<!-- Offline Status Bar -->
<div id="offline-sync-banner" style="display: none; background: linear-gradient(135deg, var(--cor-acento) 0%, rgba(var(--cor-acento-rgb), 0.8) 100%); color: #ffffff; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-weight: 600; box-shadow: 0 4px 12px rgba(var(--cor-acento-rgb), 0.25); align-items: center; justify-content: space-between; gap: 10px;">
    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.95em;">
        <i class="fas fa-wifi-slash" id="offline-banner-icon" style="color: #ffffff;"></i>
        <span id="offline-banner-text" style="color: #ffffff;">Você possui entregas aguardando sinal...</span>
    </div>
    <button onclick="startManualSync()" id="offline-sync-btn" class="btn-primary" style="background: #111111; color: var(--cor-acento); padding: 6px 12px; font-size: 0.85em; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 6px; font-weight: 700; transition: all 0.2s ease;">
        <i class="fas fa-sync"></i> Sincronizar Agora
    </button>
</div>

<!-- Compartilhar Localização (GPS) Card - Visível para TODOS os motoristas -->
<div class="driver-card" style="background: var(--cor-secundaria); border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="color: var(--cor-acento); font-size: 1.1em; margin-bottom: 5px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-satellite-dish"></i> Compartilhar Localização (GPS)
            </h3>
            <p id="gps-status-text" style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin: 0;">
                Transmissão de localização em tempo real desativada
            </p>
        </div>
        <div>
            <label class="gps-switch" style="position: relative; display: inline-block; width: 56px; height: 30px; cursor: pointer;">
                <input type="checkbox" id="gps-toggle-switch" style="opacity: 0; width: 0; height: 0;">
                <span class="gps-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.25); transition: .3s ease; border-radius: 34px; border: 1px solid rgba(255,255,255,0.2);">
                    <span class="gps-knob" style="position: absolute; height: 22px; width: 22px; left: 3px; bottom: 3px; background-color: #ffffff; transition: .3s ease; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.4);"></span>
                </span>
            </label>
        </div>
    </div>
    <div id="gps-details-info" style="margin-top: 12px; font-size: 0.8em; color: rgba(245, 245, 245, 0.6); display: none;">
        <span><i class="fas fa-location-arrow" style="color: #4caf50; margin-right: 5px;"></i> Transmitindo GPS a cada 15s</span>
        <span id="gps-last-sent" style="float: right;"></span>
    </div>
</div>

<style>
#gps-toggle-switch:checked + .gps-slider {
    background-color: #4caf50 !important;
    border-color: #4caf50 !important;
}
#gps-toggle-switch:checked + .gps-slider .gps-knob {
    transform: translateX(26px) !important;
}
</style>

<!-- Navigation / Location Map (Sempre Visível) -->
<div class="route-map-container" style="background-color: var(--cor-secundaria); border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <h3 style="color: var(--cor-acento); margin: 0; font-size: 1.2em; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-map-marked-alt"></i>
            @if($activeRoute && $activeRoute->shipments->isNotEmpty())
                Mapa de Navegação da Rota
            @else
                Mapa de Navegação / Localização
            @endif
        </h3>
        <div style="display: flex; gap: 8px; align-items: center;">
            <button type="button" onclick="centerMapOnDriver()" style="padding: 8px 14px; font-size: 0.85em; background: rgba(33,150,243,0.2); color: #2196F3; border: 1px solid #2196F3; border-radius: 8px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fas fa-crosshairs"></i> Minha Posição
            </button>
        </div>
    </div>
    <div id="route-map" style="width: 100%; height: 380px; border-radius: 10px; overflow: hidden; position: relative;"></div>
</div>

@if($activeRoute)
    <!-- Route Status Card -->
    <div class="route-status-card">
        <h2><i class="fas fa-route"></i> Rota Ativa</h2>
        <p><strong>{{ $activeRoute->name }}</strong></p>
        <p style="margin-top: 5px;">{{ $shipments->count() }} entregas</p>
        <div class="action-buttons">
            @if($activeRoute->status === 'scheduled')
            <button class="btn-primary" onclick="startRoute({{ $activeRoute->id }})">
                <i class="fas fa-play"></i> Iniciar Rota
            </button>
            @elseif($activeRoute->status === 'in_progress')
            <button class="btn-secondary" onclick="finishRoute({{ $activeRoute->id }})">
                <i class="fas fa-check"></i> Finalizar Rota
            </button>
            @endif
        </div>
    </div>

    <!-- Location Status -->
    @if($driver->current_latitude && $driver->current_longitude)
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
            Última atualização: {{ (isset($driver->attributes["last_location_update"]) && $driver->attributes["last_location_update"]) ? \Carbon\Carbon::parse($driver->attributes["last_location_update"])->diffForHumans() : "Nunca" }}
        </p>
    </div>
    @endif

    <!-- Nested Timeline Route Section (Ordem de Entrega Otimizada) -->
    <div id="shipments" style="margin-top: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: var(--cor-acento); margin: 0; font-size: 1.2em; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-route"></i> Itinerário Otimizado da Rota ({{ $shipments->count() }} Entregas)
            </h2>
            <span style="background: rgba(33,150,243,0.2); color: #2196F3; border: 1px solid #2196F3; padding: 4px 10px; border-radius: 12px; font-size: 0.8em; font-weight: 600;">
                <i class="fas fa-magic"></i> Melhor Caminho
            </span>
        </div>

        
        @if($shipments->count() > 0)
            <div class="nested-route-timeline" style="position: relative; padding-left: 28px; border-left: 3px dashed rgba(255,255,255,0.25); margin-left: 12px; margin-bottom: 20px;">

                <!-- 1. PONTO DE PARTIDA (FILIAL / DEPÓSITO DE ORIGEM) -->
                <div class="timeline-step-node" style="position: relative; margin-bottom: 25px;">
                    <div style="position: absolute; left: -42px; top: 0; width: 28px; height: 28px; border-radius: 50%; background: #FF9800; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.85em; font-weight: bold; border: 2px solid var(--cor-principal); box-shadow: 0 0 10px rgba(255,152,0,0.5);">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div style="background: rgba(255,152,0,0.12); border: 1px solid rgba(255,152,0,0.3); border-radius: 10px; padding: 12px 16px;">
                        <span style="color: #FF9800; font-weight: 700; font-size: 0.8em; text-transform: uppercase; letter-spacing: 0.5px;">Ponto de Partida (Carregamento)</span>
                        <h4 style="color: #fff; margin: 4px 0 2px 0; font-size: 1em; font-weight: 600;">
                            <i class="fas fa-building" style="color: var(--cor-acento);"></i> {{ $activeRoute->branch ? $activeRoute->branch->name : ($activeRoute->origin_branch ?: 'Origem da Carga') }}
                        </h4>
                        <p style="color: rgba(245,245,245,0.6); font-size: 0.8em; margin: 0;">Início da rota otimizada e partida do veículo</p>
                    </div>
                </div>

                <!-- 2. PARADAS ANINHADAS POR CT-E EM ORDEM OTIMIZADA -->
                @foreach($shipments as $index => $shipment)
                    @php
                        $stepNumber = $index + 1;
                        $isDelivered = $shipment->status === 'delivered';
                        $isException = $shipment->status === 'exception';
                        $isPending = !$isDelivered && !$isException;
                        
                        $nodeColor = $isDelivered ? '#4caf50' : ($isException ? '#f44336' : '#2196F3');
                        $nodeIcon = $isDelivered ? 'check' : ($isException ? 'exclamation' : 'box');
                    @endphp
                    <div class="timeline-step-node shipment-node" data-shipment-id="{{ $shipment->id }}" style="position: relative; margin-bottom: 25px;">
                        <!-- Node Circle Badge -->
                        <div style="position: absolute; left: -42px; top: 12px; width: 28px; height: 28px; border-radius: 50%; background: {{ $nodeColor }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.85em; font-weight: bold; border: 2px solid var(--cor-principal); box-shadow: 0 0 10px {{ $nodeColor }}80;">
                            {{ $stepNumber }}
                        </div>

                        <div class="shipment-card" data-shipment-id="{{ $shipment->id }}" style="margin-bottom: 0; background: var(--cor-secundaria); border-radius: 12px; padding: 18px; border: 1px solid rgba(255,255,255,0.1); border-left: 4px solid {{ $nodeColor }};">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px; flex-wrap: wrap; gap: 10px;">
                                <div>
                                    <span style="font-size: 0.75em; text-transform: uppercase; font-weight: 700; color: {{ $nodeColor }};">Parada {{ $stepNumber }} de {{ $shipments->count() }}</span>
                                    <h3 style="color: var(--cor-acento); font-size: 1.1em; margin: 2px 0 4px 0;">
                                        <i class="fas fa-barcode"></i> {{ $shipment->tracking_number }}
                                    </h3>
                                    @if($shipment->title)
                                        <p style="color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin: 0 0 4px 0;">{{ $shipment->title }}</p>
                                    @endif
                                </div>
                                <span class="status-badge {{ $shipment->status }}" style="background: rgba({{ $isDelivered ? '76,175,80' : ($isException ? '244,67,54' : '33,150,243') }}, 0.2); color: {{ $nodeColor }}; border: 1px solid {{ $nodeColor }}; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.85em;">
                                    @if($isDelivered)
                                        <i class="fas fa-check-circle"></i> Entregue
                                    @elseif($isException)
                                        <i class="fas fa-exclamation-triangle"></i> Não Entregue / Ocorrência
                                    @else
                                        <i class="fas fa-clock"></i> Pendente
                                    @endif
                                </span>
                            </div>

                            <div style="background: rgba(0,0,0,0.25); padding: 12px; border-radius: 8px; margin-bottom: 12px; display: flex; flex-direction: column; gap: 8px;">
                                @if($shipment->receiverClient)
                                    <div style="font-size: 0.9em; color: var(--cor-texto-claro);">
                                        <strong style="color: var(--cor-acento); display: block; font-size: 0.8em; text-transform: uppercase;">Recebedor / Cliente:</strong>
                                        <i class="fas fa-user"></i> {{ $shipment->receiverClient->name }}
                                    </div>
                                @endif
                                <div style="font-size: 0.9em; color: var(--cor-texto-claro);">
                                    <strong style="color: rgba(245,245,245,0.7); display: block; font-size: 0.8em; text-transform: uppercase;">Endereço de Entrega:</strong>
                                    <i class="fas fa-map-marker-alt" style="color: #f44336;"></i> 
                                    {{ $shipment->delivery_address ?: ($shipment->delivery_city ? "{$shipment->delivery_city}/{$shipment->delivery_state}" : 'Endereço cadastrado') }}
                                </div>
                            </div>

                            <!-- Botão GPS de Navegação -->
                            @if($shipment->delivery_latitude && $shipment->delivery_longitude)
                                <button type="button" class="nav-btn" onclick="openNavigation({{ $shipment->delivery_latitude }}, {{ $shipment->delivery_longitude }}, {{ json_encode($shipment->delivery_address . ', ' . $shipment->delivery_city . '/' . $shipment->delivery_state) }})" style="width: 100%; margin-bottom: 12px; padding: 10px; background: rgba(33,150,243,0.15); color: #2196F3; border: 1px solid #2196F3; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <i class="fas fa-directions"></i> Abrir Navegação GPS (Waze / Maps)
                                </button>
                            @endif

                            <!-- Miniaturas de Comprovantes/Ocorrências -->
                            @if($shipment->deliveryProofs && $shipment->deliveryProofs->count() > 0)
                                <div class="proof-photos" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.1);">
                                    <h4 style="color: var(--cor-acento); font-size: 0.85em; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                        <i class="fas fa-camera"></i> Fotos e Assinaturas Registradas:
                                    </h4>
                                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                        @foreach($shipment->deliveryProofs as $proof)
                                            @foreach($proof->photo_urls as $photoUrl)
                                                @if($photoUrl)
                                                    <div style="position: relative; width: 65px; height: 65px; border-radius: 8px; overflow: hidden; border: 2px solid {{ $proof->proof_type === 'pickup' ? '#FFD700' : ($proof->proof_type === 'other' ? '#f44336' : '#4CAF50') }}; background: #000;">
                                                        <img src="{{ $photoUrl }}" alt="Comprovante" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" onclick="openPhotoModal('{{ $photoUrl }}', '{{ $proof->proof_type === 'pickup' ? 'Coleta' : 'Comprovante' }}', '{{ $proof->delivery_time ? $proof->delivery_time->format('d/m/Y H:i') : '' }}')">
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Ações Rápidas: Entregue vs Não Entregue -->
                            <div class="shipment-actions" style="display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;">
                                <button type="button" class="btn-action delivered" onclick="updateShipmentStatus({{ $shipment->id }}, 'delivered')" style="flex: 1; min-width: 130px; padding: 12px; border-radius: 8px; border: none; font-weight: 700; cursor: pointer; background: #4caf50; color: #fff; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9em; box-shadow: 0 4px 10px rgba(76,175,80,0.3);">
                                    <i class="fas fa-check-circle"></i> Entregue
                                </button>
                                
                                <button type="button" class="btn-action exception" onclick="showExceptionModal({{ $shipment->id }})" style="flex: 1; min-width: 130px; padding: 12px; border-radius: 8px; border: none; font-weight: 700; cursor: pointer; background: #f44336; color: #fff; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.9em; box-shadow: 0 4px 10px rgba(244,67,54,0.3);">
                                    <i class="fas fa-times-circle"></i> Não Entregue
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- 3. PONTO FINAL (DESTINO / RETORNO) -->
                <div class="timeline-step-node" style="position: relative;">
                    <div style="position: absolute; left: -42px; top: 0; width: 28px; height: 28px; border-radius: 50%; background: #9C27B0; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.85em; font-weight: bold; border: 2px solid var(--cor-principal); box-shadow: 0 0 10px rgba(156,39,176,0.5);">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div style="background: rgba(156,39,176,0.12); border: 1px solid rgba(156,39,176,0.3); border-radius: 10px; padding: 12px 16px;">
                        <span style="color: #9C27B0; font-weight: 700; font-size: 0.8em; text-transform: uppercase; letter-spacing: 0.5px;">Conclusão da Rota</span>
                        <h4 style="color: #fff; margin: 4px 0 2px 0; font-size: 1em; font-weight: 600;">Retorno à Base / Filial</h4>
                        <p style="color: rgba(245,245,245,0.6); font-size: 0.8em; margin: 0;">Finalização do percurso e recolhimento</p>
                    </div>
                </div>

            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Nenhuma entrega nesta rota</p>
            </div>
        @endif
    </div>
@else
    <div class="empty-state">
        <i class="fas fa-route"></i>
        <h3 style="color: var(--cor-texto-claro); margin-bottom: 10px;">Nenhuma Rota Ativa</h3>
        <p>Você não tem rotas atribuídas no momento.</p>
    </div>
@endif

<!-- Wallet Card (always visible) -->
<div class="wallet-card">
    <div class="wallet-header">
        <h2><i class="fas fa-wallet"></i> Carteira</h2>
        <div style="display: flex; gap: 10px; align-items: center;">
            <form method="GET" action="{{ route('driver.dashboard') }}" id="period-filter-form" style="display: flex; gap: 5px;">
                <select name="period" id="period-select" onchange="this.form.submit()" style="padding: 8px; border-radius: 8px; background: var(--cor-principal); color: var(--cor-texto-claro); border: 1px solid rgba(255,255,255,0.2); font-size: 0.85em;">
                    <option value="all" {{ ($period ?? 'all') === 'all' ? 'selected' : '' }}>Todo Período</option>
                    <option value="week" {{ ($period ?? 'all') === 'week' ? 'selected' : '' }}>Esta Semana</option>
                    <option value="month" {{ ($period ?? 'all') === 'month' ? 'selected' : '' }}>Este Mês</option>
                    <option value="year" {{ ($period ?? 'all') === 'year' ? 'selected' : '' }}>Este Ano</option>
                </select>
            </form>
            <a href="{{ route('driver.wallet.export', ['period' => $period ?? 'all']) }}" class="btn-primary" style="padding: 8px 12px; font-size: 0.85em; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>
    
    <div class="wallet-balance">
        <div class="wallet-balance-label">Saldo Disponível</div>
        <div class="wallet-balance-value" style="color: {{ ($currentBalance ?? 0) >= 0 ? '#4caf50' : '#f44336' }};">
            R$ {{ number_format($currentBalance ?? 0, 2, ',', '.') }}
        </div>
    </div>

    <div class="wallet-summary">
        <div class="wallet-summary-item">
            <div class="wallet-summary-label">Total Recebido</div>
            <div class="wallet-summary-value received">R$ {{ number_format($totalReceived ?? 0, 2, ',', '.') }}</div>
        </div>
        <div class="wallet-summary-item">
            <div class="wallet-summary-label">Gastos Comprovados</div>
            <div class="wallet-summary-value spent">R$ {{ number_format($totalSpent ?? 0, 2, ',', '.') }}</div>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 15px;">
        <a href="{{ route('driver.wallet') }}" class="btn-primary" style="padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-wallet"></i> Ver Carteira Completa
        </a>
    </div>

    @if($recentFinancialRoutes && $recentFinancialRoutes->count() > 0)
    <div class="wallet-transactions">
        <h3><i class="fas fa-history"></i> Histórico Recente</h3>
        @foreach($recentFinancialRoutes as $transaction)
        <div class="transaction-item">
            <div class="transaction-info">
                <div class="transaction-route-name">{{ $transaction['description'] }}</div>
                <div class="transaction-date">{{ $transaction['date']->format('d/m/Y') }}</div>
                @if(isset($transaction['expense']) && $transaction['expense']->expense_type)
                <div style="font-size: 0.8em; color: rgba(245,245,245,0.6); margin-top: 3px;">
                    <i class="fas fa-tag"></i> {{ $transaction['expense']->expense_type_label }}
                </div>
                @endif
            </div>
            <div class="transaction-amounts">
                @if($transaction['is_positive'])
                <div class="transaction-received" style="color: #4caf50; font-weight: 600;">
                    + R$ {{ number_format($transaction['amount'], 2, ',', '.') }}
                </div>
                @else
                <div class="transaction-spent" style="color: #f44336; font-weight: 600;">
                    - R$ {{ number_format($transaction['amount'], 2, ',', '.') }}
                </div>
                @endif
                <div class="transaction-net" style="font-size: 0.9em; color: {{ $transaction['balance'] >= 0 ? '#4caf50' : '#f44336' }}; margin-top: 5px;">
                    Saldo: {{ $transaction['balance'] >= 0 ? '+' : '' }}R$ {{ number_format($transaction['balance'], 2, ',', '.') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-transactions">
        <i class="fas fa-inbox"></i> Nenhuma transação financeira registrada ainda.
    </div>
    @endif

    @if(isset($period) && $period !== 'all')
    <div class="wallet-period-info">
        <i class="fas fa-calendar"></i> 
        Período: {{ $startDate ? $startDate->format('d/m/Y') : 'Início' }} até {{ $endDate->format('d/m/Y') }}
    </div>
    @endif
</div>

<!-- Route History Timeline -->
<div class="route-history-section">
    <div class="route-history-header">
        <h2><i class="fas fa-history"></i> Histórico de Rotas</h2>
        <div class="history-stats">
            <div class="stat-item">
                <span class="stat-label">Total de Rotas</span>
                <span class="stat-value">{{ $driverStats['total_routes'] ?? 0 }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Distância Total</span>
                <span class="stat-value">{{ number_format($driverStats['total_distance_km'] ?? 0, 0, ',', '.') }} km</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Eficiência Média</span>
                <span class="stat-value" style="color: {{ ($driverStats['average_efficiency'] ?? 0) >= 75 ? '#4caf50' : (($driverStats['average_efficiency'] ?? 0) >= 60 ? '#ffc107' : '#f44336') }}">
                    {{ number_format($driverStats['average_efficiency'] ?? 0, 1) }}%
                </span>
            </div>
        </div>
    </div>

    <!-- Upcoming Routes -->
    @if(isset($upcomingRoutes) && $upcomingRoutes->count() > 0)
    <div class="upcoming-routes-section">
        <h3 style="color: var(--cor-acento); margin-bottom: 15px; font-size: 1.1em;">
            <i class="fas fa-calendar-check"></i> Próximas Rotas
        </h3>
        @foreach($upcomingRoutes as $upcomingRoute)
        <div class="upcoming-route-card">
            <div class="upcoming-route-header">
                <div>
                    <h4>{{ $upcomingRoute->name }}</h4>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 5px 0;">
                        <i class="fas fa-calendar"></i> {{ $upcomingRoute->scheduled_date->format('d/m/Y') }}
                        @if($upcomingRoute->start_time)
                        <span style="margin-left: 10px;">
                            <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($upcomingRoute->start_time)->format('H:i') }}
                        </span>
                        @endif
                    </p>
                </div>
                <span class="status-badge pending">{{ $upcomingRoute->status_label }}</span>
            </div>
            <div class="upcoming-route-info">
                <div class="info-item">
                    <i class="fas fa-box"></i>
                    <span>{{ $upcomingRoute->shipments->count() }} entregas</span>
                </div>
                @if($upcomingRoute->estimated_distance)
                <div class="info-item">
                    <i class="fas fa-route"></i>
                    <span>{{ number_format($upcomingRoute->estimated_distance, 1, ',', '.') }} km</span>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- History Sub-Tabs Navigation -->
    <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; flex-wrap: wrap;">
        <button type="button" id="tab-btn-shipments-history" onclick="switchHistoryTab('shipments')" class="history-tab-btn active" style="flex: 1 1 130px; padding: 10px 12px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; background: var(--cor-acento); color: var(--cor-principal); font-size: 0.85em; display: flex; align-items: center; justify-content: center; gap: 6px; text-align: center;">
            <i class="fas fa-box"></i> Entregas Realizadas ({{ $completedShipments->count() }})
        </button>
        <button type="button" id="tab-btn-routes-history" onclick="switchHistoryTab('routes')" class="history-tab-btn" style="flex: 1 1 130px; padding: 10px 12px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; background: rgba(255,255,255,0.1); color: var(--cor-texto-claro); font-size: 0.85em; display: flex; align-items: center; justify-content: center; gap: 6px; text-align: center;">
            <i class="fas fa-route"></i> Rotas Concluídas ({{ $completedRoutes->count() }})
        </button>
    </div>

    <!-- Completed Deliveries History List -->
    <div id="history-shipments-tab" class="history-tab-content" style="max-width: 100%; overflow-x: hidden;">
        @if(isset($completedShipments) && $completedShipments->count() > 0)
            <div style="display: flex; flex-direction: column; gap: 15px; width: 100%;">
                @foreach($completedShipments as $cShipment)
                    <div class="shipment-card" style="border-left: 4px solid #4caf50; background: var(--cor-secundaria); padding: 14px; border-radius: 12px; margin-bottom: 0; width: 100%; box-sizing: border-box; overflow: hidden;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                            <div style="max-width: 100%; overflow-wrap: anywhere;">
                                <h3 style="color: var(--cor-acento); font-size: 1.05em; margin: 0 0 4px 0; word-break: break-word;">
                                    <i class="fas fa-barcode"></i> {{ $cShipment->tracking_number }}
                                </h3>
                                @if($cShipment->title)
                                    <p style="color: var(--cor-texto-claro); font-size: 0.85em; margin: 0 0 4px 0; font-weight: 600; word-break: break-word;">{{ $cShipment->title }}</p>
                                @endif
                            </div>
                            <span class="status-badge delivered" style="background: rgba(76,175,80,0.2); color: #4caf50; border: 1px solid #4caf50; font-size: 0.75em; padding: 4px 8px;">
                                <i class="fas fa-check-circle"></i> Entregue {{ $cShipment->updated_at ? $cShipment->updated_at->format('d/m/Y H:i') : '' }}
                            </span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box;">
                            @if($cShipment->receiverClient)
                                <div>
                                    <span style="color: rgba(245,245,245,0.6); font-size: 0.75em; display: block;">Cliente / Recebedor:</span>
                                    <span style="color: var(--cor-texto-claro); font-weight: 600; font-size: 0.85em; word-break: break-word;">
                                        <i class="fas fa-user" style="color: var(--cor-acento);"></i> {{ $cShipment->receiverClient->name }}
                                    </span>
                                </div>
                            @endif

                            <div>
                                <span style="color: rgba(245,245,245,0.6); font-size: 0.75em; display: block;">Endereço de Entrega:</span>
                                <span style="color: var(--cor-texto-claro); font-size: 0.85em; word-break: break-word;">
                                    <i class="fas fa-map-marker-alt" style="color: #f44336;"></i> 
                                    {{ $cShipment->delivery_address ?: ($cShipment->delivery_city ? "{$cShipment->delivery_city}/{$cShipment->delivery_state}" : 'Endereço registrado') }}
                                </span>
                            </div>

                            @if($cShipment->route)
                                <div>
                                    <span style="color: rgba(245,245,245,0.6); font-size: 0.75em; display: block;">Rota Realizada:</span>
                                    <span style="color: var(--cor-acento); font-size: 0.85em; font-weight: 600; word-break: break-word;">
                                        <i class="fas fa-route"></i> {{ $cShipment->route->name }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Proofs Thumbnails -->
                        @if($cShipment->deliveryProofs && $cShipment->deliveryProofs->count() > 0)
                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.1);">
                                <strong style="color: var(--cor-acento); font-size: 0.85em; display: block; margin-bottom: 8px;">
                                    <i class="fas fa-camera"></i> Comprovante e Assinatura Registrados:
                                </strong>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    @foreach($cShipment->deliveryProofs as $dProof)
                                        @if($dProof->photo_urls && count($dProof->photo_urls) > 0)
                                            @foreach($dProof->photo_urls as $pUrl)
                                                @if($pUrl)
                                                    <div style="position: relative; width: 60px; height: 60px; border-radius: 6px; overflow: hidden; border: 2px solid #4caf50;">
                                                        <img src="{{ $pUrl }}" alt="Foto" style="width:100%; height:100%; object-fit:cover; cursor:pointer;" onclick="openPhotoModal('{{ $pUrl }}', 'Comprovante', '{{ $dProof->delivery_time ? $dProof->delivery_time->format('d/m/Y H:i') : '' }}', 'Foto de Entrega')">
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                        @if($dProof->signature_url)
                                            <div style="position: relative; width: 90px; height: 60px; border-radius: 6px; overflow: hidden; border: 2px solid #2196F3; background: #fff;">
                                                <img src="{{ $dProof->signature_url }}" alt="Assinatura" style="width:100%; height:100%; object-fit:contain; cursor:pointer;" onclick="openPhotoModal('{{ $dProof->signature_url }}', 'Assinatura', '{{ $dProof->delivery_time ? $dProof->delivery_time->format('d/m/Y H:i') : '' }}', 'Recebedor: {{ addslashes($dProof->recipient_name ?? '') }}')">
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" style="margin: 20px 0;">
                <i class="fas fa-box-open" style="font-size: 3em; opacity: 0.3;"></i>
                <p style="margin-top: 15px;">Nenhuma entrega concluída registrada ainda.</p>
            </div>
        @endif
    </div>

    <!-- Completed Routes History List -->
    <div id="history-routes-tab" class="history-tab-content" style="display: none; max-width: 100%; overflow-x: hidden;">
        @if(isset($completedRoutes) && $completedRoutes->count() > 0)
            <div style="display: flex; flex-direction: column; gap: 15px; width: 100%;">
                @foreach($completedRoutes as $cRoute)
                    <div style="background: var(--cor-principal); padding: 14px; border-radius: 12px; border-left: 4px solid var(--cor-acento); border: 1px solid rgba(255,255,255,0.1); width: 100%; box-sizing: border-box; overflow: hidden;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                            <div style="max-width: 100%; overflow-wrap: anywhere;">
                                <h4 style="color: var(--cor-acento); font-size: 1.05em; margin: 0 0 5px 0; word-break: break-word;">
                                    <i class="fas fa-route"></i> {{ $cRoute->name }}
                                </h4>
                                <p style="color: rgba(245,245,245,0.7); font-size: 0.8em; margin: 0;">
                                    <i class="fas fa-calendar"></i> Agendada: {{ $cRoute->scheduled_date ? $cRoute->scheduled_date->format('d/m/Y') : 'N/A' }}
                                    @if($cRoute->completed_at)
                                        <span style="display: inline-block; margin-left: 6px; color: #4caf50;">
                                            <i class="fas fa-check-circle"></i> Concluída: {{ $cRoute->completed_at->format('d/m/Y H:i') }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <span class="status-badge delivered" style="font-size: 0.75em; padding: 4px 8px;">Concluída</span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px; width: 100%; box-sizing: border-box;">
                            <div style="background: rgba(255,255,255,0.05); padding: 8px 12px; border-radius: 8px;">
                                <span style="color: rgba(245,245,245,0.6); font-size: 0.75em; display: block;">Total de Entregas:</span>
                                <span style="color: var(--cor-texto-claro); font-weight: 700; font-size: 0.9em;">
                                    <i class="fas fa-box" style="color: var(--cor-acento);"></i> {{ $cRoute->shipments->count() }} cargas
                                </span>
                            </div>
                            @if($cRoute->vehicle)
                                <div style="background: rgba(255,255,255,0.05); padding: 8px 12px; border-radius: 8px;">
                                    <span style="color: rgba(245,245,245,0.6); font-size: 0.75em; display: block;">Veículo Utilizado:</span>
                                    <span style="color: var(--cor-texto-claro); font-weight: 600; font-size: 0.85em;">
                                        <i class="fas fa-truck"></i> {{ $cRoute->vehicle->formatted_plate }}
                                    </span>
                                </div>
                            @endif
                            @if($cRoute->estimated_distance)
                                <div style="background: rgba(255,255,255,0.05); padding: 8px 12px; border-radius: 8px;">
                                    <span style="color: rgba(245,245,245,0.6); font-size: 0.75em; display: block;">Distância Estimada:</span>
                                    <span style="color: var(--cor-texto-claro); font-weight: 600; font-size: 0.85em;">
                                        <i class="fas fa-road"></i> {{ number_format($cRoute->estimated_distance, 1, ',', '.') }} km
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" style="margin: 20px 0;">
                <i class="fas fa-history" style="font-size: 3em; opacity: 0.3;"></i>
                <p style="margin-top: 15px;">Nenhuma rota concluída registrada no histórico.</p>
            </div>
        @endif
    </div>
</div>

<script>
function switchHistoryTab(tab) {
    const shipmentsTab = document.getElementById('history-shipments-tab');
    const routesTab = document.getElementById('history-routes-tab');
    const shipmentsBtn = document.getElementById('tab-btn-shipments-history');
    const routesBtn = document.getElementById('tab-btn-routes-history');

    if (tab === 'shipments') {
        if (shipmentsTab) shipmentsTab.style.display = 'block';
        if (routesTab) routesTab.style.display = 'none';
        if (shipmentsBtn) {
            shipmentsBtn.style.background = 'var(--cor-acento)';
            shipmentsBtn.style.color = 'var(--cor-principal)';
        }
        if (routesBtn) {
            routesBtn.style.background = 'rgba(255,255,255,0.1)';
            routesBtn.style.color = 'var(--cor-texto-claro)';
        }
    } else {
        if (shipmentsTab) shipmentsTab.style.display = 'none';
        if (routesTab) routesTab.style.display = 'block';
        if (routesBtn) {
            routesBtn.style.background = 'var(--cor-acento)';
            routesBtn.style.color = 'var(--cor-principal)';
        }
        if (shipmentsBtn) {
            shipmentsBtn.style.background = 'rgba(255,255,255,0.1)';
            shipmentsBtn.style.color = 'var(--cor-texto-claro)';
        }
    }
}
</script>

<!-- Status Update Modal (Entregue / Não Entregue) -->
<div id="statusModal" class="modal">
    <div class="modal-content" style="max-width: 500px; border-radius: 15px;">
        <div class="modal-header">
            <h3 id="modalTitleText" style="color: var(--cor-acento); font-size: 1.2em; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-clipboard-check"></i> Atualizar Status
            </h3>
            <button class="close-modal" onclick="closeModal('statusModal')">&times;</button>
        </div>
        <form id="statusForm" onsubmit="submitStatusUpdate(event)">
            <input type="hidden" id="modalShipmentId" name="shipment_id">
            <input type="hidden" id="modalStatus" name="status">
            
            <!-- Motivo da Ocorrência (Exibido apenas para 'exception') -->
            <div id="exception-reason-section" style="display: none; margin-bottom: 15px;">
                <label style="color: #f44336; font-weight: 600; display: block; margin-bottom: 8px; font-size: 0.9em;">
                    <i class="fas fa-exclamation-triangle"></i> Motivo da Não Entrega / Ocorrência *
                </label>
                <select name="exception_reason" id="exception_reason" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro); font-size: 0.95em;">
                    <option value="Cliente Ausente">🚪 Cliente Ausente / Fechado</option>
                    <option value="Endereço Não Encontrado">📍 Endereço Não Encontrado / Inexistente</option>
                    <option value="Recusado pelo Destinatário">🚫 Recusado pelo Destinatário</option>
                    <option value="Mercadoria Avariada">📦 Mercadoria Avariada / Danificada</option>
                    <option value="Estabelecimento Fechado">🏢 Estabelecimento Fechado</option>
                    <option value="Dificuldade de Acesso / Segurança">⚠️ Dificuldade de Acesso / Segurança</option>
                    <option value="Outros">❓ Outros Motivos (Descreva abaixo)</option>
                </select>
            </div>

            <!-- Upload de Foto Obrigatório -->
            <div class="file-input-wrapper" style="margin-bottom: 15px;">
                <label for="proofPhoto" class="file-input-label" style="border: 2px dashed var(--cor-acento); border-radius: 10px; padding: 20px; text-align: center; display: block; cursor: pointer; background: rgba(0,0,0,0.2);">
                    <i class="fas fa-camera" style="font-size: 2em; color: var(--cor-acento); margin-bottom: 8px;"></i><br>
                    <span id="photoLabelText" style="font-weight: 600; color: #fff;">📸 Tirar / Adicionar Foto (Obrigatório) *</span><br>
                    <small style="color: rgba(245,245,245,0.6); font-size: 0.75em;">Otimizada e comprimida automaticamente</small>
                </label>
                <input type="file" id="proofPhoto" name="photo" accept="image/*" capture="environment" onchange="previewPhoto(this)" style="display: none;" required>
                <img id="photoPreview" class="photo-preview" style="display: none; width: 100%; max-height: 200px; object-fit: cover; border-radius: 10px; margin-top: 10px; border: 2px solid var(--cor-acento);">
            </div>

            <!-- Aviso de Coordenadas GPS -->
            <div style="background: rgba(33,150,243,0.12); border: 1px solid rgba(33,150,243,0.3); border-radius: 8px; padding: 10px; margin-bottom: 15px; font-size: 0.8em; color: #2196F3; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-satellite-dish" style="font-size: 1.2em;"></i>
                <span>Suas coordenadas GPS exatas serão vinculadas ao comprovante.</span>
            </div>
            
            <!-- Seção de Assinatura (Exibida apenas para 'delivered') -->
            <div id="signature-section" style="display: none; margin-bottom: 15px;">
                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px; font-weight: 600;">Nome do Recebedor</label>
                <input type="text" name="recipient_name" id="recipient_name" placeholder="Nome de quem recebeu" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro); margin-bottom: 10px;">
                <input type="text" name="recipient_document" id="recipient_document" placeholder="Documento (RG/CPF) - Opcional" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro); margin-bottom: 10px;">
                
                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px; font-weight: 600;">Assinatura do Recebedor (Opcional)</label>
                <div style="background: white; border-radius: 8px; overflow: hidden; position: relative; touch-action: none;">
                    <canvas id="signature-pad" style="width: 100%; height: 180px; display: block;"></canvas>
                    <button type="button" onclick="if(window.signaturePad) window.signaturePad.clear()" style="position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.6); border: none; color: white; border-radius: 4px; padding: 6px 12px; font-size: 0.85em; cursor: pointer; z-index: 10;">Limpar</button>
                </div>
                <input type="hidden" name="recipient_signature" id="recipient_signature">
            </div>

            <div style="margin-bottom: 15px;">
                <label id="notesLabelText" style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px; font-weight: 600;">Justificativa / Observações</label>
                <textarea name="notes" id="statusNotes" rows="3" placeholder="Digite observações..." style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro); resize: none;"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" id="submitStatusBtn" class="btn-primary" style="flex: 1; padding: 12px; font-weight: 700;">
                    <i class="fas fa-check"></i> Confirmar
                </button>
                <button type="button" class="btn-secondary" onclick="closeModal('statusModal')" style="flex: 1; padding: 12px;">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
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
    @php
        $driverLat = $driver->current_latitude ?? null;
        $driverLng = $driver->current_longitude ?? null;
        $routeOriginLat = ($activeRoute && $activeRoute->start_latitude) ? $activeRoute->start_latitude : null;
        $routeOriginLng = ($activeRoute && $activeRoute->start_longitude) ? $activeRoute->start_longitude : null;
        $routeOriginName = ($activeRoute && $activeRoute->branch) ? $activeRoute->branch->name : 'Ponto de Partida';
        $routeId = ($activeRoute && $activeRoute->id) ? $activeRoute->id : null;
        $tenantId = auth()->user()->tenant_id ?? null;
        $driverId = $driver->id ?? null;
    @endphp
    window.driverCurrentLat = @json($driverLat);
    window.driverCurrentLng = @json($driverLng);
    window.routeOriginLat = @json($routeOriginLat);
    window.routeOriginLng = @json($routeOriginLng);
    window.routeOriginName = @json($routeOriginName);
    window.routeId = @json($routeId);
    window.tenantId = @json($tenantId);
    window.driverId = @json($driverId);
    
    // Format shipments EXACTLY like routes/show.blade.php
    @php
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
    @endphp
    window.routeShipments = @json($shipmentsArray);
    
    // Debug: Log data availability
    console.log('Driver Dashboard - Route Data:', {
        hasActiveRoute: @json($activeRoute ? true : false),
        routeId: window.routeId,
        routeOriginLat: window.routeOriginLat,
        routeOriginLng: window.routeOriginLng,
        driverLat: window.driverCurrentLat,
        driverLng: window.driverCurrentLng,
        shipmentsCount: window.routeShipments ? window.routeShipments.length : 0,
        shipments: window.routeShipments
    });
    
    // Also keep deliveryLocations for backward compatibility
    @php
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
    @endphp
    window.deliveryLocations = @json($deliveryLocationsArray);

    // Helper function to validate coordinates (must be global to be used in watchPosition)
    function isValidCoordinate(value) {
        return value !== null && value !== undefined && !isNaN(value) && isFinite(value);
    }

    function updateShipmentStatus(shipmentId, status) {
        currentShipmentId = shipmentId;
        currentStatus = status;
        document.getElementById('modalShipmentId').value = shipmentId;
        document.getElementById('modalStatus').value = status;
        
        const modalTitle = document.getElementById('modalTitleText');
        const photoLabel = document.getElementById('photoLabelText');
        const notesLabel = document.getElementById('notesLabelText');
        const notesInput = document.getElementById('statusNotes');
        const exceptionSec = document.getElementById('exception-reason-section');
        const sigSection = document.getElementById('signature-section');
        
        if (status === 'delivered') {
            if (modalTitle) modalTitle.innerHTML = '<i class="fas fa-check-circle" style="color: #4caf50;"></i> Confirmar Entrega Realizada';
            if (photoLabel) photoLabel.innerHTML = '📸 Foto da Entrega / Comprovante (Obrigatório) *';
            if (notesLabel) notesLabel.innerHTML = 'Observações da Entrega (Opcional)';
            if (notesInput) {
                notesInput.placeholder = 'Comentários adicionais sobre a entrega...';
                notesInput.required = false;
            }
            if (exceptionSec) exceptionSec.style.display = 'none';
            if (sigSection) {
                sigSection.style.display = 'block';
                setTimeout(resizeCanvas, 50);
            }
        } else if (status === 'exception') {
            if (modalTitle) modalTitle.innerHTML = '<i class="fas fa-exclamation-triangle" style="color: #f44336;"></i> Registrar Não Entrega / Ocorrência';
            if (photoLabel) photoLabel.innerHTML = '📸 Foto da Ocorrência / Fachada (Obrigatório) *';
            if (notesLabel) notesLabel.innerHTML = 'Justificativa Detalhada (Obrigatório) *';
            if (notesInput) {
                notesInput.placeholder = 'Descreva com detalhes o motivo pelo qual a entrega não pôde ser realizada...';
                notesInput.required = true;
            }
            if (exceptionSec) exceptionSec.style.display = 'block';
            if (sigSection) sigSection.style.display = 'none';
        }
        
        document.getElementById('statusModal').classList.add('active');
    }

    function showExceptionModal(shipmentId) {
        updateShipmentStatus(shipmentId, 'exception');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.getElementById('photoPreview').style.display = 'none';
        document.getElementById('proofPhoto').value = '';
        if (window.signaturePad) window.signaturePad.clear();
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

    async function submitStatusUpdate(event) {
        event.preventDefault();
        
        const photoFileInput = document.getElementById('proofPhoto');
        if (!photoFileInput || !photoFileInput.files || !photoFileInput.files[0]) {
            alert('⚠️ A foto é obrigatória! Por favor, tire ou selecione uma foto de comprovante/ocorrência.');
            return;
        }

        const submitBtn = document.getElementById('submitStatusBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
        }
        
        const formEl = event.target;
        const formData = new FormData(formEl);
        const shipmentId = formData.get('shipment_id');
        const status = formData.get('status');
        const rawNotes = formData.get('notes') || '';
        const exceptionReason = formData.get('exception_reason') || '';
        const recipientName = formData.get('recipient_name') || '';
        const recipientDocument = formData.get('recipient_document') || '';

        if (status === 'exception' && !rawNotes && !exceptionReason) {
            alert('⚠️ Por favor, forneça o motivo e a justificativa da não entrega.');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Confirmar';
            }
            return;
        }

        const notes = status === 'exception' ? `[${exceptionReason}] ${rawNotes}` : rawNotes;
        
        let signatureData = null;
        if (status === 'delivered' && window.signaturePad && !window.signaturePad.isEmpty()) {
            signatureData = window.signaturePad.toDataURL('image/png');
        }
        
        // Get current geolocation coordinates
        let lat = 0.00000000;
        let lng = 0.00000000;
        let accuracy = null;
        
        try {
            const position = await new Promise((resolve, reject) => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 6000,
                        maximumAge: 0
                    });
                } else {
                    reject(new Error("Não suportado"));
                }
            });
            lat = position.coords.latitude;
            lng = position.coords.longitude;
            accuracy = position.coords.accuracy;
        } catch (e) {
            console.warn('Geolocation not obtained, checking window caches', e);
            if (window.driverCurrentLat && window.driverCurrentLng) {
                lat = window.driverCurrentLat;
                lng = window.driverCurrentLng;
            }
        }
        
        // Compress proof photo if attached
        let compressedPhotoBase64 = null;
        if (photoFileInput && photoFileInput.files && photoFileInput.files[0]) {
            try {
                if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Otimizando Foto...';
                compressedPhotoBase64 = await compressPhoto(photoFileInput.files[0]);
            } catch (err) {
                console.error("Erro na compressão Canvas. Usando fallback bruto.", err);
                try {
                    compressedPhotoBase64 = await new Promise((res, rej) => {
                        const reader = new FileReader();
                        reader.onload = e => res(e.target.result);
                        reader.onerror = rej;
                        reader.readAsDataURL(photoFileInput.files[0]);
                    });
        // Prepare offline-friendly packet
        const updatePayload = {
            shipment_id: shipmentId,
            status: status,
            notes: notes,
            recipient_name: recipientName,
            recipient_document: recipientDocument,
            recipient_signature: signatureData,
            photo: compressedPhotoBase64,
            latitude: lat,
            longitude: lng,
            accuracy: accuracy,
            completed_at_offline: new Date().toISOString()
        };
        
        // Force offline routing if strictly offline
        if (!navigator.onLine) {
            await handleOfflineSave(updatePayload);
            return;
        }
        
        // Try sending online
        try {
            if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            
            const submissionFormData = new FormData();
            submissionFormData.append('shipment_id', shipmentId);
            submissionFormData.append('status', status);
            submissionFormData.append('notes', notes);
            submissionFormData.append('recipient_name', recipientName);
            submissionFormData.append('recipient_document', recipientDocument);
            submissionFormData.append('latitude', lat);
            submissionFormData.append('longitude', lng);
            if (accuracy) submissionFormData.append('accuracy', accuracy);
            
            if (signatureData) {
                submissionFormData.append('recipient_signature', signatureData);
            }
            
            if (compressedPhotoBase64) {
                const photoBlob = dataURLtoBlob(compressedPhotoBase64);
                submissionFormData.append('photo', photoBlob, 'photo.jpg');
            }
            
            await performOnlineSubmit(submissionFormData, shipmentId);
        } catch (error) {
            console.warn("Falha de rede ao transmitir status. Salvando na fila local offline.", error);
            await handleOfflineSave(updatePayload);
        }
    }
    // IndexedDB & Offline Queue Management
    // ========================================================
    const DB_NAME = 'thiga_driver_pwa';
    const DB_VERSION = 1;
    const STORE_NAME = 'offline_status_updates';

    function getDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);
            request.onupgradeneeded = function(e) {
                const db = e.target.result;
                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    db.createObjectStore(STORE_NAME, { keyPath: 'shipment_id' });
                }
            };
            request.onsuccess = function(e) {
                resolve(e.target.result);
            };
            request.onerror = function(e) {
                reject(e.target.error);
            };
        });
    }

    async function saveOfflineUpdate(update) {
        const db = await getDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            const request = store.put(update);
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async function getOfflineUpdates() {
        const db = await getDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async function deleteOfflineUpdate(shipmentId) {
        const db = await getDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            const request = store.delete(shipmentId);
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    // ========================================================
    // Canvas Client-Side Image Compression
    // ========================================================
    function compressPhoto(file, maxWidth = 1280, quality = 0.8) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    
                    if (width > maxWidth) {
                        height = Math.round((height * maxWidth) / width);
                        width = maxWidth;
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    // Get base64 DataURL (image/jpeg)
                    const compressedBase64 = canvas.toDataURL('image/jpeg', quality);
                    resolve(compressedBase64);
                };
                img.onerror = function() {
                    reject(new Error("Erro ao renderizar imagem no Canvas para compressão"));
                };
                img.src = e.target.result;
            };
            reader.onerror = function() {
                reject(new Error("Erro ao carregar arquivo de foto"));
            };
            reader.readAsDataURL(file);
        });
    }

    function dataURLtoBlob(dataurl) {
        var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
        while(n--){
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], {type:mime});
    }

    // ========================================================
    // Status Submit Handlers (Offline & Online Resiliency)
    // ========================================================
    async function submitStatusUpdate(event) {
        event.preventDefault();
        
        const submitBtn = document.getElementById('submitStatusBtn');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            submitBtn.disabled = true;
        }
        
        const formEl = event.target;
        const formData = new FormData(formEl);
        const shipmentId = formData.get('shipment_id');
        const status = formData.get('status');
        const notes = formData.get('notes') || '';
        const recipientName = formData.get('recipient_name') || '';
        const recipientDocument = formData.get('recipient_document') || '';
        
        let signatureData = null;
        if (status === 'delivered' && window.signaturePad && !window.signaturePad.isEmpty()) {
            signatureData = window.signaturePad.toDataURL('image/png');
        }
        
        // Get current geolocation coordinates
        let lat = 0.00000000;
        let lng = 0.00000000;
        let accuracy = null;
        
        try {
            const position = await new Promise((resolve, reject) => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    });
                } else {
                    reject(new Error("Não suportado"));
                }
            });
            lat = position.coords.latitude;
            lng = position.coords.longitude;
            accuracy = position.coords.accuracy;
        } catch (e) {
            console.warn('Geolocation not obtained, checking window caches', e);
            if (window.driverCurrentLat && window.driverCurrentLng) {
                lat = window.driverCurrentLat;
                lng = window.driverCurrentLng;
            }
        }
        
        // Compress proof photo if attached
        const photoFileInput = document.getElementById('proofPhoto');
        let compressedPhotoBase64 = null;
        if (photoFileInput && photoFileInput.files && photoFileInput.files[0]) {
            try {
                if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Comprimindo Foto...';
                compressedPhotoBase64 = await compressPhoto(photoFileInput.files[0]);
            } catch (err) {
                console.error("Erro na compressão Canvas. Usando fallback bruto.", err);
                try {
                    compressedPhotoBase64 = await new Promise((res, rej) => {
                        const reader = new FileReader();
                        reader.onload = e => res(e.target.result);
                        reader.onerror = rej;
                        reader.readAsDataURL(photoFileInput.files[0]);
                    });
                } catch(e2) {
                    console.error("Erro crítico ao ler arquivo:", e2);
                }
            }
        }
        
        // Prepare offline-friendly packet
        const updatePayload = {
            shipment_id: shipmentId,
            status: status,
            notes: notes,
            recipient_name: recipientName,
            recipient_document: recipientDocument,
            recipient_signature: signatureData,
            photo: compressedPhotoBase64,
            latitude: lat,
            longitude: lng,
            accuracy: accuracy,
            completed_at_offline: new Date().toISOString()
        };
        
        // Force offline routing if strictly offline
        if (!navigator.onLine) {
            await handleOfflineSave(updatePayload);
            return;
        }
        
        // Try sending online
        try {
            if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            
            const submissionFormData = new FormData();
            submissionFormData.append('shipment_id', shipmentId);
            submissionFormData.append('status', status);
            submissionFormData.append('notes', notes);
            submissionFormData.append('recipient_name', recipientName);
            submissionFormData.append('recipient_document', recipientDocument);
            submissionFormData.append('latitude', lat);
            submissionFormData.append('longitude', lng);
            if (accuracy) submissionFormData.append('accuracy', accuracy);
            
            if (signatureData) {
                submissionFormData.append('recipient_signature', signatureData);
            }
            
            if (compressedPhotoBase64) {
                const photoBlob = dataURLtoBlob(compressedPhotoBase64);
                submissionFormData.append('photo', photoBlob, 'photo.jpg');
            }
            
            await performOnlineSubmit(submissionFormData, shipmentId);
        } catch (error) {
            console.warn("Falha de rede ao transmitir status. Salvando na fila local offline.", error);
            await handleOfflineSave(updatePayload);
        }
    }

    async function performOnlineSubmit(formData, shipmentId) {
        const response = await fetch(`/driver/shipments/${shipmentId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: formData
        });
        
        const contentType = response.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');
        
        if (!response.ok) {
            if (isJson) {
                const errorData = await response.json();
                throw new Error(errorData.error || errorData.message || 'Erro de validação no servidor');
            } else {
                throw new Error('Servidor retornou um erro inesperado.');
            }
        }
        
        if (!isJson) {
            throw new Error('Resposta inválida do servidor.');
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        if (data.message) {
            alert(data.message);
            setTimeout(() => {
                window.location.reload(true);
            }, 500);
        } else if (data.error || data.message) {
            alert('Erro ao atualizar status: ' + (data.message || data.error || 'Erro desconhecido'));
        } else {
            console.error('Unexpected response format:', data);
            alert('Erro ao atualizar status: Resposta inesperada do servidor');
        }
    }

    async function handleOfflineSave(payload) {
        try {
            await saveOfflineUpdate(payload);
            console.log("Status armazenado localmente no IndexedDB:", payload);
            
            closeModal('statusModal');
            updateShipmentDOMOffline(payload.shipment_id, payload.status);
            await updateOfflineBanner();
            
            showPremiumToast("Entrega salva localmente! Sincronizará quando houver internet.");
        } catch (e) {
            console.error("Falha ao persistir no IndexedDB:", e);
            alert("Erro ao gravar dados localmente. Por favor, libere espaço de armazenamento.");
            
            const submitBtn = document.getElementById('submitStatusBtn');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Confirmar';
                submitBtn.disabled = false;
            }
        }
    }

    // ========================================================
    // UI Synchronized Offline States
    // ========================================================
    function updateShipmentDOMOffline(shipmentId, status) {
        const card = document.querySelector(`.shipment-card[data-shipment-id="${shipmentId}"]`);
        if (!card) return;
        
        // Remove interactive action buttons
        const actions = card.querySelector('.shipment-actions');
        if (actions) {
            actions.innerHTML = '';
        }
        
        // Reflect status in custom orange pending sync badge
        const badge = card.querySelector('.status-badge');
        if (badge) {
            badge.className = 'status-badge pending-sync';
            badge.style.cssText = 'background-color: rgba(var(--cor-acento-rgb), 0.2); color: var(--cor-acento); border: 2px solid var(--cor-acento); border-radius: 4px;';
            badge.innerHTML = '<i class="fas fa-clock fa-spin"></i> Sincronização Pendente';
        }
    }

    async function updateOfflineBanner() {
        const banner = document.getElementById('offline-sync-banner');
        if (!banner) return;
        
        try {
            const updates = await getOfflineUpdates();
            const count = updates.length;
            
            if (count > 0) {
                banner.style.display = 'flex';
                
                const textEl = document.getElementById('offline-banner-text');
                const btnEl = document.getElementById('offline-sync-btn');
                const iconEl = document.getElementById('offline-banner-icon');
                
                if (navigator.onLine) {
                    textEl.innerHTML = `Você possui <strong>${count}</strong> entrega(s) pendente(s) de envio.`;
                    btnEl.style.display = 'flex';
                    btnEl.disabled = false;
                    btnEl.innerHTML = '<i class="fas fa-sync"></i> Sincronizar Agora';
                    iconEl.className = 'fas fa-wifi';
                } else {
                    textEl.innerHTML = `Modo Offline: <strong>${count}</strong> entrega(s) salvas no dispositivo.`;
                    btnEl.style.display = 'none';
                    iconEl.className = 'fas fa-wifi-slash animate-pulse';
                }
            } else {
                banner.style.display = 'none';
            }
        } catch (e) {
            console.error("Erro ao gerenciar banner offline:", e);
        }
    }

    // ========================================================
    // Queue Synchronization Processing Loop
    // ========================================================
    let isSyncing = false;
    
    async function startManualSync() {
        if (isSyncing) return;
        if (!navigator.onLine) {
            showPremiumToast("Você continua sem conexão com a internet!");
            return;
        }
        
        const btnEl = document.getElementById('offline-sync-btn');
        if (btnEl) {
            btnEl.disabled = true;
            btnEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sincronizando...';
        }
        
        await syncOfflineUpdates();
    }
    
    async function syncOfflineUpdates() {
        if (isSyncing) return;
        isSyncing = true;
        
        try {
            const updates = await getOfflineUpdates();
            if (updates.length === 0) {
                isSyncing = false;
                await updateOfflineBanner();
                return;
            }
            
            console.log(`Iniciando envio em segundo plano de ${updates.length} itens do IndexedDB...`);
            
            for (let update of updates) {
                try {
                    const submissionFormData = new FormData();
                    submissionFormData.append('shipment_id', update.shipment_id);
                    submissionFormData.append('status', update.status);
                    submissionFormData.append('notes', update.notes);
                    submissionFormData.append('recipient_name', update.recipient_name);
                    submissionFormData.append('recipient_document', update.recipient_document);
                    submissionFormData.append('latitude', update.latitude);
                    submissionFormData.append('longitude', update.longitude);
                    if (update.accuracy) submissionFormData.append('accuracy', update.accuracy);
                    if (update.completed_at_offline) {
                        submissionFormData.append('completed_at_offline', update.completed_at_offline);
                    }
                    
                    if (update.recipient_signature) {
                        submissionFormData.append('recipient_signature', update.recipient_signature);
                    }
                    
                    if (update.photo) {
                        const photoBlob = dataURLtoBlob(update.photo);
                        submissionFormData.append('photo', photoBlob, 'photo.jpg');
                    }
                    
                    const response = await fetch(`/driver/shipments/${update.shipment_id}/status`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: submissionFormData
                    });
                    
                    if (!response.ok) {
                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.includes('application/json')) {
                            const errData = await response.json();
                            throw new Error(errData.error || errData.message || 'Erro do servidor');
                        } else {
                            throw new Error(`HTTP error ${response.status}`);
                        }
                    }
                    
                    await deleteOfflineUpdate(update.shipment_id);
                    console.log(`Shipment ${update.shipment_id} enviado e liberado da base local.`);
                    
                } catch (shipmentErr) {
                    console.error(`Falha no item ${update.shipment_id}:`, shipmentErr);
                }
            }
            
            const remaining = await getOfflineUpdates();
            if (remaining.length === 0) {
                showPremiumToast("Todas as entregas offline foram sincronizadas!");
                setTimeout(() => {
                    window.location.reload(true);
                }, 1500);
            } else {
                showPremiumToast(`Sincronização parcial realizada. Restam ${remaining.length} pendências.`);
                await updateOfflineBanner();
            }
            
        } catch (e) {
            console.error("Erro geral na sincronização offline:", e);
        } finally {
            isSyncing = false;
        }
    }

    // ========================================================
    // Premium Toast & Network Listeners
    // ========================================================
    function showPremiumToast(message) {
        const existing = document.querySelector('.premium-system-toast');
        if (existing) existing.remove();
        
        const toast = document.createElement('div');
        toast.className = 'premium-system-toast';
        toast.style.cssText = `
            position: fixed;
            bottom: 90px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(var(--cor-secundaria-rgb), 0.96);
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 4px;
            border-left: 4px solid var(--cor-acento);
            box-shadow: 0 4px 15px rgba(0,0,0,0.45);
            z-index: 10000;
            width: 90%;
            max-width: 400px;
            font-size: 0.9em;
            font-weight: 600;
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideUpToast 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        `;
        toast.innerHTML = `
            <div style="flex-grow: 1; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-info-circle" style="color: var(--cor-acento);"></i>
                <span>${message}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; padding: 5px; cursor: pointer; opacity: 0.8;">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        document.body.appendChild(toast);
        
        if (navigator.vibrate) {
            navigator.vibrate(80);
        }
        
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 4500);
    }

    window.addEventListener('online', () => {
        console.log("OnLine: Conexão detectada!");
        showPremiumToast("Sinal de internet detectado! Sincronizando entregas pendentes...");
        setTimeout(syncOfflineUpdates, 2000);
    });
    
    window.addEventListener('offline', () => {
        console.log("OffLine: Conexão interrompida!");
        showPremiumToast("Sem internet! Operando no modo offline resiliente.");
        updateOfflineBanner();
    });
    
    // Auto-check for sync every 20 seconds if onLine
    setInterval(() => {
        if (navigator.onLine && !isSyncing) {
            getOfflineUpdates().then(updates => {
                if (updates.length > 0) {
                    syncOfflineUpdates();
                }
            });
        }
    }, 20000);

    function startRoute(routeId) {
        if (confirm('Deseja iniciar esta rota?')) {
            fetch(`/driver/routes/${routeId}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
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

    // Realtime GPS Tracking System with localStorage persistence & 15s safety interval
    let gpsWatchId = null;
    let gpsIntervalId = null;
    let lastKnownPosition = null;

    function sendGpsLocation(position) {
        if (!position || !position.coords) return;
        lastKnownPosition = position;
        const routeId = window.routeId || null;
        const lat = parseFloat(position.coords.latitude);
        const lng = parseFloat(position.coords.longitude);
        const accuracy = position.coords.accuracy || 0;

        fetch('/driver/location/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                latitude: lat,
                longitude: lng,
                accuracy: accuracy,
                route_id: routeId,
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('GPS Location updated on server:', data);
            const lastSentElem = document.getElementById('gps-last-sent');
            if (lastSentElem) {
                lastSentElem.textContent = 'Enviado às ' + new Date().toLocaleTimeString('pt-BR');
            }
        })
        .catch(err => {
            console.error('Error updating GPS location:', err);
        });

        // Also update local variables for route map if available
        window.driverCurrentLat = lat;
        window.driverCurrentLng = lng;
        if (window.driverMarker && window.routeMap && typeof window.routeMap.updateMarker === 'function') {
            window.routeMap.updateMarker(window.driverMarker, { lat: lat, lng: lng });
        }
    }

    function centerMapOnDriver() {
        if (window.driverCurrentLat && window.driverCurrentLng && window.routeMap) {
            if (typeof window.routeMap.setCenter === 'function') {
                window.routeMap.setCenter([parseFloat(window.driverCurrentLng), parseFloat(window.driverCurrentLat)], 15);
            } else if (typeof window.routeMap.flyTo === 'function') {
                window.routeMap.flyTo({ center: [parseFloat(window.driverCurrentLng), parseFloat(window.driverCurrentLat)], zoom: 15 });
            }
        } else if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                window.driverCurrentLat = lat;
                window.driverCurrentLng = lng;
                if (window.routeMap) {
                    if (typeof window.routeMap.setCenter === 'function') {
                        window.routeMap.setCenter([lng, lat], 15);
                    } else if (typeof window.routeMap.flyTo === 'function') {
                        window.routeMap.flyTo({ center: [lng, lat], zoom: 15 });
                    }
                }
            }, function(err) {
                console.warn('Geolocation position error:', err);
            }, { enableHighAccuracy: true });
        }
    }

    function startGpsTracking() {
        if (!navigator.geolocation) {
            console.warn('Geolocalização não é suportada por este navegador.');
            return;
        }

        const statusText = document.getElementById('gps-status-text');
        const detailsInfo = document.getElementById('gps-details-info');
        if (statusText) {
            statusText.textContent = 'Transmissão em tempo real ATIVADA';
            statusText.style.color = '#4caf50';
        }
        if (detailsInfo) detailsInfo.style.display = 'block';

        // 1. Watch position
        if (gpsWatchId === null) {
            gpsWatchId = navigator.geolocation.watchPosition(
                sendGpsLocation,
                function(err) { console.warn('GPS Watch error:', err); },
                { enableHighAccuracy: true, maximumAge: 10000, timeout: 15000 }
            );
        }

        // 2. Safety fallback interval every 15s
        if (gpsIntervalId === null) {
            gpsIntervalId = setInterval(function() {
                if (lastKnownPosition) {
                    sendGpsLocation(lastKnownPosition);
                } else if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(sendGpsLocation, function(err) {
                        console.warn('GPS interval getCurrentPosition error:', err);
                    }, { enableHighAccuracy: true, timeout: 10000 });
                }
            }, 15000);
        }
    }

    function stopGpsTracking() {
        if (gpsWatchId !== null) {
            navigator.geolocation.clearWatch(gpsWatchId);
            gpsWatchId = null;
        }
        if (gpsIntervalId !== null) {
            clearInterval(gpsIntervalId);
            gpsIntervalId = null;
        }
        const statusText = document.getElementById('gps-status-text');
        const detailsInfo = document.getElementById('gps-details-info');
        if (statusText) {
            statusText.textContent = 'Transmissão de localização em tempo real desativada';
            statusText.style.color = 'rgba(245, 245, 245, 0.7)';
        }
        if (detailsInfo) detailsInfo.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const gpsToggle = document.getElementById('gps-toggle-switch');
        if (gpsToggle) {
            // Restore persisted state from localStorage.
            // Default: ON if already used once (driver_gps_enabled was stored), or if there is an active route.
            const storedState = localStorage.getItem('driver_gps_enabled');
            const hasActiveRoute = {{ $activeRoute ? 'true' : 'false' }};
            // If never set, default ON when active route or not explicitly disabled
            const isEnabled = storedState === null ? true : (storedState === 'true');
            gpsToggle.checked = isEnabled;

            // Update visual state of the slider
            updateGpsToggleVisual(isEnabled);

            if (isEnabled) {
                startGpsTracking();
            }

            gpsToggle.addEventListener('change', function() {
                localStorage.setItem('driver_gps_enabled', this.checked);
                updateGpsToggleVisual(this.checked);
                if (this.checked) {
                    startGpsTracking();
                    // Also update gps-status-text and gps-details-info
                    const statusText = document.getElementById('gps-status-text');
                    const detailsInfo = document.getElementById('gps-details-info');
                    if (statusText) statusText.textContent = 'Transmitindo localização em tempo real...';
                    if (detailsInfo) detailsInfo.style.display = 'block';
                } else {
                    stopGpsTracking();
                    const statusText = document.getElementById('gps-status-text');
                    const detailsInfo = document.getElementById('gps-details-info');
                    if (statusText) statusText.textContent = 'Transmissão de localização em tempo real desativada';
                    if (detailsInfo) detailsInfo.style.display = 'none';
                }
            });

            // Initialize the visual state immediately
            const statusText = document.getElementById('gps-status-text');
            const detailsInfo = document.getElementById('gps-details-info');
            if (isEnabled) {
                if (statusText) statusText.textContent = 'Transmitindo localização em tempo real...';
                if (detailsInfo) detailsInfo.style.display = 'block';
            }
        }
    });

    function updateGpsToggleVisual(enabled) {
        const slider = document.querySelector('.gps-slider');
        const knob = document.querySelector('.gps-knob');
        if (enabled) {
            if (slider) { slider.style.backgroundColor = '#4caf50'; slider.style.borderColor = '#4caf50'; }
            if (knob) knob.style.transform = 'translateX(26px)';
        } else {
            if (slider) { slider.style.backgroundColor = 'rgba(255,255,255,0.25)'; slider.style.borderColor = 'rgba(255,255,255,0.2)'; }
            if (knob) knob.style.transform = 'translateX(0px)';
        }
    }
            
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
            let message = 'Erro ao obter localização. Verifique o GPS.';
            if (error.code === 1) {
                console.warn('Geolocation permission denied');
                message = 'Permissão de GPS negada. Por favor, libere nas configurações do Navegador/App para continuarmos rasteando.';
            } else if (error.code === 2) {
                console.warn('Geolocation position unavailable');
                message = 'GPS Indisponível (Sem Sinal). Verifique se a localização está ativada no seu aparelho.';
            } else if (error.code === 3) {
                console.warn('Geolocation timeout');
                message = 'Tempo esgotado ao tentar ler GPS. Tentando novamente em background...';
            }
            showGpsErrorAlert(message);
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

    // Show GPS Error Alert
    let lastGpsAlertTime = 0;
    function showGpsErrorAlert(message) {
        const now = Date.now();
        // Only show once every 3 minutes to avoid spamming
        if (now - lastGpsAlertTime < 180000) return;
        lastGpsAlertTime = now;

        const existing = document.querySelector('.gps-error-alert');
        if (existing) existing.remove();

        const alert = document.createElement('div');
        alert.className = 'gps-error-alert';
        alert.style.cssText = `
            position: fixed;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            background: #f44336;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.4);
            z-index: 10000;
            width: 90%;
            max-width: 400px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.9em;
            animation: slideDown 0.3s ease-out;
        `;
        alert.innerHTML = `
            <div style="flex-grow: 1;">
                <i class="fas fa-satellite-dish" style="margin-right: 8px;"></i>
                <strong>ATENÇÃO:</strong> ${message}
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; padding: 5px; margin-left: 10px; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        `;
        document.body.appendChild(alert);
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
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
        const driverLat = @json($driver->current_latitude ?? null);
        const driverLng = @json($driver->current_longitude ?? null);
        @php
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
        @endphp
        const deliveryLocations = @json($proximityLocationsArray);

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

    let signaturePad;

    function resizeCanvas() {
        const canvas = document.getElementById('signature-pad');
        if (!canvas) return;
        const ratio =  Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        if (window.signaturePad) window.signaturePad.clear();
    }

    // Initialize navigation app preference on page load
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signature-pad');
        if (canvas) {
            window.signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });
            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();
        }

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
        
        // Check offline items in IndexedDB and reflect in UI
        if (typeof getOfflineUpdates === 'function') {
            getOfflineUpdates().then(updates => {
                updates.forEach(u => {
                    updateShipmentDOMOffline(u.shipment_id, u.status);
                });
                updateOfflineBanner();
            }).catch(e => {
                console.error("Erro ao ler IndexedDB na inicialização:", e);
            });
        }
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

    // Initialize map when page loads — wait for MapboxHelper to be ready (retry up to 5s)
    function tryInitRouteMap(attempts) {
        const mapContainer = document.getElementById('route-map');
        if (!mapContainer) return;
        if (window.routeMapInitialized) return;

        if (typeof MapboxHelper !== 'undefined' && window.mapboxAccessToken) {
            if (!window.routeMapInitializing) {
                window.routeMapInitializing = true;
                initRouteMap();
            }
        } else if (attempts > 0) {
            setTimeout(() => tryInitRouteMap(attempts - 1), 500);
        } else {
            // Fallback after all retries
            if (mapContainer) {
                mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: rgba(255,255,255,0.6);"><i class="fas fa-map-marked-alt" style="font-size: 2em; margin-bottom: 10px;"></i><br>Mapa indisponível momentaneamente.<br><small>Recarregue a página para tentar novamente.</small></div>';
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => tryInitRouteMap(10), 300);
        });
    } else {
        setTimeout(() => tryInitRouteMap(10), 300);
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

    // Route History Functions
    let historyOffset = {{ isset($routeHistory) ? $routeHistory->count() : 0 }};
    const historyLimit = 10;

    function loadMoreHistory() {
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.disabled = true;
            loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Carregando...';
        }

        fetch(`/driver/route-history?limit=${historyLimit}&offset=${historyOffset}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.routes && data.routes.length > 0) {
                appendHistoryItems(data.routes);
                historyOffset += data.routes.length;
                
                // Hide button if no more items
                if (historyOffset >= data.total) {
                    if (loadMoreBtn) loadMoreBtn.style.display = 'none';
                } else {
                    if (loadMoreBtn) {
                        loadMoreBtn.disabled = false;
                        loadMoreBtn.innerHTML = '<i class="fas fa-chevron-down"></i> Carregar Mais';
                    }
                }
            } else {
                if (loadMoreBtn) loadMoreBtn.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading more history:', error);
            if (loadMoreBtn) {
                loadMoreBtn.disabled = false;
                loadMoreBtn.innerHTML = '<i class="fas fa-chevron-down"></i> Carregar Mais';
            }
        });
    }

    function appendHistoryItems(routes) {
        const timeline = document.querySelector('.timeline');
        if (!timeline) return;

        routes.forEach(route => {
            const timelineItem = createHistoryItem(route);
            timeline.appendChild(timelineItem);
        });
    }

    function createHistoryItem(route) {
        const item = document.createElement('div');
        item.className = 'timeline-item';
        
        const badgeColor = route.efficiency_badge_color === 'green' ? '#4caf50' : 
                          (route.efficiency_badge_color === 'blue' ? '#2196F3' : 
                          (route.efficiency_badge_color === 'yellow' ? '#ffc107' : '#f44336'));
        
        const badgeBg = route.efficiency_badge_color === 'green' ? 'rgba(76, 175, 80, 0.2)' : 
                       (route.efficiency_badge_color === 'blue' ? 'rgba(33, 150, 243, 0.2)' : 
                       (route.efficiency_badge_color === 'yellow' ? 'rgba(255, 193, 7, 0.2)' : 'rgba(244, 67, 54, 0.2)'));
        
        const badgeBorder = route.efficiency_badge_color === 'green' ? '#4caf50' : 
                           (route.efficiency_badge_color === 'blue' ? '#2196F3' : 
                           (route.efficiency_badge_color === 'yellow' ? '#ffc107' : '#f44336'));

        let achievementsHtml = '';
        if (route.achievements && route.achievements.length > 0) {
            achievementsHtml = `
                <div class="achievements-section">
                    <strong style="color: var(--cor-acento); font-size: 0.9em; display: block; margin-bottom: 8px;">
                        <i class="fas fa-trophy"></i> Conquistas:
                    </strong>
                    <div class="achievements-list">
                        ${route.achievements.map(badge => {
                            const badgeColors = {
                                'green': { bg: 'rgba(76, 175, 80, 0.2)', border: '#4caf50' },
                                'blue': { bg: 'rgba(33, 150, 243, 0.2)', border: '#2196F3' },
                                'gold': { bg: 'rgba(255, 215, 0, 0.2)', border: '#FFD700' },
                                'purple': { bg: 'rgba(156, 39, 176, 0.2)', border: '#9c27b0' },
                                'orange': { bg: 'rgba(255, 152, 0, 0.2)', border: '#FF9800' }
                            };
                            const colors = badgeColors[badge.color] || badgeColors.blue;
                            return `<span class="achievement-badge" style="background: ${colors.bg}; border: 1px solid ${colors.border};">
                                <i class="fas fa-${badge.icon}"></i> ${badge.label}
                            </span>`;
                        }).join('')}
                    </div>
                </div>
            `;
        }

        let profitHtml = '';
        if (route.net_profit > 0) {
            profitHtml = `
                <div class="route-profit" style="margin-top: 10px; padding: 10px; background: rgba(76, 175, 80, 0.1); border-radius: 8px; border-left: 3px solid #4caf50;">
                    <strong style="color: #4caf50;">
                        <i class="fas fa-dollar-sign"></i> Lucro: R$ ${parseFloat(route.net_profit).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </strong>
                </div>
            `;
        }

        item.innerHTML = `
            <div class="timeline-marker" style="background: ${badgeColor};">
                <i class="fas fa-route"></i>
            </div>
            <div class="timeline-content">
                <div class="route-history-card">
                    <div class="route-history-header-card">
                        <div>
                            <h4>${route.route_name}</h4>
                            <p class="route-date">
                                <i class="fas fa-calendar"></i> ${route.formatted_date}
                            </p>
                        </div>
                        <div class="efficiency-badge" style="background: ${badgeBg}; border: 2px solid ${badgeBorder};">
                            <span style="font-size: 1.2em; font-weight: 700;">${Math.round(route.efficiency_score || 0)}</span>
                            <span style="font-size: 0.8em; opacity: 0.8;">pontos</span>
                        </div>
                    </div>
                    
                    <div class="route-stats-grid">
                        <div class="route-stat">
                            <i class="fas fa-route"></i>
                            <div>
                                <span class="stat-label">Distância</span>
                                <span class="stat-value">${route.distance}</span>
                            </div>
                        </div>
                        <div class="route-stat">
                            <i class="fas fa-clock"></i>
                            <div>
                                <span class="stat-label">Duração</span>
                                <span class="stat-value">${route.duration}</span>
                            </div>
                        </div>
                        <div class="route-stat">
                            <i class="fas fa-box"></i>
                            <div>
                                <span class="stat-label">Entregas</span>
                                <span class="stat-value">${route.delivered_shipments}/${route.total_shipments}</span>
                            </div>
                        </div>
                        <div class="route-stat">
                            <i class="fas fa-tachometer-alt"></i>
                            <div>
                                <span class="stat-label">Taxa de Sucesso</span>
                                <span class="stat-value">${Math.round(route.success_rate || 0)}%</span>
                            </div>
                        </div>
                    </div>
                    ${achievementsHtml}
                    ${profitHtml}
                </div>
            </div>
        `;

        return item;
    }

    // Show load more button if there are more items
    @if(isset($routeHistory) && $routeHistory->count() >= 10)
    document.addEventListener('DOMContentLoaded', function() {
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.style.display = 'block';
        }
    });
    @endif
    // ========================================================
    // Push Notification Registration
    // ========================================================
    async function initPushNotifications() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.log('Push notifications not supported');
            return;
        }

        try {
            const registration = await navigator.serviceWorker.register('/sw-push.js');
            console.log('Push SW registered:', registration.scope);

            // Check existing subscription
            const subscription = await registration.pushManager.getSubscription();
            updatePushButton(!!subscription);

            // Add button click handler
            const pushBtn = document.getElementById('push-notification-btn');
            if (pushBtn) {
                pushBtn.addEventListener('click', async () => {
                    const sub = await registration.pushManager.getSubscription();
                    if (sub) {
                        // Unsubscribe
                        await sub.unsubscribe();
                        await fetch('{{ route("driver.push.unsubscribe") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ endpoint: sub.endpoint }),
                        });
                        updatePushButton(false);
                    } else {
                        // Subscribe
                        let publicKey = null;
                        try {
                            const res = await fetch('{{ route("driver.push.vapid-key") }}');
                            const data = await res.json();
                            publicKey = data.publicKey;
                        } catch (e) {
                            console.warn('VAPID key fetch error, using server fallback key');
                        }

                        const keyToUse = publicKey || 'BKLXAlSPCOXKhKzMqDsj_NjobdO__j6HXn_gRPRsJmJitjlf3k1zwwswbhG6hBa-ILuYYg_UGvCekMbX4aeTAls';

                        const newSub = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(keyToUse),
                        });

                        await fetch('{{ route("driver.push.subscribe") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(newSub.toJSON()),
                        });
                        updatePushButton(true);
                    }
                });
            }
        } catch (err) {
            console.error('Push init error:', err);
        }
    }

    function updatePushButton(isSubscribed) {
        const btn = document.getElementById('push-notification-btn');
        if (!btn) return;
        const icon = btn.querySelector('i');
        const text = btn.querySelector('span');
        if (isSubscribed) {
            // Hide the button — notifications are already active
            btn.style.display = 'none';
        } else {
            btn.style.display = 'flex';
            btn.style.background = 'rgba(var(--cor-acento-rgb), 0.9)';
            if (icon) icon.className = 'fas fa-bell-slash';
            if (text) text.textContent = 'Ativar Notificações';
        }
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        return Uint8Array.from([...rawData].map(char => char.charCodeAt(0)));
    }

    // --- REAL-TIME GPS LOCATION TRACKING LOGIC ---
    let locationWatchId = null;
    let locationInterval = null;

    window.handleLocationToggle = function(enabled) {
        const iconBox = document.getElementById('location-icon-box');
        const icon = document.getElementById('location-status-icon');
        const desc = document.getElementById('location-status-desc');
        const track = document.getElementById('location-slider-track');
        const thumb = document.getElementById('location-slider-thumb');

        if (enabled) {
            if (track) track.style.backgroundColor = '#16a34a';
            if (thumb) thumb.style.transform = 'translateX(26px)';
            if (thumb) thumb.style.backgroundColor = '#ffffff';
            if (iconBox) {
                iconBox.style.background = 'rgba(34, 197, 94, 0.2)';
                iconBox.style.color = '#4ade80';
            }
            if (icon) icon.className = 'fas fa-satellite-dish fa-spin';
            if (desc) desc.innerHTML = '<b style="color: #4ade80;">Localização Ativa</b> — Posição em tempo real transmitida para o Mapa do Admin.';

            localStorage.setItem('driver_location_tracking_enabled', 'true');
            startLocationTracking();
        } else {
            if (track) track.style.backgroundColor = '#334155';
            if (thumb) thumb.style.transform = 'translateX(0px)';
            if (thumb) thumb.style.backgroundColor = '#94a3b8';
            if (iconBox) {
                iconBox.style.background = 'rgba(239, 68, 68, 0.2)';
                iconBox.style.color = '#f87171';
            }
            if (icon) icon.className = 'fas fa-satellite-dish';
            if (desc) desc.innerHTML = 'Localização desligada — Você não está visível no Mapa do Admin.';

            localStorage.setItem('driver_location_tracking_enabled', 'false');
            stopLocationTracking();
        }
    };

    function sendLocationToBackend(position) {
        if (!position || !position.coords) return;
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy || 0;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) return;

        fetch('{{ route("driver.location.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                latitude: lat,
                longitude: lng,
                accuracy: accuracy,
                route_id: {{ $activeRoute->id ?? 'null' }}
            })
        })
        .then(res => res.json())
        .then(data => {
            console.log('[GPS Admin Monitoring] Posição enviada ao backend:', lat, lng);
        })
        .catch(err => console.error('[GPS Admin Monitoring] Erro ao enviar posição:', err));
    }

    function startLocationTracking() {
        stopLocationTracking();

        if (!('geolocation' in navigator)) {
            alert('Seu dispositivo ou navegador não suporta geolocalização por GPS.');
            return;
        }

        // Send first location immediately
        navigator.geolocation.getCurrentPosition(
            sendLocationToBackend,
            (err) => console.warn('[GPS Tracking] Erro posição inicial:', err.message),
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );

        // Continuous watchPosition
        locationWatchId = navigator.geolocation.watchPosition(
            sendLocationToBackend,
            (err) => console.warn('[GPS Tracking] Erro watchPosition:', err.message),
            { enableHighAccuracy: true, timeout: 20000, maximumAge: 5000 }
        );

        // Interval fallback every 15 seconds
        locationInterval = setInterval(function() {
            navigator.geolocation.getCurrentPosition(
                sendLocationToBackend,
                null,
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 5000 }
            );
        }, 15000);
    }

    function stopLocationTracking() {
        if (locationWatchId !== null) {
            navigator.geolocation.clearWatch(locationWatchId);
            locationWatchId = null;
        }
        if (locationInterval !== null) {
            clearInterval(locationInterval);
            locationInterval = null;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initPushNotifications();

        // Restore Location Switch state on page load (using gps-toggle-switch — canonical GPS toggle ID)
        // Note: primary GPS init is handled above (gps-toggle-switch block)
        // This block handles the legacy location-toggle-input if it exists
        const legacyToggle = document.getElementById('location-toggle-input');
        if (legacyToggle) {
            const savedState = localStorage.getItem('driver_location_tracking_enabled');
            const shouldEnable = savedState === 'true' || (savedState === null && {{ $activeRoute ? 'true' : 'false' }});
            legacyToggle.checked = shouldEnable;
            window.handleLocationToggle(shouldEnable);
        }
    });
</script>

<!-- Push Notification Button (Floating) -->
<style>
    #push-notification-btn {
        position: fixed;
        bottom: 80px;
        right: 20px;
        z-index: 1000;
        background: rgba(var(--cor-acento-rgb), 0.9);
        color: #fff;
        border: none;
        border-radius: 50px;
        padding: 12px 20px;
        font-size: 0.85em;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    #push-notification-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0,0,0,0.4);
    }
</style>
<button id="push-notification-btn">
    <i class="fas fa-bell-slash"></i>
    <span>Ativar Notificações</span>
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const driverForm = document.getElementById('driver-ai-form');
    const driverInput = document.getElementById('driver-ai-input');
    const driverMessages = document.getElementById('driver-ai-messages');

    document.querySelectorAll('.driver-ai-chip').forEach(chip => {
        chip.addEventListener('click', function() {
            if (driverInput && driverForm) {
                driverInput.value = this.getAttribute('data-msg');
                driverForm.dispatchEvent(new Event('submit'));
            }
        });
    });

    if (driverForm) {
        driverForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const msg = driverInput.value.trim();
            if (!msg) return;

            appendDriverMsg('user', msg);
            driverInput.value = '';

            const loadingId = 'loading-' + Date.now();
            const loadDiv = document.createElement('div');
            loadDiv.id = loadingId;
            loadDiv.style.color = '#a78bfa';
            loadDiv.style.fontSize = '0.82em';
            loadDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando gasto e otimizando rota...';
            driverMessages.appendChild(loadDiv);
            driverMessages.scrollTop = driverMessages.scrollHeight;

            try {
                const res = await fetch('/driver/ai-copilot/query', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: msg })
                });
                const data = await res.json();
                const loadEl = document.getElementById(loadingId);
                if (loadEl) loadEl.remove();

                if (data.reply) {
                    appendDriverMsg('ai', data.reply);
                } else {
                    appendDriverMsg('ai', '⚠️ Não foi possível processar seu gasto.');
                }
            } catch (err) {
                const loadEl = document.getElementById(loadingId);
                if (loadEl) loadEl.remove();
                appendDriverMsg('ai', '❌ Erro de comunicação com o servidor.');
            }
        });
    }

    function appendDriverMsg(sender, text) {
        if (!driverMessages) return;
        const div = document.createElement('div');
        if (sender === 'user') {
            div.style.alignSelf = 'flex-end';
            div.style.background = 'var(--cor-acento)';
            div.style.color = 'var(--cor-principal)';
            div.style.fontWeight = '600';
            div.style.borderRadius = '10px 10px 2px 10px';
            div.style.padding = '8px 12px';
            div.style.fontSize = '0.85em';
            div.style.maxWidth = '85%';
            div.textContent = text;
        } else {
            div.style.alignSelf = 'flex-start';
            div.style.background = 'rgba(15, 23, 42, 0.9)';
            div.style.border = '1px solid rgba(139, 92, 246, 0.3)';
            div.style.color = '#e2e8f0';
            div.style.borderRadius = '10px 10px 10px 2px';
            div.style.padding = '10px 14px';
            div.style.fontSize = '0.85em';
            div.style.maxWidth = '90%';
            div.style.lineHeight = '1.4';

            let formatted = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                                .replace(/\n/g, '<br>');
            div.innerHTML = formatted;
        }
        driverMessages.appendChild(div);
        driverMessages.scrollTop = driverMessages.scrollHeight;
    }
});
</script>
@endpush