<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['tenant', 'plan', 'payments'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('search')) {
            $query->whereHas('tenant', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        // Summary stats
        $stats = [
            'active'    => Subscription::where('status', 'active')->count(),
            'trial'     => Subscription::where('status', 'trial')->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
            'expired'   => Subscription::where('status', 'expired')->count(),
        ];

        // Payments recentes
        $recentPayments = Payment::whereNotNull('subscription_id')
            ->with(['subscription.tenant'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('superadmin.subscriptions.index', compact('subscriptions', 'stats', 'recentPayments'));
    }
}
