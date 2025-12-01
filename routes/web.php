<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Settings\WhatsAppIntegrationController;

Route::get('/', function () {
    return view('welcome');
});

// Public tracking route
Route::get('/tracking/{trackingNumber}', [App\Http\Controllers\TrackingController::class, 'show'])->name('tracking.show');

// Rotas de autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Rotas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Subscription routes
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [App\Http\Controllers\SubscriptionController::class, 'index'])->name('index');
        Route::get('/plans/{plan}', [App\Http\Controllers\SubscriptionController::class, 'show'])->name('show');
        Route::post('/plans/{plan}/subscribe', [App\Http\Controllers\SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::get('/success', [App\Http\Controllers\SubscriptionController::class, 'success'])->name('success');
        Route::get('/{subscription}', [App\Http\Controllers\SubscriptionController::class, 'showSubscription'])->name('details');
        Route::post('/{subscription}/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('cancel');
    });
    
    // Company routes
    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/', [App\Http\Controllers\CompanyController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\CompanyController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\CompanyController::class, 'store'])->name('store');
        Route::get('/{company}', [App\Http\Controllers\CompanyController::class, 'show'])->name('show');
        Route::get('/{company}/edit', [App\Http\Controllers\CompanyController::class, 'edit'])->name('edit');
        Route::put('/{company}', [App\Http\Controllers\CompanyController::class, 'update'])->name('update');
        Route::delete('/{company}', [App\Http\Controllers\CompanyController::class, 'destroy'])->name('destroy');
    });
    
    // Salesperson routes
    Route::prefix('salespeople')->name('salespeople.')->group(function () {
        Route::get('/', [App\Http\Controllers\SalespersonController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\SalespersonController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\SalespersonController::class, 'store'])->name('store');
        Route::get('/{salesperson}', [App\Http\Controllers\SalespersonController::class, 'show'])->name('show');
        Route::get('/{salesperson}/edit', [App\Http\Controllers\SalespersonController::class, 'edit'])->name('edit');
        Route::put('/{salesperson}', [App\Http\Controllers\SalespersonController::class, 'update'])->name('update');
        Route::delete('/{salesperson}', [App\Http\Controllers\SalespersonController::class, 'destroy'])->name('destroy');
        Route::post('/{salesperson}/discount-settings', [App\Http\Controllers\SalespersonController::class, 'updateDiscountSettings'])->name('updateDiscountSettings');
    });
    
    // Proposal routes
    Route::prefix('proposals')->name('proposals.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProposalController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\ProposalController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ProposalController::class, 'store'])->name('store');
        Route::get('/{proposal}', [App\Http\Controllers\ProposalController::class, 'show'])->name('show');
        Route::get('/{proposal}/edit', [App\Http\Controllers\ProposalController::class, 'edit'])->name('edit');
        Route::put('/{proposal}', [App\Http\Controllers\ProposalController::class, 'update'])->name('update');
        Route::delete('/{proposal}', [App\Http\Controllers\ProposalController::class, 'destroy'])->name('destroy');
        Route::post('/{proposal}/send', [App\Http\Controllers\ProposalController::class, 'send'])->name('send');
        Route::post('/{proposal}/accept', [App\Http\Controllers\ProposalController::class, 'accept'])->name('accept');
        Route::post('/{proposal}/reject', [App\Http\Controllers\ProposalController::class, 'reject'])->name('reject');
        Route::post('/calculate-discount', [App\Http\Controllers\ProposalController::class, 'calculateDiscount'])->name('calculateDiscount');
    });
    
    // Freight Tables routes (per tenant)
    Route::prefix('freight-tables')->name('freight-tables.')->group(function () {
        Route::get('/', [App\Http\Controllers\FreightTableController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\FreightTableController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\FreightTableController::class, 'store'])->name('store');
        Route::get('/{freightTable}', [App\Http\Controllers\FreightTableController::class, 'show'])->name('show');
        Route::get('/{freightTable}/edit', [App\Http\Controllers\FreightTableController::class, 'edit'])->name('edit');
        Route::put('/{freightTable}', [App\Http\Controllers\FreightTableController::class, 'update'])->name('update');
        Route::delete('/{freightTable}', [App\Http\Controllers\FreightTableController::class, 'destroy'])->name('destroy');
    });
    
    // Salesperson Dashboard routes
    Route::prefix('salesperson')->name('salesperson.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\SalespersonDashboardController::class, 'index'])->name('dashboard');
        Route::post('/calculate-freight', [App\Http\Controllers\SalespersonDashboardController::class, 'calculateFreight'])->name('calculateFreight');
    });
    
    // Invoicing routes
    Route::prefix('invoicing')->name('invoicing.')->group(function () {
        Route::get('/', [App\Http\Controllers\InvoicingController::class, 'index'])->name('index');
    });
    
    // Invoice routes
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/{invoice}', [App\Http\Controllers\InvoicingController::class, 'show'])->name('show');
    });
    
    // Shipments routes
    Route::prefix('shipments')->name('shipments.')->group(function () {
        Route::get('/', [App\Http\Controllers\ShipmentController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\ShipmentController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ShipmentController::class, 'store'])->name('store');
        Route::get('/{shipment}', [App\Http\Controllers\ShipmentController::class, 'show'])->name('show');
        Route::get('/{shipment}/edit', [App\Http\Controllers\ShipmentController::class, 'edit'])->name('edit');
        Route::put('/{shipment}', [App\Http\Controllers\ShipmentController::class, 'update'])->name('update');
        Route::delete('/{shipment}', [App\Http\Controllers\ShipmentController::class, 'destroy'])->name('destroy');
    });
    
    // Accounts Receivable routes
    Route::prefix('accounts/receivable')->name('accounts.receivable.')->group(function () {
        Route::get('/', [App\Http\Controllers\AccountsReceivableController::class, 'index'])->name('index');
        Route::get('/overdue', [App\Http\Controllers\AccountsReceivableController::class, 'overdueReport'])->name('overdue');
        Route::get('/{invoice}', [App\Http\Controllers\AccountsReceivableController::class, 'show'])->name('show');
        Route::post('/{invoice}/payment', [App\Http\Controllers\AccountsReceivableController::class, 'recordPayment'])->name('payment');
    });
    
    // Accounts Payable routes
    Route::prefix('accounts/payable')->name('accounts.payable.')->group(function () {
        Route::get('/', [App\Http\Controllers\ExpenseController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\ExpenseController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ExpenseController::class, 'store'])->name('store');
        Route::get('/{expense}', [App\Http\Controllers\ExpenseController::class, 'show'])->name('show');
        Route::get('/{expense}/edit', [App\Http\Controllers\ExpenseController::class, 'edit'])->name('edit');
        Route::put('/{expense}', [App\Http\Controllers\ExpenseController::class, 'update'])->name('update');
        Route::delete('/{expense}', [App\Http\Controllers\ExpenseController::class, 'destroy'])->name('destroy');
        Route::post('/{expense}/payment', [App\Http\Controllers\ExpenseController::class, 'recordPayment'])->name('payment');
    });
    
    // Cash Flow routes
    Route::prefix('cash-flow')->name('cash-flow.')->group(function () {
        Route::get('/', [App\Http\Controllers\CashFlowController::class, 'index'])->name('index');
    });
    
    // Clients routes
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [App\Http\Controllers\ClientController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\ClientController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ClientController::class, 'store'])->name('store');
        Route::get('/{client}', [App\Http\Controllers\ClientController::class, 'show'])->name('show');
        Route::get('/{client}/edit', [App\Http\Controllers\ClientController::class, 'edit'])->name('edit');
        Route::put('/{client}', [App\Http\Controllers\ClientController::class, 'update'])->name('update');
        Route::delete('/{client}', [App\Http\Controllers\ClientController::class, 'destroy'])->name('destroy');
    });
    
    // Drivers routes
    Route::prefix('drivers')->name('drivers.')->group(function () {
        Route::get('/', [App\Http\Controllers\DriverController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\DriverController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\DriverController::class, 'store'])->name('store');
        Route::get('/{driver}', [App\Http\Controllers\DriverController::class, 'show'])->name('show');
        Route::get('/{driver}/edit', [App\Http\Controllers\DriverController::class, 'edit'])->name('edit');
        Route::put('/{driver}', [App\Http\Controllers\DriverController::class, 'update'])->name('update');
        Route::delete('/{driver}', [App\Http\Controllers\DriverController::class, 'destroy'])->name('destroy');
    });
    
    // Vehicles routes
    Route::prefix('vehicles')->name('vehicles.')->group(function () {
        Route::get('/', [App\Http\Controllers\VehicleController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\VehicleController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\VehicleController::class, 'store'])->name('store');
        Route::get('/{vehicle}', [App\Http\Controllers\VehicleController::class, 'show'])->name('show');
        Route::get('/{vehicle}/edit', [App\Http\Controllers\VehicleController::class, 'edit'])->name('edit');
        Route::put('/{vehicle}', [App\Http\Controllers\VehicleController::class, 'update'])->name('update');
        Route::delete('/{vehicle}', [App\Http\Controllers\VehicleController::class, 'destroy'])->name('destroy');
        Route::post('/{vehicle}/assign-drivers', [App\Http\Controllers\VehicleController::class, 'assignDrivers'])->name('assign-drivers');
        Route::post('/{vehicle}/unassign-driver/{driver}', [App\Http\Controllers\VehicleController::class, 'unassignDriver'])->name('unassign-driver');
    });
    
    // Routes routes
    Route::prefix('routes')->name('routes.')->group(function () {
        Route::get('/', [App\Http\Controllers\RouteController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\RouteController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\RouteController::class, 'store'])->name('store');
        Route::post('/create-branch', [App\Http\Controllers\RouteController::class, 'createBranch'])->name('create-branch');
        Route::get('/{route}/select-route', [App\Http\Controllers\RouteController::class, 'selectRoute'])->name('select-route');
        Route::post('/{route}/select-route', [App\Http\Controllers\RouteController::class, 'storeSelectedRoute'])->name('store-selected-route');
        Route::get('/{route}', [App\Http\Controllers\RouteController::class, 'show'])->name('show');
        Route::get('/{route}/edit', [App\Http\Controllers\RouteController::class, 'edit'])->name('edit');
        Route::put('/{route}', [App\Http\Controllers\RouteController::class, 'update'])->name('update');
        Route::delete('/{route}', [App\Http\Controllers\RouteController::class, 'destroy'])->name('destroy');
        Route::get('/{route}/cte-xml/{fiscalDocument}', [App\Http\Controllers\RouteController::class, 'downloadCteXml'])->name('download-cte-xml');
    });
    
    // Driver Dashboard routes
    Route::prefix('driver')->name('driver.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\DriverDashboardController::class, 'index'])->name('dashboard');
        Route::get('/routes/{route}/map-data', [App\Http\Controllers\DriverDashboardController::class, 'getRouteMapData'])->name('route.map-data');
        Route::post('/routes/{route}/start', [App\Http\Controllers\DriverDashboardController::class, 'startRoute'])->name('route.start');
        Route::post('/routes/{route}/finish', [App\Http\Controllers\DriverDashboardController::class, 'finishRoute'])->name('route.finish');
    });
    
    // Monitoring routes (Admin/Manager)
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/', [App\Http\Controllers\MonitoringController::class, 'index'])->name('index');
        Route::get('/driver-locations', [App\Http\Controllers\MonitoringController::class, 'getDriverLocations'])->name('driver-locations');
        Route::get('/routes/{route}/map-data', [App\Http\Controllers\MonitoringController::class, 'getRouteMapData'])->name('route.map-data');
    });
    
    // Fiscal routes
    Route::prefix('fiscal')->name('fiscal.')->group(function () {
        // CT-e listing routes
        Route::get('/ctes', [App\Http\Controllers\FiscalDocumentController::class, 'indexCtes'])->name('ctes.index');
        Route::get('/ctes/{fiscalDocument}', [App\Http\Controllers\FiscalDocumentController::class, 'showCte'])->name('ctes.show');
        Route::post('/ctes/filter', [App\Http\Controllers\FiscalDocumentController::class, 'filterCtes'])->name('ctes.filter');
        
        // MDF-e listing routes
        Route::get('/mdfes', [App\Http\Controllers\FiscalDocumentController::class, 'indexMdfes'])->name('mdfes.index');
        Route::get('/mdfes/{fiscalDocument}', [App\Http\Controllers\FiscalDocumentController::class, 'showMdfe'])->name('mdfes.show');
        Route::post('/mdfes/filter', [App\Http\Controllers\FiscalDocumentController::class, 'filterMdfes'])->name('mdfes.filter');
        
        // Fiscal reports routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [App\Http\Controllers\FiscalReportController::class, 'index'])->name('index');
            Route::get('/ctes', [App\Http\Controllers\FiscalReportController::class, 'ctes'])->name('ctes');
            Route::get('/mdfes', [App\Http\Controllers\FiscalReportController::class, 'mdfes'])->name('mdfes');
            Route::get('/consolidated', [App\Http\Controllers\FiscalReportController::class, 'consolidated'])->name('consolidated');
        });
        
        // Document issuance routes
        Route::post('/shipments/{shipment}/issue-cte', [App\Http\Controllers\FiscalController::class, 'issueCte'])->name('issue-cte');
        Route::post('/shipments/{shipment}/sync-cte', [App\Http\Controllers\FiscalController::class, 'syncCte'])->name('sync-cte');
        Route::post('/routes/{route}/issue-mdfe', [App\Http\Controllers\FiscalController::class, 'issueMdfe'])->name('issue-mdfe');
        Route::post('/routes/{route}/sync-mdfe', [App\Http\Controllers\FiscalController::class, 'syncMdfe'])->name('sync-mdfe');
        Route::post('/documents/{fiscalDocument}/cancel-cte', [App\Http\Controllers\FiscalController::class, 'cancelCte'])->name('cancel-cte');
        Route::get('/documents/{fiscalDocument}/status', [App\Http\Controllers\FiscalController::class, 'getStatus'])->name('document-status');
    });
    
    // Notifications routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });
    
    // Reports routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::get('/shipments', [App\Http\Controllers\ReportController::class, 'shipments'])->name('shipments');
        Route::get('/financial', [App\Http\Controllers\ReportController::class, 'financial'])->name('financial');
    });
    
    // Settings routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\SettingsController::class, 'index'])->name('index');
        Route::get('/appearance', [App\Http\Controllers\SettingsController::class, 'appearance'])->name('appearance');
        Route::put('/appearance', [App\Http\Controllers\SettingsController::class, 'updateAppearance'])->name('appearance.update');

        Route::prefix('integrations')->name('integrations.')->group(function () {
            Route::get('/whatsapp', [WhatsAppIntegrationController::class, 'index'])->name('whatsapp.index');
            Route::post('/whatsapp', [WhatsAppIntegrationController::class, 'store'])->name('whatsapp.store');
            Route::post('/whatsapp/{whatsappIntegration}/sync', [WhatsAppIntegrationController::class, 'sync'])->name('whatsapp.sync');
            Route::get('/whatsapp/{whatsappIntegration}/qr', [WhatsAppIntegrationController::class, 'qr'])->name('whatsapp.qr');
            Route::delete('/whatsapp/{whatsappIntegration}', [WhatsAppIntegrationController::class, 'destroy'])->name('whatsapp.destroy');
        });
    });
});
