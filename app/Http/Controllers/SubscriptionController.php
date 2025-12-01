<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function __construct(
        private AsaasService $asaasService
    ) {}

    /**
     * Display available plans
     */
    public function index()
    {
        $plans = Plan::active()->orderBy('sort_order')->get();
        
        return view('subscriptions.index', compact('plans'));
    }

    /**
     * Show plan details
     */
    public function show(Plan $plan)
    {
        return view('subscriptions.show', compact('plan'));
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request, Plan $plan)
    {
        $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
            'payment_method' => 'required|in:credit_card,pix,boleto',
        ]);

        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return back()->withErrors(['error' => 'Tenant not found']);
        }

        // Check if tenant already has an active subscription
        $activeSubscription = $tenant->currentSubscription();
        if ($activeSubscription) {
            return back()->withErrors(['error' => 'You already have an active subscription']);
        }

        try {
            DB::beginTransaction();

            // Calculate price based on billing cycle
            $price = $plan->price;
            if ($request->billing_cycle === 'yearly') {
                $price = $price * 12 * 0.9; // 10% discount for yearly
            }

            // Create customer in Asaas
            $customerData = [
                'name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone ?? '',
                'cpfCnpj' => $tenant->document ?? '',
                'postalCode' => $tenant->postal_code ?? '',
                'address' => $tenant->address ?? '',
                'addressNumber' => $tenant->address_number ?? '',
                'complement' => $tenant->complement ?? '',
                'province' => $tenant->city ?? '',
                'city' => $tenant->city ?? '',
                'state' => $tenant->state ?? '',
            ];

            $asaasCustomer = $this->asaasService->createCustomer($customerData);

            // Create subscription in Asaas
            $subscriptionData = [
                'customer' => $asaasCustomer['id'],
                'billingType' => $request->payment_method,
                'value' => $price,
                'nextDueDate' => now()->addMonth()->format('Y-m-d'),
                'cycle' => $request->billing_cycle === 'monthly' ? 'MONTHLY' : 'YEARLY',
                'description' => "Assinatura {$plan->name} - TMS SaaS",
            ];

            $asaasSubscription = $this->asaasService->createSubscription($subscriptionData);

            // Create local subscription
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'asaas_customer_id' => $asaasCustomer['id'],
                'asaas_subscription_id' => $asaasSubscription['id'],
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(30),
                'starts_at' => now(),
                'amount' => $price,
                'billing_cycle' => $request->billing_cycle,
                'features' => $plan->features,
                'limits' => $plan->limits,
            ]);

            // Create first payment record
            Payment::create([
                'subscription_id' => $subscription->id,
                'asaas_payment_id' => $asaasSubscription['id'],
                'amount' => $price,
                'status' => 'pending',
                'due_date' => now()->addMonth(),
                'payment_method' => $request->payment_method,
                'description' => "Primeira cobrança - {$plan->name}",
            ]);

            DB::commit();

            return redirect()->route('subscriptions.success')
                ->with('success', 'Assinatura criada com sucesso! Você tem 30 dias de teste gratuito.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors(['error' => 'Erro ao criar assinatura: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Subscription $subscription)
    {
        if ($subscription->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        try {
            // Cancel in Asaas
            $this->asaasService->cancelSubscription($subscription->asaas_subscription_id);

            // Update local subscription
            $subscription->update([
                'status' => 'cancelled',
                'ends_at' => now(),
            ]);

            return back()->with('success', 'Assinatura cancelada com sucesso');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao cancelar assinatura: ' . $e->getMessage()]);
        }
    }

    /**
     * Show subscription details
     */
    public function showSubscription(Subscription $subscription)
    {
        if ($subscription->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $payments = $subscription->payments()->orderBy('created_at', 'desc')->get();

        return view('subscriptions.details', compact('subscription', 'payments'));
    }

    /**
     * Success page after subscription
     */
    public function success()
    {
        return view('subscriptions.success');
    }
}
