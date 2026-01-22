<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProposalPolicy
{
    use HandlesAuthorization;

    protected array $managementRoles = [
        'Admin Tenant',
        'Super Admin',
    ];

    /**
     * Determine if the user can view any proposals.
     */
    public function viewAny(User $user): bool
    {
        // Usuários com roles de gestão podem ver todas as propostas do tenant
        if ($this->hasManagementRole($user)) {
            return true;
        }

        // Vendedores podem ver suas próprias propostas
        if ($user->hasRole('Vendedor')) {
            return true;
        }

        // Clientes podem ver propostas destinadas a eles
        if ($user->hasRole('Cliente')) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can view the proposal.
     */
    public function view(User $user, Proposal $proposal): bool
    {
        // Verifica se pertencem ao mesmo tenant
        if (!$this->belongsToSameTenant($user, $proposal)) {
            return false;
        }

        // Admin e Super Admin podem ver todas as propostas do tenant
        if ($this->hasManagementRole($user)) {
            return true;
        }

        // Vendedor pode ver suas próprias propostas
        if ($user->hasRole('Vendedor')) {
            $salesperson = \App\Models\Salesperson::where('user_id', $user->id)->first();
            if ($salesperson && $proposal->salesperson_id === $salesperson->id) {
                return true;
            }
        }

        // Cliente pode ver propostas destinadas a ele
        if ($user->hasRole('Cliente')) {
            $client = \App\Models\Client::where('user_id', $user->id)->first();
            if ($client && $proposal->client_id === $client->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the user can create proposals.
     */
    public function create(User $user): bool
    {
        // Admin, Super Admin e Vendedores podem criar propostas
        return $this->hasManagementRole($user) || $user->hasRole('Vendedor');
    }

    /**
     * Determine if the user can update the proposal.
     */
    public function update(User $user, Proposal $proposal): bool
    {
        if (!$this->belongsToSameTenant($user, $proposal)) {
            return false;
        }

        // Admin e Super Admin podem atualizar qualquer proposta do tenant
        if ($this->hasManagementRole($user)) {
            return true;
        }

        // Vendedor pode atualizar suas próprias propostas
        if ($user->hasRole('Vendedor')) {
            $salesperson = \App\Models\Salesperson::where('user_id', $user->id)->first();
            if ($salesperson && $proposal->salesperson_id === $salesperson->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the user can delete the proposal.
     */
    public function delete(User $user, Proposal $proposal): bool
    {
        if (!$this->belongsToSameTenant($user, $proposal)) {
            return false;
        }

        // Apenas Admin e Super Admin podem deletar propostas
        return $this->hasManagementRole($user);
    }

    /**
     * Check if user and proposal belong to the same tenant
     */
    protected function belongsToSameTenant(User $user, Proposal $proposal): bool
    {
        // Obtém tenant_id do usuário
        $userTenantId = $user->tenant_id;
        if (!$userTenantId && $user->tenant) {
            $userTenantId = $user->tenant->id;
        }

        // Obtém tenant_id da proposta
        $proposalTenantId = $proposal->tenant_id;
        
        // Se a proposta não tem tenant_id, tenta obter do relacionamento
        if (!$proposalTenantId && $proposal->relationLoaded('tenant')) {
            $proposalTenantId = $proposal->tenant ? $proposal->tenant->id : null;
        }

        // Se ainda não tem tenant_id, carrega o relacionamento
        if (!$proposalTenantId) {
            $proposal->loadMissing('tenant');
            $proposalTenantId = $proposal->tenant ? $proposal->tenant->id : null;
        }

        // Se ainda não tem tenant_id, tenta obter do client ou salesperson
        if (!$proposalTenantId) {
            if ($proposal->client_id) {
                $client = \App\Models\Client::find($proposal->client_id);
                if ($client && $client->tenant_id) {
                    $proposalTenantId = $client->tenant_id;
                }
            }
        }

        if (!$proposalTenantId && $proposal->salesperson_id) {
            $salesperson = \App\Models\Salesperson::find($proposal->salesperson_id);
            if ($salesperson && $salesperson->tenant_id) {
                $proposalTenantId = $salesperson->tenant_id;
            }
        }

        // Se ainda não conseguiu determinar o tenant, permite acesso se o usuário tem role de gestão
        if (!$proposalTenantId && $this->hasManagementRole($user)) {
            return true; // Admin pode acessar mesmo se proposta não tem tenant definido
        }

        return $userTenantId !== null
            && $proposalTenantId !== null
            && $userTenantId === $proposalTenantId;
    }

    /**
     * Check if user has management role
     */
    protected function hasManagementRole(User $user): bool
    {
        return $user->hasAnyRole($this->managementRoles);
    }
}
