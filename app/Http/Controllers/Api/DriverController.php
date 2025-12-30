<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Shipment;
use App\Models\LocationTracking;
use App\Models\DeliveryProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{
    /**
     * Get active route for driver
     */
    public function getActiveRoute(Request $request)
    {
        $driver = $this->getDriver($request);
        
        if (!$driver) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $route = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->with(['shipments.senderClient', 'shipments.receiverClient'])
            ->orderBy('scheduled_date', 'desc')
            ->first();

        if (!$route) {
            return response()->json(['message' => 'No active route found'], 404);
        }

        return response()->json([
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'current_location' => ($driver->current_latitude && $driver->current_longitude) ? [
                    'lat' => floatval($driver->current_latitude),
                    'lng' => floatval($driver->current_longitude),
                ] : null,
                'last_location_update' => $driver->last_location_update ? $driver->last_location_update->toIso8601String() : null,
            ],
            'route' => [
                'id' => $route->id,
                'name' => $route->name,
                'status' => $route->status,
                'scheduled_date' => $route->scheduled_date,
                'shipments' => $route->shipments->map(function ($shipment) {
                    return [
                        'id' => $shipment->id,
                        'tracking_number' => $shipment->tracking_number,
                        'title' => $shipment->title,
                        'pickup_address' => $shipment->pickup_address,
                        'pickup_city' => $shipment->pickup_city,
                        'pickup_state' => $shipment->pickup_state,
                        'pickup_zip_code' => $shipment->pickup_zip_code,
                        'pickup_latitude' => $shipment->pickup_latitude,
                        'pickup_longitude' => $shipment->pickup_longitude,
                        'delivery_address' => $shipment->delivery_address,
                        'delivery_city' => $shipment->delivery_city,
                        'delivery_state' => $shipment->delivery_state,
                        'delivery_zip_code' => $shipment->delivery_zip_code,
                        'delivery_latitude' => $shipment->delivery_latitude,
                        'delivery_longitude' => $shipment->delivery_longitude,
                        'status' => $shipment->status,
                        'sender_client' => $shipment->senderClient ? [
                            'name' => $shipment->senderClient->name,
                            'phone' => $shipment->senderClient->phone,
                        ] : null,
                        'receiver_client' => $shipment->receiverClient ? [
                            'name' => $shipment->receiverClient->name,
                            'phone' => $shipment->receiverClient->phone,
                        ] : null,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get shipments for active route
     */
    public function getShipments(Request $request)
    {
        $driver = $this->getDriver($request);
        
        if (!$driver) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $route = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->first();

        if (!$route) {
            return response()->json(['shipments' => []]);
        }

        $shipments = $route->shipments()
            ->with(['senderClient', 'receiverClient'])
            ->get()
            ->map(function ($shipment) {
                return [
                    'id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'title' => $shipment->title,
                    'pickup_address' => $shipment->pickup_address,
                    'pickup_city' => $shipment->pickup_city,
                    'pickup_state' => $shipment->pickup_state,
                    'pickup_zip_code' => $shipment->pickup_zip_code,
                    'pickup_latitude' => $shipment->pickup_latitude,
                    'pickup_longitude' => $shipment->pickup_longitude,
                    'delivery_address' => $shipment->delivery_address,
                    'delivery_city' => $shipment->delivery_city,
                    'delivery_state' => $shipment->delivery_state,
                    'delivery_zip_code' => $shipment->delivery_zip_code,
                    'delivery_latitude' => $shipment->delivery_latitude,
                    'delivery_longitude' => $shipment->delivery_longitude,
                    'status' => $shipment->status,
                ];
            });

        return response()->json(['shipments' => $shipments]);
    }

    /**
     * Update shipment status
     */
    public function updateShipmentStatus(Request $request, $shipmentId)
    {
        $driver = $this->getDriver($request);
        
        if (!$driver) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,picked_up,in_transit,delivered,exception',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:5120', // Max 5MB
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $shipment = Shipment::where('id', $shipmentId)
            ->where('driver_id', $driver->id)
            ->first();

        if (!$shipment) {
            return response()->json(['error' => 'Shipment not found'], 404);
        }

        // Handle photo upload if provided (using MinIO like DriverPhotoService)
        $photoPath = null;
        if ($request->hasFile('photo')) {
            try {
                $photo = $request->file('photo');
                $extension = $photo->getClientOriginalExtension();
                $filename = 'proof_' . time() . '_' . uniqid() . '.' . $extension;
                $path = "delivery_proofs/{$shipment->tenant_id}/{$shipment->id}/{$filename}";
                
                // Try MinIO first, fallback to public
                $disk = 'minio';
                try {
                    \Storage::disk('minio')->put($path, file_get_contents($photo->getRealPath()));
                } catch (\Exception $e) {
                    Log::warning('MinIO upload failed, using public disk fallback', [
                        'error' => $e->getMessage()
                    ]);
                    $disk = 'public';
                    \Storage::disk('public')->put($path, file_get_contents($photo->getRealPath()));
                }
                
                $photoPath = $path;
            } catch (\Exception $e) {
                Log::error('Error uploading delivery proof photo', [
                    'shipment_id' => $shipmentId,
                    'error' => $e->getMessage()
                ]);
                return response()->json(['error' => 'Failed to upload photo'], 500);
            }
        }

        // Update shipment status
        $updateData = [
            'status' => $request->status,
        ];

        if ($request->notes) {
            $updateData['notes'] = $request->notes;
        }

        if ($request->status === 'picked_up') {
            $updateData['picked_up_at'] = now();
        }

        if ($request->status === 'delivered') {
            $updateData['delivered_at'] = now();
        }

        $shipment->update($updateData);

        // Create delivery proof if photo or location is provided
        if ($photoPath || ($request->latitude && $request->longitude)) {
            $proofData = [
                'tenant_id' => $shipment->tenant_id,
                'shipment_id' => $shipment->id,
                'driver_id' => $driver->id,
                'proof_type' => $request->status === 'delivered' ? 'delivery' : ($request->status === 'picked_up' ? 'pickup' : 'other'),
                'description' => $request->notes,
                'status' => 'pending',
                'delivery_time' => now(),
            ];

            if ($request->latitude && $request->longitude) {
                $proofData['latitude'] = $request->latitude;
                $proofData['longitude'] = $request->longitude;
                // Try to get address from geocoding if available
                $proofData['address'] = $shipment->delivery_address;
                $proofData['city'] = $shipment->delivery_city;
                $proofData['state'] = $shipment->delivery_state;
            }

            if ($photoPath) {
                // Store path, URL will be generated by accessor
                $proofData['photos'] = [$photoPath];
            }

            DeliveryProof::create($proofData);
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

        return response()->json([
            'message' => 'Shipment status updated successfully',
            'shipment' => [
                'id' => $shipment->id,
                'status' => $shipment->status,
            ],
        ]);
    }

    /**
     * Update driver location
     */
    public function updateLocation(Request $request)
    {
        $driver = $this->getDriver($request);
        
        if (!$driver) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'is_moving' => 'nullable|boolean',
            'route_id' => 'nullable|exists:routes,id',
            'shipment_id' => 'nullable|exists:shipments,id',
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
        LocationTracking::create([
            'tenant_id' => $driver->tenant_id,
            'driver_id' => $driver->id,
            'route_id' => $request->route_id,
            'shipment_id' => $request->shipment_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'is_moving' => $request->is_moving ?? false,
            'tracked_at' => now(),
            'device_id' => $request->header('X-Device-ID'),
            'app_version' => $request->header('X-App-Version'),
            'metadata' => $request->except(['latitude', 'longitude', 'accuracy', 'speed', 'heading', 'is_moving', 'route_id', 'shipment_id']),
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
     * Get driver location history
     */
    public function getLocationHistory(Request $request)
    {
        $driver = $this->getDriver($request);
        
        if (!$driver) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $routeId = $request->input('route_id');
        $shipmentId = $request->input('shipment_id');
        $minutes = $request->input('minutes', 60);

        $query = LocationTracking::where('driver_id', $driver->id)
            ->where('tracked_at', '>=', now()->subMinutes($minutes))
            ->orderBy('tracked_at', 'asc');

        if ($routeId) {
            $query->where('route_id', $routeId);
        }

        if ($shipmentId) {
            $query->where('shipment_id', $shipmentId);
        }

        $locations = $query->get()->map(function ($location) {
            return [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'accuracy' => $location->accuracy,
                'speed' => $location->speed,
                'heading' => $location->heading,
                'is_moving' => $location->is_moving,
                'tracked_at' => $location->tracked_at->toIso8601String(),
            ];
        });

        return response()->json(['locations' => $locations]);
    }

    /**
     * Get driver from request (via token or auth)
     */
    protected function getDriver(Request $request)
    {
        if ($request->attributes->has('driver')) {
            return $request->attributes->get('driver');
        }

        if ($request->user()) {
            $driver = Driver::where('user_id', $request->user()->id)->first();
            if ($driver) {
                return $driver;
            }
        }

        return null;
    }
}

