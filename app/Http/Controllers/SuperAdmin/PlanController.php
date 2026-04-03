<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('subscriptions')->orderBy('sort_order')->get();
        return view('superadmin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('superadmin.plans.form', ['plan' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
            'is_popular'  => 'nullable|boolean',
            'features'    => 'nullable|array',
            'limits'      => 'nullable|array',
        ]);

        $data['is_active']  = $request->boolean('is_active');
        $data['is_popular'] = $request->boolean('is_popular');
        $data['features']   = $request->input('features', []);
        $data['limits']     = $request->input('limits', []);

        Plan::create($data);

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Plano criado com sucesso!');
    }

    public function edit(Plan $plan)
    {
        return view('superadmin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
            'is_popular'  => 'nullable|boolean',
            'features'    => 'nullable|array',
            'limits'      => 'nullable|array',
        ]);

        $data['is_active']  = $request->boolean('is_active');
        $data['is_popular'] = $request->boolean('is_popular');
        $data['features']   = $request->input('features', []);
        $data['limits']     = $request->input('limits', []);

        $plan->update($data);

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Plano atualizado com sucesso!');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->subscriptions()->whereIn('status', ['active', 'trial'])->exists()) {
            return back()->withErrors(['error' => 'Não é possível excluir um plano com assinaturas ativas.']);
        }

        $plan->delete();
        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Plano removido.');
    }

    public function toggleActive(Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        return back()->with('success', 'Status do plano atualizado.');
    }

    public function togglePopular(Plan $plan)
    {
        $plan->update(['is_popular' => !$plan->is_popular]);
        return back()->with('success', 'Destaque do plano atualizado.');
    }
}
