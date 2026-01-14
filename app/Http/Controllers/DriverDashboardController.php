<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Driver;
use App\Models\DriverPhoto;
use App\Models\DriverExpense;
use App\Models\LocationTracking;
use App\Models\Vehicle;
use App\Models\DriverRouteHistory;
use App\Services\DriverPhotoService;
use App\Services\RouteHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;

class DriverDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display driver dashboard with map
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        // Get driver associated with user
        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not registered as a driver.');
        }

        // Get active route with delivery proofs
        $activeRoute = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->with(['shipments' => function($query) {
                $query->with(['senderClient', 'receiverClient', 'deliveryProofs']);
            }])
            ->orderBy('scheduled_date', 'desc')
            ->first();

        // Get recent location tracking
        $recentLocations = LocationTracking::where('driver_id', $driver->id)
            ->where('tracked_at', '>=', now()->subHours(2))
            ->orderBy('tracked_at', 'desc')
            ->limit(100)
            ->get();

        // Get all shipments for active route
        $shipments = $activeRoute ? $activeRoute->shipments : collect();

        // Get period filter from request (week, month, or all)
        $period = $request->get('period', 'all');
        $startDate = $this->getStartDateForPeriod($period);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now();

        // Calculate wallet data with cache and period filter
        $cacheKey = "driver_wallet_{$driver->id}_{$period}_" . ($startDate ? $startDate->format('Y-m-d') : 'all');
        $walletData = Cache::remember($cacheKey, 300, function () use ($driver, $startDate, $endDate) {
            return $this->calculateWalletData($driver, $startDate, $endDate);
        });

        $totalReceived = $walletData['totalReceived'];
        $totalSpent = $walletData['totalSpent'];
        $currentBalance = $walletData['currentBalance'];
        $recentFinancialRoutes = $walletData['recentFinancialRoutes'];

        return view('driver.dashboard', compact(
            'driver', 
            'activeRoute', 
            'shipments', 
            'recentLocations',
            'totalReceived',
            'totalSpent',
            'currentBalance',
            'recentFinancialRoutes',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get start date based on period filter
     */
    private function getStartDateForPeriod(?string $period): ?Carbon
    {
        return match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => null, // all time
        };
    }

    /**
     * Calculate wallet data for driver
     * IMPORTANT: Only approved expenses count as spent
     */
    private function calculateWalletData(Driver $driver, ?Carbon $startDate, Carbon $endDate): array
    {
        $query = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['scheduled', 'in_progress', 'completed']);

        // Apply date filter if provided
        if ($startDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('scheduled_date', [$startDate, $endDate])
                  ->orWhereBetween('completed_at', [$startDate, $endDate])
                  ->orWhere(function ($subQ) use ($startDate, $endDate) {
                      $subQ->whereNull('completed_at')
                           ->whereBetween('scheduled_date', [$startDate, $endDate]);
                  });
            });
        }

        $allRoutes = $query->get();

        // Calculate total received (diarias)
        $totalReceived = $allRoutes->sum(function ($route) {
            return ($route->driver_diarias_count ?? 0) * ($route->driver_diaria_value ?? 0);
        });

        // Calculate total deposits (money given to driver for expenses)
        $totalDeposits = $allRoutes->sum(function ($route) {
            return ($route->deposit_toll ?? 0) + 
                   ($route->deposit_expenses ?? 0) + 
                   ($route->deposit_fuel ?? 0);
        });

        // Calculate total proven expenses (only approved expenses count)
        $expensesQuery = DriverExpense::where('driver_id', $driver->id)
            ->where('status', 'approved');

        if ($startDate) {
            $expensesQuery->whereBetween('expense_date', [$startDate, $endDate]);
        }

        $totalProvenExpenses = $expensesQuery->sum('amount');

        // Available balance = received + deposits - proven expenses
        $availableBalance = ($totalReceived + $totalDeposits) - $totalProvenExpenses;

        // For backward compatibility
        $totalSpent = $totalProvenExpenses;
        $currentBalance = $availableBalance;

        // Get recent financial transactions - unified history like bank statement
        // Positive: Diárias and Deposits (money received)
        // Negative: Proven expenses (money spent)
        $transactions = collect();

        // Get routes with financial data (diárias and deposits = POSITIVE)
        $routesQuery = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('driver_diarias_count')
                      ->where('driver_diarias_count', '>', 0);
                })
                ->orWhere(function ($q) {
                    $q->whereNotNull('deposit_toll')
                      ->where('deposit_toll', '>', 0);
                })
                ->orWhere(function ($q) {
                    $q->whereNotNull('deposit_expenses')
                      ->where('deposit_expenses', '>', 0);
                })
                ->orWhere(function ($q) {
                    $q->whereNotNull('deposit_fuel')
                      ->where('deposit_fuel', '>', 0);
                });
            });

        if ($startDate) {
            $routesQuery->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('scheduled_date', [$startDate, $endDate])
                  ->orWhereBetween('completed_at', [$startDate, $endDate]);
            });
        }

        $routes = $routesQuery->get();

        foreach ($routes as $route) {
            $routeDate = $route->completed_at ?? $route->scheduled_date;
            
            // Diárias (positive)
            $diariasAmount = ($route->driver_diarias_count ?? 0) * ($route->driver_diaria_value ?? 0);
            if ($diariasAmount > 0) {
                $transactions->push([
                    'type' => 'diarias',
                    'description' => "Diárias - {$route->name}",
                    'amount' => $diariasAmount,
                    'date' => $routeDate,
                    'route' => $route,
                    'is_positive' => true,
                ]);
            }

            // Deposits (positive - money given to spend)
            if (($route->deposit_toll ?? 0) > 0) {
                $transactions->push([
                    'type' => 'deposit',
                    'description' => "Depósito Pedágio - {$route->name}",
                    'amount' => $route->deposit_toll,
                    'date' => $routeDate,
                    'route' => $route,
                    'is_positive' => true,
                ]);
            }
            if (($route->deposit_expenses ?? 0) > 0) {
                $transactions->push([
                    'type' => 'deposit',
                    'description' => "Depósito Despesas - {$route->name}",
                    'amount' => $route->deposit_expenses,
                    'date' => $routeDate,
                    'route' => $route,
                    'is_positive' => true,
                ]);
            }
            if (($route->deposit_fuel ?? 0) > 0) {
                $transactions->push([
                    'type' => 'deposit',
                    'description' => "Depósito Combustível - {$route->name}",
                    'amount' => $route->deposit_fuel,
                    'date' => $routeDate,
                    'route' => $route,
                    'is_positive' => true,
                ]);
            }
        }

        // Get proven expenses (negative - money spent)
        $expensesQuery = DriverExpense::where('driver_id', $driver->id)
            ->where('status', 'approved');

        if ($startDate) {
            $expensesQuery->whereBetween('expense_date', [$startDate, $endDate]);
        }

        $expenses = $expensesQuery->get();

        foreach ($expenses as $expense) {
            $transactions->push([
                'type' => 'expense',
                'description' => $expense->description,
                'amount' => $expense->amount,
                'date' => $expense->expense_date,
                'expense' => $expense,
                'route' => $expense->route,
                'is_positive' => false,
            ]);
        }

        // Sort by date descending (most recent first) for display
        // But calculate balance from oldest to newest
        $sortedByDate = $transactions->sortBy(function ($transaction) {
            return $transaction['date']->timestamp;
        })->values();

        // Calculate running balance from oldest to newest
        $runningBalance = 0;
        $transactionsWithBalance = $sortedByDate->map(function ($transaction) use (&$runningBalance) {
            if ($transaction['is_positive']) {
                $runningBalance += $transaction['amount'];
            } else {
                $runningBalance -= $transaction['amount'];
            }
            $transaction['balance'] = $runningBalance;
            return $transaction;
        });

        // Now sort descending for display (most recent first) and take last 10
        $recentFinancialRoutes = $transactionsWithBalance
            ->sortByDesc(function ($transaction) {
                return $transaction['date']->timestamp;
            })
            ->take(10)
            ->values();

        return [
            'totalReceived' => $totalReceived,
            'totalDeposits' => $totalDeposits,
            'totalProvenExpenses' => $totalProvenExpenses,
            'availableBalance' => $availableBalance,
            'totalGiven' => $totalReceived + $totalDeposits,
            // Backward compatibility
            'totalSpent' => $totalSpent,
            'currentBalance' => $currentBalance,
            'recentFinancialRoutes' => $recentFinancialRoutes,
        ];
    }

    /**
     * Clear wallet cache for driver
     */
    public function clearWalletCache(Driver $driver): void
    {
        $periods = ['all', 'week', 'month', 'year'];
        foreach ($periods as $period) {
            $startDate = $this->getStartDateForPeriod($period);
            $cacheKey = "driver_wallet_{$driver->id}_{$period}_" . ($startDate ? $startDate->format('Y-m-d') : 'all');
            Cache::forget($cacheKey);
        }
    }

    /**
     * Update driver location from web browser (AJAX endpoint)
     */
    public function updateLocation(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'route_id' => 'nullable|exists:routes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update driver current location
        $driver->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
            'last_location_update' => now(),
        ]);

        // Create location tracking record
        $locationTracking = LocationTracking::create([
            'tenant_id' => $driver->tenant_id,
            'driver_id' => $driver->id,
            'route_id' => $request->route_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'tracked_at' => now(),
            'device_id' => $request->header('User-Agent'),
            'metadata' => ['source' => 'web_browser'],
        ]);

        // Update actual path if route is in progress
        if ($request->route_id) {
            $route = \App\Models\Route::find($request->route_id);
            if ($route && $route->status === 'in_progress') {
                try {
                    $routePathService = app(\App\Services\RoutePathService::class);
                    $routePathService->updateActualPath(
                        $route,
                        $request->latitude,
                        $request->longitude
                    );
                } catch (\Exception $e) {
                    Log::error('Error updating actual path', [
                        'route_id' => $request->route_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::debug('Driver location updated from web', [
            'driver_id' => $driver->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'message' => 'Location updated successfully',
            'location' => [
                'latitude' => $driver->current_latitude,
                'longitude' => $driver->current_longitude,
                'updated_at' => $driver->last_location_update,
            ],
        ]);
    }

    /**
     * Get current driver location (AJAX endpoint for polling)
     */
    public function getCurrentLocation(Request $request)
    {
        try {
            $user = Auth::user();
            $tenant = $user->tenant;

            if (!$tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }

            $driver = Driver::where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$driver) {
                return response()->json(['error' => 'Driver not found'], 404);
            }

            // Refresh driver data to get latest location from database
            $driver->refresh();

            // Also check recent location tracking records
            $recentTracking = null;
            try {
                $recentTracking = LocationTracking::where('driver_id', $driver->id)
                    ->where('tracked_at', '>=', now()->subMinutes(5))
                    ->orderBy('tracked_at', 'desc')
                    ->first();
            } catch (\Exception $e) {
                Log::warning('Error fetching recent tracking', [
                    'driver_id' => $driver->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // If driver location is old but we have recent tracking, use that
            // Use attributes array to access raw value and avoid accessor recursion
            $lastUpdateRaw = isset($driver->attributes['last_location_update']) && $driver->attributes['last_location_update'] 
                ? $driver->attributes['last_location_update'] 
                : null;
            
            if ($recentTracking && $recentTracking->latitude && $recentTracking->longitude) {
                $shouldUpdate = false;
                if (!$lastUpdateRaw) {
                    $shouldUpdate = true;
                } else {
                    try {
                        $lastUpdateCarbon = \Carbon\Carbon::parse($lastUpdateRaw);
                        if ($lastUpdateCarbon->lt($recentTracking->tracked_at)) {
                            $shouldUpdate = true;
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error parsing last_location_update', [
                            'driver_id' => $driver->id,
                            'last_update_raw' => $lastUpdateRaw,
                            'error' => $e->getMessage(),
                        ]);
                        $shouldUpdate = true; // Update if we can't parse the date
                    }
                }
                
                if ($shouldUpdate) {
                    try {
                        $driver->update([
                            'current_latitude' => $recentTracking->latitude,
                            'current_longitude' => $recentTracking->longitude,
                            'last_location_update' => $recentTracking->tracked_at,
                        ]);
                        $driver->refresh();
                        $lastUpdateRaw = $driver->attributes['last_location_update'] ?? null;
                    } catch (\Exception $e) {
                        Log::error('Error updating driver location from tracking', [
                            'driver_id' => $driver->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Format last_location_update safely
            $lastUpdateFormatted = null;
            if ($lastUpdateRaw) {
                try {
                    if (is_string($lastUpdateRaw)) {
                        $lastUpdateFormatted = \Carbon\Carbon::parse($lastUpdateRaw)->toIso8601String();
                    } elseif ($lastUpdateRaw instanceof \Carbon\Carbon) {
                        $lastUpdateFormatted = $lastUpdateRaw->toIso8601String();
                    } elseif ($lastUpdateRaw instanceof \DateTime) {
                        $lastUpdateFormatted = \Carbon\Carbon::instance($lastUpdateRaw)->toIso8601String();
                    }
                } catch (\Exception $e) {
                    Log::warning('Error formatting last_location_update', [
                        'last_update_raw' => $lastUpdateRaw,
                        'type' => gettype($lastUpdateRaw),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'driver' => [
                    'id' => $driver->id,
                    'name' => $driver->name ?? 'Unknown',
                    'current_location' => ($driver->current_latitude && $driver->current_longitude) ? [
                        'lat' => floatval($driver->current_latitude),
                        'lng' => floatval($driver->current_longitude),
                    ] : null,
                    'last_location_update' => $lastUpdateFormatted,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getCurrentLocation', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Internal server error',
                'message' => 'An error occurred while fetching driver location',
            ], 500);
        }
    }

    /**
     * Get route map data (AJAX endpoint)
     */
    public function getRouteMapData(Request $request, Route $route)
    {
        $user = Auth::user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver || $route->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $route->load(['shipments', 'driver']);

        // Get location history for this route
        $locationHistory = LocationTracking::where('route_id', $route->id)
            ->where('tracked_at', '>=', now()->subHours(24))
            ->orderBy('tracked_at', 'asc')
            ->get();

        // Prepare map data
        $mapData = [
            'route' => [
                'id' => $route->id,
                'name' => $route->name,
                'status' => $route->status,
            ],
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'current_location' => $driver->current_latitude && $driver->current_longitude ? [
                    'lat' => $driver->current_latitude,
                    'lng' => $driver->current_longitude,
                ] : null,
            ],
            'shipments' => $route->shipments->map(function ($shipment) {
                return [
                    'id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'title' => $shipment->title,
                    'pickup' => $shipment->pickup_latitude && $shipment->pickup_longitude ? [
                        'lat' => $shipment->pickup_latitude,
                        'lng' => $shipment->pickup_longitude,
                        'address' => $shipment->pickup_address . ', ' . $shipment->pickup_city . '/' . $shipment->pickup_state,
                    ] : null,
                    'delivery' => $shipment->delivery_latitude && $shipment->delivery_longitude ? [
                        'lat' => $shipment->delivery_latitude,
                        'lng' => $shipment->delivery_longitude,
                        'address' => $shipment->delivery_address . ', ' . $shipment->delivery_city . '/' . $shipment->delivery_state,
                    ] : null,
                    'status' => $shipment->status,
                ];
            }),
            'location_history' => $locationHistory->map(function ($location) {
                return [
                    'lat' => $location->latitude,
                    'lng' => $location->longitude,
                    'timestamp' => $location->tracked_at->toIso8601String(),
                    'speed' => $location->speed,
                ];
            }),
        ];

        return response()->json($mapData);
    }

    /**
     * Update shipment status (web endpoint for driver dashboard)
     */
    public function updateShipmentStatus(Request $request, $shipmentId)
    {
        try {
            $user = Auth::user();
            $tenant = $user->tenant;

            if (!$tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }

            $driver = Driver::where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$driver) {
                return response()->json(['error' => 'Driver not found'], 404);
            }

            Log::info('Update shipment status request', [
                'shipment_id' => $shipmentId,
                'driver_id' => $driver->id,
                'user_id' => $user->id,
            ]);

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'status' => 'required|string|in:pending,picked_up,in_transit,delivered,exception',
                'notes' => 'nullable|string',
                'photo' => 'nullable|image|max:5120', // Max 5MB
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Check if shipment exists first
            $shipmentExists = \App\Models\Shipment::where('id', $shipmentId)->exists();
            if (!$shipmentExists) {
                Log::warning('Shipment ID does not exist', [
                    'shipment_id' => $shipmentId,
                ]);
                return response()->json(['error' => 'Shipment not found'], 404);
            }

            // Find shipment - check both direct driver_id and route's driver_id
            $shipment = \App\Models\Shipment::where('id', $shipmentId)
                ->where(function($query) use ($driver) {
                    $query->where('driver_id', $driver->id)
                          ->orWhereHas('route', function($q) use ($driver) {
                              $q->where('driver_id', $driver->id);
                          });
                })
                ->first();

            if (!$shipment) {
                // Check shipment's actual driver_id and route
                $actualShipment = \App\Models\Shipment::where('id', $shipmentId)->with('route')->first();
                Log::warning('Shipment not found for driver', [
                    'shipment_id' => $shipmentId,
                    'driver_id' => $driver->id,
                    'user_id' => $user->id,
                    'shipment_driver_id' => $actualShipment ? $actualShipment->driver_id : null,
                    'route_driver_id' => $actualShipment && $actualShipment->route ? $actualShipment->route->driver_id : null,
                    'route_id' => $actualShipment ? $actualShipment->route_id : null,
                ]);
                return response()->json(['error' => 'Shipment not found or does not belong to this driver'], 404);
            }

            // Handle photo upload if provided (using MinIO like DriverPhotoService)
            $photoPath = null;
            if ($request->hasFile('photo')) {
                try {
                    $photo = $request->file('photo');
                    
                    // Optimize image before upload (reduce file size significantly)
                    $optimizedData = $this->optimizeImage($photo, 1920, 1920, 85);
                    $originalSize = $photo->getSize();
                    $optimizedSize = strlen($optimizedData);
                    
                    Log::info('Photo file received and optimized', [
                        'shipment_id' => $shipmentId,
                        'original_name' => $photo->getClientOriginalName(),
                        'original_size' => $originalSize,
                        'optimized_size' => $optimizedSize,
                        'reduction' => round((1 - $optimizedSize / $originalSize) * 100, 1) . '%',
                        'mime_type' => $photo->getMimeType(),
                    ]);
                    
                    $filename = 'proof_' . time() . '_' . uniqid() . '.jpg'; // Always use jpg after optimization
                    $path = "delivery_proofs/{$shipment->tenant_id}/{$shipment->id}/{$filename}";
                    
                    // Check MinIO configuration once
                    $minioConfig = config('filesystems.disks.minio');
                    if (empty($minioConfig) || !isset($minioConfig['bucket']) || (!isset($minioConfig['endpoint']) && !isset($minioConfig['url']))) {
                        Log::error('MinIO configuration is missing or incomplete');
                        throw new \Exception('MinIO não está configurado corretamente. Verifique as configurações do sistema.');
                    }
                    
                    // Upload optimized image to MinIO directly
                    Storage::disk('minio')->put($path, $optimizedData, 'public');
                    
                    // Don't verify with exists() - trust that put() worked and avoid extra request
                    // If there's an error, it will throw an exception
                    
                    Log::info('Photo uploaded to MinIO successfully', [
                        'path' => $path,
                        'original_size' => $originalSize,
                        'optimized_size' => $optimizedSize,
                    ]);
                    
                    $photoPath = $path;
                } catch (\Aws\S3\Exception\S3Exception $e) {
                    Log::error('S3/MinIO exception during upload', [
                        'shipment_id' => $shipmentId,
                        'error' => $e->getMessage(),
                        'code' => $e->getAwsErrorCode(),
                    ]);
                    return response()->json(['error' => 'Erro ao conectar com MinIO: ' . $e->getMessage()], 500);
                } catch (\Exception $e) {
                    Log::error('Error uploading delivery proof photo', [
                        'shipment_id' => $shipmentId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return response()->json(['error' => 'Failed to upload photo: ' . $e->getMessage()], 500);
                }
            } else {
                Log::debug('No photo file in request', [
                    'has_file' => $request->hasFile('photo'),
                    'all_files' => array_keys($request->allFiles()),
                ]);
            }

            // Update shipment status
            $updateData = [
                'status' => $request->status,
            ];

            if ($request->notes) {
                $updateData['notes'] = $request->notes;
            }

            // Check if columns exist before trying to update them
            $shipmentTable = $shipment->getTable();
            if (\Illuminate\Support\Facades\Schema::hasColumn($shipmentTable, 'picked_up_at')) {
                if ($request->status === 'picked_up') {
                    $updateData['picked_up_at'] = now();
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn($shipmentTable, 'delivered_at')) {
                if ($request->status === 'delivered') {
                    $updateData['delivered_at'] = now();
                }
            }

            // Update shipment status first
            try {
                $shipment->update($updateData);
                Log::info('Shipment status updated', [
                    'shipment_id' => $shipment->id,
                    'status' => $request->status,
                ]);
            } catch (\Exception $e) {
                Log::error('Error updating shipment status', [
                    'shipment_id' => $shipment->id,
                    'update_data' => $updateData,
                    'error' => $e->getMessage(),
                ]);
                throw $e; // Re-throw as this is critical
            }

            // Create delivery proof if photo or notes is provided
            if ($photoPath || $request->notes) {
                try {
                    $proofData = [
                        'tenant_id' => $shipment->tenant_id,
                        'shipment_id' => $shipment->id,
                        'driver_id' => $driver->id,
                        'proof_type' => $request->status === 'delivered' ? 'delivery' : ($request->status === 'picked_up' ? 'pickup' : 'other'),
                        'description' => $request->notes ?? null,
                        'status' => 'pending',
                        'delivery_time' => now(),
                    ];

                    // Always try to get location from request first, then from shipment
                    // Latitude and longitude are required in database, so use shipment coordinates or 0,0 as fallback
                    $latitude = null;
                    $longitude = null;
                    
                    if ($request->has('latitude') && $request->has('longitude') && $request->latitude && $request->longitude) {
                        $latitude = (float) $request->latitude;
                        $longitude = (float) $request->longitude;
                    } elseif ($shipment->delivery_latitude && $shipment->delivery_longitude) {
                        $latitude = (float) $shipment->delivery_latitude;
                        $longitude = (float) $shipment->delivery_longitude;
                    } elseif ($shipment->pickup_latitude && $shipment->pickup_longitude) {
                        $latitude = (float) $shipment->pickup_latitude;
                        $longitude = (float) $shipment->pickup_longitude;
                    }

                    // Set location (required by database, use 0,0 if not available)
                    // Ensure values are valid decimals for database
                    $proofData['latitude'] = ($latitude !== null && $latitude != 0) ? number_format((float)$latitude, 8, '.', '') : '0.00000000';
                    $proofData['longitude'] = ($longitude !== null && $longitude != 0) ? number_format((float)$longitude, 8, '.', '') : '0.00000000';
                    
                    // Try to get address from shipment
                    if ($shipment->delivery_address || $shipment->pickup_address) {
                        $proofData['address'] = $shipment->delivery_address ?? $shipment->pickup_address;
                        $proofData['city'] = $shipment->delivery_city ?? $shipment->pickup_city;
                        $proofData['state'] = $shipment->delivery_state ?? $shipment->pickup_state;
                    }

                    if ($photoPath) {
                        // Store path, URL will be generated by accessor
                        // Ensure photos is always an array
                        $proofData['photos'] = is_array($photoPath) ? $photoPath : [$photoPath];
                        Log::info('Delivery proof photo path stored', [
                            'shipment_id' => $shipment->id,
                            'photo_path' => $photoPath,
                            'photos_array' => $proofData['photos'],
                        ]);
                    } else {
                        // Ensure photos is empty array if no photo
                        $proofData['photos'] = [];
                    }

                    Log::info('Attempting to create delivery proof', [
                        'shipment_id' => $shipment->id,
                        'proof_data' => array_merge($proofData, ['photos' => $proofData['photos'] ?? []]),
                    ]);

                    $deliveryProof = \App\Models\DeliveryProof::create($proofData);
                    
                    // Reload to get fresh data with accessors
                    $deliveryProof->refresh();
                    
                    // Test photo_urls accessor (wrap in try-catch to avoid errors if MinIO is not accessible)
                    $photoUrls = [];
                    try {
                        $photoUrls = $deliveryProof->photo_urls;
                    } catch (\Exception $e) {
                        Log::warning('Error getting photo URLs from delivery proof accessor', [
                            'proof_id' => $deliveryProof->id,
                            'error' => $e->getMessage(),
                        ]);
                        $photoUrls = [];
                    }
                    
                    Log::info('Delivery proof created successfully', [
                        'proof_id' => $deliveryProof->id,
                        'shipment_id' => $shipment->id,
                        'has_photo' => !empty($photoPath),
                        'photo_path' => $photoPath ?? null,
                        'photos_saved' => $deliveryProof->photos ?? [],
                        'photo_urls_count' => count($photoUrls),
                        'latitude' => $proofData['latitude'],
                        'longitude' => $proofData['longitude'],
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error creating delivery proof', [
                        'shipment_id' => $shipment->id,
                        'driver_id' => $driver->id,
                        'proof_data' => $proofData ?? null,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Don't fail the entire request if proof creation fails, but log it
                    // The shipment status update was successful, so we continue
                }
            }

            // Return updated shipment with proofs
            $shipment->load('deliveryProofs');

            // Update location tracking if coordinates provided
            if ($request->latitude && $request->longitude) {
                LocationTracking::create([
                    'tenant_id' => $driver->tenant_id,
                    'driver_id' => $driver->id,
                    'shipment_id' => $shipment->id,
                    'route_id' => $shipment->route_id,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'accuracy' => $request->accuracy ?? null,
                    'tracked_at' => now(),
                ]);

                // Update driver current location
                $driver->update([
                    'current_latitude' => $request->latitude,
                    'current_longitude' => $request->longitude,
                    'last_location_update' => now(),
                ]);
            }

            // Reload shipment with fresh delivery proofs for response
            $shipment->refresh();
            $shipment->load(['deliveryProofs' => function($query) {
                $query->orderBy('delivery_time', 'desc');
            }]);
            
            // Get proof photos for response (wrap in try-catch to avoid errors if MinIO is not accessible)
            $proofPhotos = [];
            foreach ($shipment->deliveryProofs as $proof) {
                try {
                    $photoUrls = $proof->photo_urls ?? [];
                    foreach ($photoUrls as $photoUrl) {
                        if ($photoUrl) {
                            $proofPhotos[] = $photoUrl;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Error getting photo URLs for delivery proof in response', [
                        'proof_id' => $proof->id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue with next proof
                    continue;
                }
            }
            
            Log::info('Shipment status updated successfully', [
                'shipment_id' => $shipment->id,
                'status' => $shipment->status,
                'proofs_count' => $shipment->deliveryProofs->count(),
                'photos_count' => count($proofPhotos),
            ]);

            return response()->json([
                'message' => 'Status atualizado com sucesso!',
                'shipment' => [
                    'id' => $shipment->id,
                    'status' => $shipment->status,
                    'proofs_count' => $shipment->deliveryProofs->count(),
                    'has_photos' => count($proofPhotos) > 0,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating shipment status', [
                'shipment_id' => $shipmentId ?? null,
                'driver_id' => isset($driver) && $driver ? $driver->id : null,
                'user_id' => isset($user) && $user ? $user->id : null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Return more specific error message
            $errorMessage = 'Ocorreu um erro ao processar a solicitação.';
            if (str_contains($e->getMessage(), 'SQLSTATE') || str_contains($e->getMessage(), 'Integrity constraint')) {
                $errorMessage = 'Erro ao salvar os dados no banco de dados. Verifique os logs para mais detalhes.';
            } elseif (str_contains($e->getMessage(), 'Storage') || str_contains($e->getMessage(), 'MinIO')) {
                $errorMessage = 'Erro ao salvar a foto no MinIO. Verifique a configuração do armazenamento.';
            } elseif (str_contains($e->getMessage(), 'not found')) {
                $errorMessage = 'Entregue não encontrada ou não pertence a este motorista.';
            } else {
                $errorMessage = $e->getMessage();
            }
            
            return response()->json([
                'error' => 'Erro ao atualizar status',
                'message' => $errorMessage,
            ], 500);
        }
    }

    /**
     * Optimize image: resize and compress
     * 
     * @param \Illuminate\Http\UploadedFile $image
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @param int $quality JPEG quality (1-100)
     * @return string Binary image data
     */
    private function optimizeImage(\Illuminate\Http\UploadedFile $image, int $maxWidth = 1920, int $maxHeight = 1920, int $quality = 85): string
    {
        try {
            $mimeType = $image->getMimeType();
            $tempPath = $image->getRealPath();
            
            // Create image resource from file
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    $source = imagecreatefromjpeg($tempPath);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($tempPath);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($tempPath);
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $source = imagecreatefromwebp($tempPath);
                    } else {
                        throw new \Exception('WebP não é suportado neste servidor');
                    }
                    break;
                default:
                    throw new \InvalidArgumentException('Tipo de imagem não suportado: ' . $mimeType);
            }
            
            if (!$source) {
                throw new \RuntimeException('Falha ao criar recurso de imagem');
            }
            
            // Get original dimensions
            $originalWidth = imagesx($source);
            $originalHeight = imagesy($source);
            
            // Only resize if image is larger than max dimensions
            if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
                // Image is already within limits, just compress
                ob_start();
                imagejpeg($source, null, $quality);
                $optimizedData = ob_get_clean();
                imagedestroy($source);
                return $optimizedData;
            }
            
            // Calculate new dimensions maintaining aspect ratio
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);
            
            // Create resized image
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG and GIF
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
            
            // Resize
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            
            // Output to string as JPEG
            ob_start();
            imagejpeg($resized, null, $quality);
            $optimizedData = ob_get_clean();
            
            // Clean up
            imagedestroy($source);
            imagedestroy($resized);
            
            return $optimizedData;
        } catch (\Exception $e) {
            Log::warning('Image optimization failed, using original', [
                'error' => $e->getMessage(),
            ]);
            
            // Fallback: return original file contents
            return file_get_contents($image->getRealPath());
        }
    }

    /**
     * Export wallet statement to PDF
     */
    public function exportWalletPdf(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not registered as a driver.');
        }

        $period = $request->get('period', 'all');
        $startDate = $this->getStartDateForPeriod($period);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now();

        $walletData = $this->calculateWalletData($driver, $startDate, $endDate);

        // Get all routes for detailed statement
        $query = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('driver_diarias_count')
                      ->where('driver_diarias_count', '>', 0);
                })
                ->orWhere(function ($q) {
                    $q->whereNotNull('deposit_toll')
                      ->where('deposit_toll', '>', 0);
                })
                ->orWhere(function ($q) {
                    $q->whereNotNull('deposit_expenses')
                      ->where('deposit_expenses', '>', 0);
                })
                ->orWhere(function ($q) {
                    $q->whereNotNull('deposit_fuel')
                      ->where('deposit_fuel', '>', 0);
                });
            });

        if ($startDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('scheduled_date', [$startDate, $endDate])
                  ->orWhereBetween('completed_at', [$startDate, $endDate]);
            });
        }

        $routes = $query->orderByRaw('CASE WHEN completed_at IS NOT NULL THEN completed_at ELSE scheduled_date END DESC')->get();

        $transactions = $routes->map(function ($route) {
            $diariasAmount = ($route->driver_diarias_count ?? 0) * ($route->driver_diaria_value ?? 0);
            $depositsAmount = ($route->deposit_toll ?? 0) + 
                            ($route->deposit_expenses ?? 0) + 
                            ($route->deposit_fuel ?? 0);
            
            return [
                'date' => $route->completed_at ?? $route->scheduled_date,
                'route_name' => $route->name,
                'received' => $diariasAmount,
                'spent' => $depositsAmount,
                'net' => $diariasAmount - $depositsAmount,
                'diarias_count' => $route->driver_diarias_count ?? 0,
                'diaria_value' => $route->driver_diaria_value ?? 0,
                'deposit_toll' => $route->deposit_toll ?? 0,
                'deposit_expenses' => $route->deposit_expenses ?? 0,
                'deposit_fuel' => $route->deposit_fuel ?? 0,
            ];
        });

        $html = view('driver.wallet-export-pdf', compact(
            'driver',
            'transactions',
            'walletData',
            'startDate',
            'endDate',
            'period'
        ))->render();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'extrato_carteira_' . $driver->id . '_' . date('Y-m-d') . '.pdf';

        return response()->make($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Show driver profile edit page
     */
    public function profile()
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->with([
                'primaryPhoto',
                'photos' => function($query) {
                    $query->orderBy('is_primary', 'desc')->orderBy('sort_order')->orderBy('created_at', 'desc');
                },
                'vehicles' => function($query) {
                    $query->orderByDesc('driver_vehicle.assigned_at');
                }
            ])
            ->first();

        if (!$driver) {
            return redirect()->route('driver.dashboard')
                ->with('error', 'You are not registered as a driver.');
        }

        // Get activity log history (if activity log table exists)
        $activityLog = collect([]);
        try {
            if (\Schema::hasTable('activity_log')) {
                $activityLog = \Spatie\Activitylog\Models\Activity::where('subject_type', Driver::class)
                    ->where('subject_id', $driver->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();
            }
        } catch (\Exception $e) {
            // Activity log table doesn't exist or package not installed, continue without it
            \Log::debug('Activity log not available', ['error' => $e->getMessage()]);
        }

        $assignedVehicles = $driver->vehicles;

        return view('driver.profile', compact('driver', 'activityLog', 'assignedVehicles'));
    }

    /**
     * Update driver profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return redirect()->route('driver.dashboard')
                ->with('error', 'You are not registered as a driver.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|min:3',
            'phone' => 'nullable|string|max:20',
            'cnh_number' => 'nullable|string|max:20',
            'cnh_category' => 'nullable|string|max:5',
            'cnh_expiry_date' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'photo_data' => 'nullable|string', // Base64 image data from camera
            'remove_photo' => 'nullable|boolean',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'photo.image' => 'O arquivo deve ser uma imagem válida.',
            'photo.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg, gif ou webp.',
            'photo.max' => 'A imagem deve ter no máximo 2MB.',
            'cnh_expiry_date.date' => 'A data de validade da CNH deve ser uma data válida.',
        ]);

        try {
            // Update phone_e164 if phone was changed
            if (isset($validated['phone']) && $validated['phone']) {
                $phoneDigits = preg_replace('/\D/', '', $validated['phone']);
                
                if (strlen($phoneDigits) >= 10) {
                    if (!str_starts_with($phoneDigits, '55')) {
                        $validated['phone_e164'] = '55' . $phoneDigits;
                    } else {
                        $validated['phone_e164'] = $phoneDigits;
                    }
                } else {
                    // Invalid phone number, remove phone_e164
                    $validated['phone_e164'] = null;
                }
            }

            // Handle multiple photos upload
            $disk = Driver::getPhotoStorageDisk();
            
            // Handle photo removal
            if ($request->has('remove_photo_id')) {
                $photoId = $request->input('remove_photo_id');
                $photo = DriverPhoto::where('id', $photoId)
                    ->where('driver_id', $driver->id)
                    ->first();
                
                if ($photo) {
                    DriverPhotoService::deletePhoto($photo);
                }
            }
            
            // Handle new photo upload (multiple photos support)
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    if ($photo->getSize() > 2 * 1024 * 1024) {
                        continue; // Skip oversized files
                    }
                    
                    $photoType = $request->input('photo_type', 'profile');
                    $isPrimary = $request->has('set_as_primary');
                    
                    try {
                        DriverPhotoService::storePhoto($driver, $photo, $photoType, $isPrimary);
                    } catch (\Exception $e) {
                        \Log::error('Failed to store driver photo', [
                            'driver_id' => $driver->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
            
            // Handle single photo upload (backward compatibility)
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                
                if ($file->getSize() <= 2 * 1024 * 1024) {
                    try {
                        $photo = DriverPhotoService::storePhoto($driver, $file, 'profile', true);
                        // Also update photo_url for backward compatibility
                        if ($photo && $photo->photo_url) {
                            $validated['photo_url'] = $photo->photo_url;
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to store driver photo', [
                            'driver_id' => $driver->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        throw $e; // Propagate error to outer catch block
                    }
                }
            }
            
            // Handle base64 photo from camera
            if ($request->filled('photo_data')) {
                $photoData = $request->input('photo_data');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $photoData)) {
                    try {
                        $photo = DriverPhotoService::storePhoto($driver, $photoData, 'profile', true);
                        // Also update photo_url for backward compatibility
                        if ($photo && $photo->photo_url) {
                            $validated['photo_url'] = $photo->photo_url;
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to store driver photo from camera', [
                            'driver_id' => $driver->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        throw $e; // Propagate error to outer catch block
                    }
                }
            }
            
            // Handle set primary photo
            if ($request->has('set_primary_photo_id')) {
                $photoId = $request->input('set_primary_photo_id');
                $photo = DriverPhoto::where('id', $photoId)
                    ->where('driver_id', $driver->id)
                    ->first();
                
                if ($photo) {
                    DriverPhotoService::setPrimaryPhoto($driver, $photo);
                    $validated['photo_url'] = $photo->photo_url; // Update for backward compatibility
                }
            }

            // Remove photo-related fields from validated (not database fields)
            unset($validated['photo'], $validated['photo_data'], $validated['remove_photo'], 
                  $validated['photos'], $validated['photo_type'], $validated['set_as_primary'],
                  $validated['remove_photo_id'], $validated['set_primary_photo_id']);

            $driver->update($validated);

            return redirect()->route('driver.profile')
                ->with('success', 'Perfil atualizado com sucesso!');
                
        } catch (\Exception $e) {
            \Log::error('Error updating driver profile', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('driver.profile')
                ->withErrors(['error' => 'Erro ao atualizar perfil. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Delete driver photo
     */
    public function deletePhoto(Request $request, DriverPhoto $photo)
    {
        $user = Auth::user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver || $photo->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DriverPhotoService::deletePhoto($photo);
            return response()->json(['success' => true, 'message' => 'Foto removida com sucesso!']);
        } catch (\Exception $e) {
            \Log::error('Error deleting driver photo', [
                'photo_id' => $photo->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Erro ao remover foto'], 500);
        }
    }

    /**
     * Set primary photo
     */
    public function setPrimaryPhoto(Request $request, DriverPhoto $photo)
    {
        $user = Auth::user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver || $photo->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DriverPhotoService::setPrimaryPhoto($driver, $photo);
            return response()->json(['success' => true, 'message' => 'Foto principal atualizada!']);
        } catch (\Exception $e) {
            \Log::error('Error setting primary photo', [
                'photo_id' => $photo->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Erro ao atualizar foto principal'], 500);
        }
    }

    /**
     * Start a route
     */
    public function startRoute(Request $request, Route $route)
    {
        $user = Auth::user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }

        // Verify route belongs to driver
        if ($route->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify route status
        if ($route->status !== 'scheduled') {
            return response()->json([
                'error' => 'Route can only be started if it is scheduled. Current status: ' . $route->status
            ], 400);
        }

        try {
            // Se a rota foi criada com start_address_type = 'current_location',
            // captura a localização atual do motorista agora
            $updateData = [
                'status' => 'in_progress',
                'started_at' => now(),
                'actual_departure_datetime' => now(),
            ];
            
            // Se a rota não tem coordenadas de início definidas OU foi criada com current_location,
            // usa a localização atual do motorista
            if ((!$route->start_latitude || !$route->start_longitude) || 
                ($route->start_address_type === 'current_location' && $driver->current_latitude && $driver->current_longitude)) {
                
                if ($driver->current_latitude && $driver->current_longitude) {
                    $updateData['start_latitude'] = $driver->current_latitude;
                    $updateData['start_longitude'] = $driver->current_longitude;
                    
                    Log::info('Route start coordinates captured from driver current location', [
                        'route_id' => $route->id,
                        'driver_id' => $driver->id,
                        'coordinates' => [
                            'lat' => $driver->current_latitude,
                            'lng' => $driver->current_longitude,
                        ],
                    ]);
                } else {
                    Log::warning('Driver does not have current location when starting route', [
                        'route_id' => $route->id,
                        'driver_id' => $driver->id,
                    ]);
                }
            }
            
            $route->update($updateData);

            // Update vehicle status if vehicle is assigned
            if ($route->vehicle_id) {
                $vehicle = Vehicle::find($route->vehicle_id);
                if ($vehicle && $vehicle->status === 'available') {
                    $vehicle->update(['status' => 'in_use']);
                }
            }

            Log::info('Route started by driver', [
                'route_id' => $route->id,
                'driver_id' => $driver->id,
                'started_at' => $route->started_at,
                'start_coordinates' => $route->start_latitude && $route->start_longitude ? [
                    'lat' => $route->start_latitude,
                    'lng' => $route->start_longitude,
                ] : null,
            ]);
            
            // Se as coordenadas foram atualizadas, recalcula a rota
            $route->refresh();
            if ($route->start_latitude && $route->start_longitude && $route->shipments()->count() > 0) {
                try {
                    // Usa o RouteController para recalcular a rota
                    // Usamos reflection porque o método é protected
                    $routeController = app(\App\Http\Controllers\RouteController::class);
                    $reflection = new \ReflectionClass($routeController);
                    $method = $reflection->getMethod('calculateMultipleRouteOptions');
                    $method->setAccessible(true);
                    $method->invoke($routeController, $route);
                    
                    // Atualiza coordenadas de fim para retornar ao ponto de partida
                    $route->refresh();
                    if ($route->start_latitude && $route->start_longitude) {
                        $route->update([
                            'end_latitude' => $route->start_latitude,
                            'end_longitude' => $route->start_longitude,
                        ]);
                    }
                    
                    Log::info('Route recalculated after driver started with current location', [
                        'route_id' => $route->id,
                        'driver_id' => $driver->id,
                        'start_coordinates' => [
                            'lat' => $route->start_latitude,
                            'lng' => $route->start_longitude,
                        ],
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to recalculate route after driver started', [
                        'route_id' => $route->id,
                        'driver_id' => $driver->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Não falha a operação se o recálculo falhar
                }
            }

            return response()->json([
                'message' => 'Route started successfully',
                'route' => [
                    'id' => $route->id,
                    'status' => $route->status,
                    'started_at' => $route->started_at,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error starting route', [
                'route_id' => $route->id,
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error starting route: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finish a route
     */
    public function finishRoute(Request $request, Route $route)
    {
        $user = Auth::user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }

        // Verify route belongs to driver
        if ($route->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify route status
        if ($route->status !== 'in_progress') {
            return response()->json([
                'error' => 'Route can only be finished if it is in progress. Current status: ' . $route->status
            ], 400);
        }

        try {
            $route->update([
                'status' => 'completed',
                'completed_at' => now(),
                'actual_arrival_datetime' => now(),
            ]);

            // Update vehicle status if vehicle is assigned
            if ($route->vehicle_id) {
                $vehicle = Vehicle::find($route->vehicle_id);
                if ($vehicle) {
                    // Check if vehicle has other active routes
                    $hasOtherActiveRoutes = Route::where('vehicle_id', $route->vehicle_id)
                        ->where('id', '!=', $route->id)
                        ->whereIn('status', ['scheduled', 'in_progress'])
                        ->exists();
                    
                    if (!$hasOtherActiveRoutes && $vehicle->status === 'in_use') {
                        $vehicle->update(['status' => 'available']);
                    }
                }
            }

            Log::info('Route finished by driver', [
                'route_id' => $route->id,
                'driver_id' => $driver->id,
                'completed_at' => $route->completed_at,
            ]);

            return response()->json([
                'message' => 'Route finished successfully',
                'route' => [
                    'id' => $route->id,
                    'status' => $route->status,
                    'completed_at' => $route->completed_at,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error finishing route', [
                'route_id' => $route->id,
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error finishing route: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get route history for driver (AJAX endpoint)
     */
    public function getRouteHistory(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }

        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);
        $period = $request->get('period', 'all'); // all, week, month, year

        $query = DriverRouteHistory::where('driver_id', $driver->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc');

        // Apply period filter
        if ($period !== 'all') {
            $startDate = $this->getStartDateForPeriod($period);
            if ($startDate) {
                $query->where('completed_at', '>=', $startDate);
            }
        }

        $total = $query->count();
        $routes = $query->skip($offset)->take($limit)->get();

        return response()->json([
            'routes' => $routes->map(function ($route) {
                return [
                    'id' => $route->id,
                    'route_id' => $route->route_id,
                    'route_name' => $route->route_name,
                    'completed_at' => $route->completed_at->toIso8601String(),
                    'formatted_date' => $route->completed_at->format('d/m/Y H:i'),
                    'distance' => $route->formatted_distance,
                    'duration' => $route->formatted_duration,
                    'total_shipments' => $route->total_shipments,
                    'delivered_shipments' => $route->delivered_shipments,
                    'efficiency_score' => $route->efficiency_score,
                    'efficiency_badge_color' => $route->efficiency_badge_color,
                    'success_rate' => $route->success_rate,
                    'achievements' => $route->achievement_badges,
                    'total_revenue' => $route->total_revenue,
                    'net_profit' => $route->net_profit,
                ];
            }),
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Get driver statistics (AJAX endpoint)
     */
    public function getDriverStatistics(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }

        $period = $request->get('period', 'all');
        $startDate = $this->getStartDateForPeriod($period);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now();

        $routeHistoryService = app(RouteHistoryService::class);
        $stats = $routeHistoryService->getDriverStatistics(
            $driver->id,
            $startDate,
            $endDate
        );

        return response()->json($stats);
    }
}

















