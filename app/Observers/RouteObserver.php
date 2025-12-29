<?php

namespace App\Observers;

use App\Models\Route;
use App\Models\WhatsAppIntegration;
use App\Notifications\DriverPaymentReceived;
use App\Notifications\DriverExpenseAdded;
use App\Services\DriverAuthService;
use App\Services\WuzApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RouteObserver
{
    protected WuzApiService $wuzApiService;
    protected DriverAuthService $driverAuthService;

    public function __construct(WuzApiService $wuzApiService, DriverAuthService $driverAuthService)
    {
        $this->wuzApiService = $wuzApiService;
        $this->driverAuthService = $driverAuthService;
    }

    /**
     * Handle the Route "created" event.
     */
    public function created(Route $route): void
    {
        // Notify driver if route was created with a driver assigned and has coordinates
        // Note: Coordinates may not be available immediately if geocoding happens after creation
        // The updated() method will handle notification when driver is assigned later
        if ($route->driver_id && $route->start_latitude && $route->start_longitude) {
            $this->notifyDriverAboutRoute($route);
        }
    }

    /**
     * Handle the Route "updated" event.
     */
    public function updated(Route $route): void
    {
        // Check if financial data was updated
        $financialFields = [
            'driver_diarias_count',
            'driver_diaria_value',
            'deposit_toll',
            'deposit_expenses',
            'deposit_fuel',
        ];

        $hasFinancialChanges = false;
        foreach ($financialFields as $field) {
            if ($route->wasChanged($field)) {
                $hasFinancialChanges = true;
                break;
            }
        }

        if ($hasFinancialChanges && $route->driver_id) {
            // Clear wallet cache for the driver
            $this->clearDriverWalletCache($route->driver_id);

            // Send notifications if values were added
            $this->handleFinancialNotifications($route);
        }

        // Check if driver was assigned to route
        if ($route->wasChanged('driver_id') && $route->driver_id) {
            // Only notify if route has coordinates (route is ready)
            if ($route->start_latitude && $route->start_longitude) {
                $this->notifyDriverAboutRoute($route);
            }
        }

        // Check if coordinates were just added and route already has a driver
        // This handles the case when route calculation happens after route creation
        if ($route->driver_id && 
            ($route->wasChanged('start_latitude') || $route->wasChanged('start_longitude')) &&
            $route->start_latitude && 
            $route->start_longitude) {
            // Only notify if this is the first time coordinates are set
            // (avoid duplicate notifications)
            $wasNotified = $route->settings['whatsapp_notified'] ?? false;
            if (!$wasNotified) {
                $this->notifyDriverAboutRoute($route);
                // Mark as notified to avoid duplicates
                $settings = $route->settings ?? [];
                $settings['whatsapp_notified'] = true;
                $route->settings = $settings;
                $route->saveQuietly(); // Use saveQuietly to avoid triggering observer again
            }
        }
    }

    /**
     * Clear wallet cache for driver
     */
    protected function clearDriverWalletCache(int $driverId): void
    {
        $periods = ['all', 'week', 'month', 'year'];
        foreach ($periods as $period) {
            $cacheKey = "driver_wallet_{$driverId}_{$period}_all";
            Cache::forget($cacheKey);
            
            // Also clear with date variations
            $startDate = match($period) {
                'week' => now()->startOfWeek(),
                'month' => now()->startOfMonth(),
                'year' => now()->startOfYear(),
                default => null,
            };
            
            if ($startDate) {
                $cacheKey = "driver_wallet_{$driverId}_{$period}_" . $startDate->format('Y-m-d');
                Cache::forget($cacheKey);
            }
        }
    }

    /**
     * Handle financial notifications
     */
    protected function handleFinancialNotifications(Route $route): void
    {
        if (!$route->driver) {
            return;
        }

        $driver = $route->driver;

        // Check if diarias were added or updated
        if ($route->wasChanged('driver_diarias_count') || $route->wasChanged('driver_diaria_value')) {
            $oldDiariasCount = $route->getOriginal('driver_diarias_count') ?? 0;
            $oldDiariaValue = $route->getOriginal('driver_diaria_value') ?? 0;
            $oldAmount = $oldDiariasCount * $oldDiariaValue;
            
            $newDiariasCount = $route->driver_diarias_count ?? 0;
            $newDiariaValue = $route->driver_diaria_value ?? 0;
            $newAmount = $newDiariasCount * $newDiariaValue;

            // Only notify if amount increased
            if ($newAmount > $oldAmount && $newAmount > 0) {
                try {
                    $driver->user->notify(new DriverPaymentReceived($route, $newAmount - $oldAmount));
                } catch (\Exception $e) {
                    Log::error('Failed to send payment notification', [
                        'route_id' => $route->id,
                        'driver_id' => $driver->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Check if deposits were added
        $depositFields = ['deposit_toll', 'deposit_expenses', 'deposit_fuel'];
        foreach ($depositFields as $field) {
            if ($route->wasChanged($field)) {
                $oldValue = $route->getOriginal($field) ?? 0;
                $newValue = $route->$field ?? 0;

                // Only notify if deposit increased
                if ($newValue > $oldValue && $newValue > 0) {
                    try {
                        $fieldName = match($field) {
                            'deposit_toll' => 'Pedágio',
                            'deposit_expenses' => 'Despesas',
                            'deposit_fuel' => 'Combustível',
                            default => 'Despesa',
                        };

                        $driver->user->notify(new DriverExpenseAdded($route, $fieldName, $newValue - $oldValue));
                    } catch (\Exception $e) {
                        Log::error('Failed to send expense notification', [
                            'route_id' => $route->id,
                            'driver_id' => $driver->id,
                            'field' => $field,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Notify driver about route assignment via WhatsApp
     */
    protected function notifyDriverAboutRoute(Route $route): void
    {
        try {
            if (!$route->driver || !$route->driver->phone) {
                Log::warning('Cannot notify driver: driver or phone missing', [
                    'route_id' => $route->id,
                    'driver_id' => $route->driver_id,
                ]);
                return;
            }

            $driver = $route->driver;
            $tenant = $route->tenant;

            if (!$tenant) {
                Log::warning('Cannot notify driver: tenant missing', [
                    'route_id' => $route->id,
                    'driver_id' => $driver->id,
                ]);
                return;
            }

            // Get WhatsApp integration for tenant
            $integration = WhatsAppIntegration::where('tenant_id', $tenant->id)
                ->where('status', WhatsAppIntegration::STATUS_CONNECTED)
                ->first();

            if (!$integration) {
                Log::warning('Cannot notify driver: WhatsApp integration not found or not connected', [
                    'route_id' => $route->id,
                    'tenant_id' => $tenant->id,
                    'driver_id' => $driver->id,
                ]);
                return;
            }

            $userToken = $integration->getUserToken();
            if (!$userToken) {
                Log::warning('Cannot notify driver: WhatsApp user token not available', [
                    'route_id' => $route->id,
                    'integration_id' => $integration->id,
                ]);
                return;
            }

            // Generate Google Maps URL
            $googleMapsUrl = $route->getGoogleMapsUrl();
            if (!$googleMapsUrl) {
                Log::warning('Cannot notify driver: Google Maps URL could not be generated', [
                    'route_id' => $route->id,
                    'has_start_coords' => !!($route->start_latitude && $route->start_longitude),
                ]);
                return;
            }

            // Build message
            $routeName = $route->name ?: "Rota #{$route->id}";
            $scheduledDate = $route->scheduled_date ? $route->scheduled_date->format('d/m/Y') : 'Hoje';
            
            $message = "🚛 *Nova Rota Atribuída*\n\n";
            $message .= "Olá *{$driver->name}*!\n\n";
            $message .= "Você foi atribuído a uma nova rota:\n";
            $message .= "• *Rota:* {$routeName}\n";
            $message .= "• *Data:* {$scheduledDate}\n";
            
            if ($route->start_time) {
                $message .= "• *Horário:* " . \Carbon\Carbon::parse($route->start_time)->format('H:i') . "\n";
            }
            
            $shipmentsCount = $route->shipments()->count();
            if ($shipmentsCount > 0) {
                $message .= "• *Entregas:* {$shipmentsCount}\n";
            }
            
            $message .= "\n📍 *Abra a rota no Google Maps:*\n";
            $message .= $googleMapsUrl;
            $message .= "\n\nBoa viagem! 🚀";

            // Format phone number using the same logic as driver login
            $normalizedPhone = $this->driverAuthService->normalizePhone($driver->phone);
            
            if (!$normalizedPhone) {
                Log::warning('Cannot notify driver: phone number could not be normalized', [
                    'route_id' => $route->id,
                    'driver_id' => $driver->id,
                    'raw_phone' => $driver->phone,
                ]);
                return;
            }

            // Format for WhatsApp: ensure it starts with +55 (same logic as DriverAuthService::dispatchWhatsAppMessage)
            $formattedPhone = $normalizedPhone;
            if (!str_starts_with($normalizedPhone, '+')) {
                // If phone starts with 54, add +55 prefix: 5497092223 -> +555497092223
                if (str_starts_with($normalizedPhone, '54')) {
                    $formattedPhone = '+55' . $normalizedPhone;
                } elseif (str_starts_with($normalizedPhone, '55')) {
                    // If already has 55, just add +
                    $formattedPhone = '+' . $normalizedPhone;
                } else {
                    // Otherwise, add +55 prefix
                    $formattedPhone = '+55' . $normalizedPhone;
                }
            }

            // Send WhatsApp message
            $this->wuzApiService->sendTextMessage($userToken, $formattedPhone, $message);

            Log::info('Driver notified about route assignment via WhatsApp', [
                'route_id' => $route->id,
                'driver_id' => $driver->id,
                'raw_phone' => $driver->phone,
                'normalized_phone' => $normalizedPhone,
                'formatted_phone' => $formattedPhone,
            ]);

            // Mark route as notified to avoid duplicate notifications
            $settings = $route->settings ?? [];
            $settings['whatsapp_notified'] = true;
            $route->settings = $settings;
            $route->saveQuietly(); // Use saveQuietly to avoid triggering observer again
        } catch (\Exception $e) {
            Log::error('Failed to notify driver about route assignment', [
                'route_id' => $route->id,
                'driver_id' => $route->driver_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

}

