<?php

namespace Modules\Crm;

use App\Support\ModuleServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Crm\Models\Lead;
use Modules\Identity\Models\Company;

/** Leads, the sales pipeline and activity log, based on the BD pipeline sheet. */
class CrmServiceProvider extends ModuleServiceProvider
{
    protected function bootModule(): void
    {
        // Identity does not know about modules built on it; each module adds its relations to Company.
        Company::resolveRelationUsing('leads', fn (Company $company) => $company->hasMany(Lead::class));

        RateLimiter::for('public-leads', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
    }
}
