<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PushSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Subscribe to push notifications
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|url|max:500',
            'keys.p256dh' => 'required|string|max:200',
            'keys.auth' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid subscription data'], 422);
        }

        $user = Auth::user();

        // Upsert subscription (update if endpoint exists, create if not)
        PushSubscription::updateOrCreate(
            ['endpoint' => $request->endpoint],
            [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'p256dh_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
                'user_agent' => $request->userAgent(),
                'is_active' => true,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Subscribed successfully']);
    }

    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid endpoint'], 422);
        }

        PushSubscription::where('endpoint', $request->endpoint)
            ->where('user_id', Auth::id())
            ->update(['is_active' => false]);

        return response()->json(['success' => true, 'message' => 'Unsubscribed successfully']);
    }

    /**
     * Get VAPID public key for client-side subscription
     */
    public function vapidPublicKey()
    {
        $service = app(PushNotificationService::class);

        return response()->json([
            'publicKey' => $service->getPublicKey(),
            'configured' => $service->isConfigured(),
        ]);
    }

    /**
     * Check current subscription status
     */
    public function status()
    {
        $hasSubscription = PushSubscription::where('user_id', Auth::id())
            ->active()
            ->exists();

        return response()->json([
            'subscribed' => $hasSubscription,
        ]);
    }
}
