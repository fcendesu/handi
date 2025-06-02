<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Property;
use App\Models\WorkGroup;
use App\Policies\CompanyPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\WorkGroupPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        WorkGroup::class => WorkGroupPolicy::class,
        Company::class => CompanyPolicy::class,
        Property::class => PropertyPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
