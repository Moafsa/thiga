<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\MapsApiUsage;
use Illuminate\Support\Facades\Auth;

class CheckMapsApiQuota
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::user();
        
        // If no user, allow but log warning (might be unauthenticated request)
        if (!$user) {
            \Log::warning('Maps API request without authentication', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);
            
            // For now, allow unauthenticated requests but without quota tracking
            return $next($request);
        }
        
        $tenant = $user->tenant ?? null;
        
        // Get quota limit from config
        $quotaLimit = config('services.maps.daily_quota_limit', 1000);
        
        // Get today's usage
        $today = now()->startOfDay();
        $usage = MapsApiUsage::where('date', $today)
            ->when($user, function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($tenant, function ($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })
            ->sum('requests');
        
        // Check if quota exceeded
        if ($usage >= $quotaLimit) {
            return response()->json([
                'error' => 'Daily quota exceeded',
                'message' => "You have reached the daily limit of {$quotaLimit} map API requests. Please try again tomorrow.",
                'usage' => $usage,
                'limit' => $quotaLimit,
            ], 429);
        }
        
        // Add usage info to request for logging
        $request->attributes->set('maps_api_usage', [
            'current' => $usage,
            'limit' => $quotaLimit,
            'remaining' => $quotaLimit - $usage,
        ]);
        
        return $next($request);
    }
}
