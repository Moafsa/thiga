<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Proposal;
use App\Models\Salesperson;
use App\Policies\CompanyPolicy;
use App\Policies\ProposalPolicy;
use App\Policies\SalespersonPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Company::class => CompanyPolicy::class,
        Proposal::class => ProposalPolicy::class,
        Salesperson::class => SalespersonPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}










