<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display company settings
     */
    public function index()
    {
        $tenant = Auth::user()->tenant;
        $companies = Company::where('tenant_id', $tenant->id)->get();
        
        return view('companies.index', compact('companies'));
    }

    /**
     * Show company details
     */
    public function show(Company $company)
    {
        $this->authorize('view', $company);
        
        $branches = $company->branches()->active()->get();
        
        return view('companies.show', compact('company', 'branches'));
    }

    /**
     * Show create company form
     */
    public function create()
    {
        $states = [
            'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
            'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
            'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
        ];

        return view('companies.create', compact('states'));
    }

    /**
     * Store new company
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'cnpj' => 'required|string|unique:companies,cnpj',
            'ie' => 'nullable|string|max:255',
            'im' => 'nullable|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'website' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'postal_code' => 'required|string',
            'address' => 'required|string',
            'address_number' => 'required|string',
            'complement' => 'nullable|string',
            'neighborhood' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'crt' => 'required|string',
            'cnae' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['tenant_id'] = Auth::user()->tenant_id;
        $data['is_matrix'] = true; // First company is always matrix

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $tenantId = Auth::user()->tenant_id;
            $data['logo'] = $request->file('logo')->store("tenants/{$tenantId}/logos", 'minio');
        }

        $company = Company::create($data);

        return redirect()->route('companies.show', $company)
            ->with('success', 'Empresa criada com sucesso!');
    }

    /**
     * Show edit company form
     */
    public function edit(Company $company)
    {
        $this->authorize('update', $company);
        
        return view('companies.edit', compact('company'));
    }

    /**
     * Update company
     */
    public function update(Request $request, Company $company)
    {
        $this->authorize('update', $company);

        $request->validate([
            'name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'cnpj' => 'required|string|unique:companies,cnpj,' . $company->id,
            'ie' => 'nullable|string|max:255',
            'im' => 'nullable|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'website' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'postal_code' => 'required|string',
            'address' => 'required|string',
            'address_number' => 'required|string',
            'complement' => 'nullable|string',
            'neighborhood' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'crt' => 'required|string',
            'cnae' => 'nullable|string',
        ]);

        $data = $request->all();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($company->logo) {
                Storage::disk('minio')->delete($company->logo);
            }
            $tenantId = Auth::user()->tenant_id;
            $data['logo'] = $request->file('logo')->store("tenants/{$tenantId}/logos", 'minio');
        }

        $company->update($data);

        return redirect()->route('companies.show', $company)
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Delete company
     */
    public function destroy(Company $company)
    {
        $this->authorize('delete', $company);

        // Don't allow deleting matrix company
        if ($company->is_matrix) {
            return back()->withErrors(['error' => 'Não é possível excluir a empresa matriz.']);
        }

        // Delete logo
        if ($company->logo) {
            Storage::disk('minio')->delete($company->logo);
        }

        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Empresa excluída com sucesso!');
    }
}
