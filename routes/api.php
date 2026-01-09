<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AutomationController;
use App\Http\Controllers\Api\DriverAuthController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\McpFreightController;
use App\Http\Controllers\WebhookController;

// Public API routes
Route::prefix('v1')->group(function () {
    // Tracking API
    Route::get('/track-shipment', [TrackingController::class, 'track']);
    Route::get('/shipment-history', [TrackingController::class, 'history']);
    Route::get('/tracking/{trackingNumber}/timeline', [TrackingController::class, 'timeline']);
    
    // Delivery confirmation
    Route::post('/tracking/{trackingNumber}/confirm-delivery', [App\Http\Controllers\Api\DeliveryConfirmationController::class, 'confirm']);
});

// MCP Freight API (for n8n integration)
Route::prefix('mcp/freight')->group(function () {
    Route::get('/health', [McpFreightController::class, 'health']);
    Route::post('/calculate', [McpFreightController::class, 'calculate']);
    Route::get('/destinations', [McpFreightController::class, 'destinations']);
});

Route::post('/mcp/workflows/order', [AutomationController::class, 'order']);

// Webhook routes
Route::post('/webhooks/whatsapp', [WebhookController::class, 'whatsapp']);
Route::post('/webhooks/asaas', [WebhookController::class, 'asaas']);
Route::post('/webhooks/mitt', [WebhookController::class, 'mitt'])->name('webhooks.mitt');

// Driver API routes (for web and mobile app)
Route::prefix('driver')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/request-code', [DriverAuthController::class, 'requestCode']);
        Route::post('/verify-code', [DriverAuthController::class, 'verifyCode']);
    });

    Route::middleware(['auth:sanctum', 'driver.token'])->group(function () {
        Route::get('/route/active', [App\Http\Controllers\Api\DriverController::class, 'getActiveRoute']);
        Route::get('/shipments', [App\Http\Controllers\Api\DriverController::class, 'getShipments']);
        Route::post('/shipments/{shipmentId}/status', [App\Http\Controllers\Api\DriverController::class, 'updateShipmentStatus']);
        Route::post('/shipments/{shipmentId}/update-status', [App\Http\Controllers\Api\DriverController::class, 'updateShipmentStatus']); // Legacy route
        Route::post('/location/update', [App\Http\Controllers\Api\DriverController::class, 'updateLocation']);
        Route::get('/location/history', [App\Http\Controllers\Api\DriverController::class, 'getLocationHistory']);
    });
});

// Maps API routes moved to web.php for session authentication
// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
