<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Driver;
use App\Models\DriverPhoto;
use App\Models\DriverExpense;
use App\Models\LocationTracking;
use App\Models\Vehicle;
use App\Services\DriverPhotoService;
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

        // Get active route
        $activeRoute = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->with(['shipments.senderClient', 'shipments.receiverClient'])
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
     * Get current driver location (AJAX endpoint for polling)
     */
    public function getCurrentLocation(Request $request)
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

        // Refresh driver data to get latest location from database
        $driver->refresh();

        // Also check recent location tracking records
        $recentTracking = LocationTracking::where('driver_id', $driver->id)
            ->where('tracked_at', '>=', now()->subMinutes(5))
            ->orderBy('tracked_at', 'desc')
            ->first();

        // If driver location is old but we have recent tracking, use that
        // Use attributes array to access raw value and avoid accessor recursion
        $lastUpdateRaw = isset($driver->attributes['last_location_update']) ? $driver->attributes['last_location_update'] : null;
        
        if ($recentTracking && (!$lastUpdateRaw || \Carbon\Carbon::parse($lastUpdateRaw)->lt($recentTracking->tracked_at))) {
            $driver->update([
                'current_latitude' => $recentTracking->latitude,
                'current_longitude' => $recentTracking->longitude,
                'last_location_update' => $recentTracking->tracked_at,
            ]);
            $driver->refresh();
            $lastUpdateRaw = $driver->attributes['last_location_update'];
        }

        Log::debug('Driver location requested', [
            'driver_id' => $driver->id,
            'has_location' => ($driver->current_latitude && $driver->current_longitude),
            'latitude' => $driver->current_latitude,
            'longitude' => $driver->current_longitude,
            'last_update_raw' => $lastUpdateRaw,
            'recent_tracking' => $recentTracking ? [
                'lat' => $recentTracking->latitude,
                'lng' => $recentTracking->longitude,
                'tracked_at' => $recentTracking->tracked_at,
            ] : null,
        ]);

        return response()->json([
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'current_location' => ($driver->current_latitude && $driver->current_longitude) ? [
                    'lat' => floatval($driver->current_latitude),
                    'lng' => floatval($driver->current_longitude),
                ] : null,
                'last_location_update' => $lastUpdateRaw ? \Carbon\Carbon::parse($lastUpdateRaw)->toIso8601String() : null,
            ],
        ]);
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
            $route->update([
                'status' => 'in_progress',
                'started_at' => now(),
                'actual_departure_datetime' => now(),
            ]);

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
            ]);

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
}

















