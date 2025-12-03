<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    protected array $managementRoles = [
        'Admin Tenant',
        'Super Admin',
    ];

    public function viewAny(User $user): bool
    {
        return $this->hasManagementRole($user);
    }

    public function view(User $user, Company $company): bool
    {
        return $this->belongsToSameTenant($user, $company) && $this->hasManagementRole($user);
    }

    public function create(User $user): bool
    {
        // Allow any authenticated user to create the first company (matrix) for their tenant
        if ($user->tenant_id === null) {
            return false;
        }

        $hasCompanies = \App\Models\Company::where('tenant_id', $user->tenant_id)->exists();
        
        // If tenant has no companies yet, allow any authenticated user to create the first one
        if (!$hasCompanies) {
            return true;
        }

        // For additional companies, require management role
        return $this->hasManagementRole($user);
    }

    public function update(User $user, Company $company): bool
    {
        return $this->belongsToSameTenant($user, $company) && $this->hasManagementRole($user);
    }

    public function delete(User $user, Company $company): bool
    {
        return $this->belongsToSameTenant($user, $company) && $this->hasManagementRole($user);
    }

    protected function belongsToSameTenant(User $user, Company $company): bool
    {
        return $user->tenant_id !== null
            && $company->tenant_id !== null
            && $user->tenant_id === $company->tenant_id;
    }

    protected function hasManagementRole(User $user): bool
    {
        return $user->hasAnyRole($this->managementRoles);
    }
}

