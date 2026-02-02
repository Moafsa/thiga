<?php

namespace App\Http\Livewire\Tenant;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class TenantUserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $isEditing = false;
    public $userIdBeingEdited = null;

    // Form fields
    public $name;
    public $email;
    public $phone;
    public $password;
    public $selected_roles = [];

    protected $listeners = ['refreshUsers' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $tenant = Auth::user()->tenant;

        $users = User::where('tenant_id', $tenant->id)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->with('roles')
            ->orderBy('name')
            ->paginate(10);

        $roles = Role::whereNull('tenant_id') // Global roles? Or specific tenant roles? Assuming global for now or needs check
            ->orWhere('tenant_id', $tenant->id)
            ->get();

        // If no roles found, providing default names for UI if they exist in DB conceptually
        // In this system, roles seem to be global like 'Admin Tenant', 'Financeiro', etc.
        // Let's filter to only show relevant roles for a tenant admin to assign.
        $availableRoles = ['Admin Tenant', 'Financeiro', 'Operacional', 'Vendedor', 'Motorista'];
        $roles = Role::whereIn('name', $availableRoles)->get();

        return view('livewire.tenant.user-management', [
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'email', 'phone', 'password', 'selected_roles', 'userIdBeingEdited']);
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(User $user)
    {
        if ($user->tenant_id !== Auth::user()->tenant_id) {
            return;
        }

        $this->resetValidation();
        $this->userIdBeingEdited = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->selected_roles = $user->roles->pluck('name')->toArray();
        $this->password = ''; // Don't show password
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $tenantId = Auth::user()->tenant_id;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($this->userIdBeingEdited)->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                })
            ],
            'phone' => 'nullable|string|max:20',
            'selected_roles' => 'required|array|min:1',
        ];

        if (!$this->isEditing) {
            $rules['password'] = 'required|string|min:8';
        } else {
            $rules['password'] = 'nullable|string|min:8';
        }

        $this->validate($rules);

        if ($this->isEditing) {
            $user = User::find($this->userIdBeingEdited);
            $user->name = $this->name;
            $user->email = $this->email;
            $user->phone = $this->phone;
            if (!empty($this->password)) {
                $user->password = Hash::make($this->password);
            }
            $user->save();
        } else {
            $user = User::create([
                'tenant_id' => $tenantId,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'password' => Hash::make($this->password),
                'is_active' => true,
            ]);
        }

        $user->syncRoles($this->selected_roles);

        $this->showModal = false;
        $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'Usuário salvo com sucesso!']);
    }

    public function confirmDelete($id)
    {
        $this->dispatchBrowserEvent('swal:confirm', [
            'type' => 'warning',
            'title' => 'Tem certeza?',
            'text' => 'O usuário será desativado permanentemente.',
            'id' => $id
        ]);
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);

        if ($user->tenant_id === Auth::user()->tenant_id && $user->id !== Auth::id()) {
            $user->delete(); // Or set is_active = false
            $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'Usuário removido.']);
        }
    }
}
