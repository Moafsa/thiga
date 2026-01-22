<?php

namespace App\Http\Controllers;

use App\Models\FreightTableCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FreightTableCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of categories
     */
    public function index()
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }
        
        $categories = FreightTableCategory::where('tenant_id', $tenant->id)
            ->withCount('activeFreightTables')
            ->orderBy('order')
            ->orderBy('name')
            ->get();
        
        return view('freight-table-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        return view('freight-table-categories.create');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'order' => 'nullable|integer|min:0',
        ]);

        $tenant = Auth::user()->tenant;

        $category = FreightTableCategory::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color ?? '#FF6B35',
            'order' => $request->order ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('freight-table-categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(FreightTableCategory $freightTableCategory)
    {
        $this->authorizeAccess($freightTableCategory);
        
        return view('freight-table-categories.edit', compact('freightTableCategory'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, FreightTableCategory $freightTableCategory)
    {
        $this->authorizeAccess($freightTableCategory);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $freightTableCategory->update($request->only([
            'name',
            'description',
            'color',
            'order',
            'is_active',
        ]));

        return redirect()->route('freight-table-categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Remove the specified category
     */
    public function destroy(FreightTableCategory $freightTableCategory)
    {
        $this->authorizeAccess($freightTableCategory);
        
        // Verifica se há tabelas vinculadas
        if ($freightTableCategory->freightTables()->count() > 0) {
            return redirect()->route('freight-table-categories.index')
                ->with('error', 'Não é possível excluir a categoria pois existem tabelas de frete vinculadas a ela.');
        }
        
        $freightTableCategory->delete();

        return redirect()->route('freight-table-categories.index')
            ->with('success', 'Categoria excluída com sucesso!');
    }

    /**
     * Authorize access to category (tenant isolation)
     */
    protected function authorizeAccess(FreightTableCategory $category)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant || $category->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized access to category');
        }
    }
}
