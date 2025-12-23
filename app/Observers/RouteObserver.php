<?php

namespace App\Observers;

use App\Models\Route;
use App\Notifications\DriverPaymentReceived;
use App\Notifications\DriverExpenseAdded;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RouteObserver
{
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
}

