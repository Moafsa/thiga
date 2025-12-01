<?php

namespace App\Policies;

use App\Models\Salesperson;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalespersonPolicy
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

    public function view(User $user, Salesperson $salesperson): bool
    {
        if ($user->id === $salesperson->user_id) {
            return true;
        }

        return $this->belongsToSameTenant($user, $salesperson) && $this->hasManagementRole($user);
    }

    public function create(User $user): bool
    {
        return $this->hasManagementRole($user);
    }

    public function update(User $user, Salesperson $salesperson): bool
    {
        return $this->belongsToSameTenant($user, $salesperson) && $this->hasManagementRole($user);
    }

    public function delete(User $user, Salesperson $salesperson): bool
    {
        return $this->belongsToSameTenant($user, $salesperson) && $this->hasManagementRole($user);
    }

    public function updateDiscountSettings(User $user, Salesperson $salesperson): bool
    {
        return $this->update($user, $salesperson);
    }

    protected function belongsToSameTenant(User $user, Salesperson $salesperson): bool
    {
        return $user->tenant_id !== null
            && $salesperson->tenant_id !== null
            && $user->tenant_id === $salesperson->tenant_id;
    }

    protected function hasManagementRole(User $user): bool
    {
        return $user->hasAnyRole($this->managementRoles);
    }
}














