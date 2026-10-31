<?php

namespace Modules\Identity;

use App\Support\ModuleServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Identity\Console\CreateUserCommand;

/** Users, companies, memberships and roles, and token authentication. */
class IdentityServiceProvider extends ModuleServiceProvider
{
    protected function bootModule(): void
    {
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(20)->by($request->ip()),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([CreateUserCommand::class]);
        }
    }
}
