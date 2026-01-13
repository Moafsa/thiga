<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'company_id',
        'branch_id',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the user.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the company that owns the user.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the user.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the driver profile associated with the user.
     */
    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    /**
     * Get the salespeople for the user.
     */
    public function salespeople()
    {
        return $this->hasMany(Salesperson::class);
    }

    /**
     * Get the client profile associated with the user.
     */
    public function client()
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Get all client assignments for the user (multi-tenant support).
     */
    public function clientAssignments()
    {
        return $this->hasMany(ClientUser::class);
    }

    /**
     * Check if user is admin of tenant.
     */
    public function isTenantAdmin(): bool
    {
        return $this->hasRole('Admin Tenant');
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }
}
