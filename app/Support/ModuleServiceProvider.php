<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * Base provider for a module in modules/<Name>.
 *
 * Layout of modules/<Name> (namespace Modules\<Name>): the provider at the root, routes.php
 * (served under /v1 with the api middleware), Database/Migrations, Database/Factories, Tests,
 * and the module's classes (Models, Http, Enums, Policies). Register the provider in bootstrap/providers.php.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $root = $this->moduleRoot();

        if (is_dir($root.'/Database/Migrations')) {
            $this->loadMigrationsFrom($root.'/Database/Migrations');
        }

        if (is_file($root.'/routes.php') && ! $this->app->routesAreCached()) {
            Route::prefix('v1')->middleware('api')->group($root.'/routes.php');
        }

        $this->bootModule();
    }

    protected function bootModule(): void {}

    /** modules/<Name>: the folder that holds the provider. */
    protected function moduleRoot(): string
    {
        return dirname((new ReflectionClass(static::class))->getFileName());
    }
}
