<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MapsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MapsController extends Controller
{
    private MapsService $mapsService;

    public function __construct(MapsService $mapsService)
    {
        $this->mapsService = $mapsService;
    }

    /**
     * Geocode an address
     * 
     * POST /api/maps/geocode
     */
    public function geocode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->mapsService->geocode($request->address);

        if (!$result) {
            return response()->json([
                'error' => 'Geocoding failed',
                'message' => 'Could not find coordinates for this address',
            ], 404);
        }

        return response()->json([
            'latitude' => $result['latitude'],
            'longitude' => $result['longitude'],
            'formatted_address' => $result['formatted_address'] ?? $request->address,
        ]);
    }

    /**
     * Reverse geocode coordinates
     * 
     * POST /api/maps/reverse-geocode
     */
    public function reverseGeocode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->mapsService->reverseGeocode(
            $request->latitude,
            $request->longitude
        );

        if (!$result) {
            return response()->json([
                'error' => 'Reverse geocoding failed',
                'message' => 'Could not find address for these coordinates',
            ], 404);
        }

        return response()->json([
            'formatted_address' => $result['formatted_address'],
            'latitude' => $result['latitude'],
            'longitude' => $result['longitude'],
        ]);
    }

    /**
     * Calculate route
     * 
     * POST /api/maps/route
     */
    public function calculateRoute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_latitude' => 'required|numeric|between:-90,90',
            'origin_longitude' => 'required|numeric|between:-180,180',
            'destination_latitude' => 'required|numeric|between:-90,90',
            'destination_longitude' => 'required|numeric|between:-180,180',
            'waypoints' => 'nullable|array',
            'waypoints.*.latitude' => 'required_with:waypoints|numeric|between:-90,90',
            'waypoints.*.longitude' => 'required_with:waypoints|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $waypoints = [];
        if ($request->has('waypoints')) {
            foreach ($request->waypoints as $wp) {
                $waypoints[] = [
                    'lat' => $wp['latitude'],
                    'lng' => $wp['longitude'],
                ];
            }
        }

        $result = $this->mapsService->calculateRoute(
            $request->origin_latitude,
            $request->origin_longitude,
            $request->destination_latitude,
            $request->destination_longitude,
            $waypoints
        );

        if (!$result) {
            return response()->json([
                'error' => 'Route calculation failed',
                'message' => 'Could not calculate route',
            ], 404);
        }

        return response()->json($result);
    }

    /**
     * Calculate distance
     * 
     * POST /api/maps/distance
     */
    public function calculateDistance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_latitude' => 'required|numeric|between:-90,90',
            'origin_longitude' => 'required|numeric|between:-180,180',
            'destination_latitude' => 'required|numeric|between:-90,90',
            'destination_longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->mapsService->calculateDistance(
            $request->origin_latitude,
            $request->origin_longitude,
            $request->destination_latitude,
            $request->destination_longitude
        );

        if (!$result) {
            return response()->json([
                'error' => 'Distance calculation failed',
                'message' => 'Could not calculate distance',
            ], 404);
        }

        return response()->json($result);
    }

    /**
     * Get usage statistics
     * 
     * GET /api/maps/usage
     */
    public function getUsage(Request $request)
    {
        $user = $request->user();
        $usage = \App\Models\MapsApiUsage::getTodayUsage(
            $user->id ?? null,
            $user->tenant_id ?? null
        );

        $quotaLimit = config('services.maps.daily_quota_limit', 1000);
        $totalUsage = array_sum(array_column($usage, 'total_requests'));

        return response()->json([
            'usage' => $usage,
            'total_requests' => $totalUsage,
            'quota_limit' => $quotaLimit,
            'remaining' => max(0, $quotaLimit - $totalUsage),
        ]);
    }
}
