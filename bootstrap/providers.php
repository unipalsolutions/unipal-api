<?php

use App\Providers\AppServiceProvider;
use Modules\Crm\CrmServiceProvider;
use Modules\Identity\IdentityServiceProvider;
use Modules\Projects\ProjectsServiceProvider;

return [
    AppServiceProvider::class,
    IdentityServiceProvider::class,
    CrmServiceProvider::class,
    ProjectsServiceProvider::class,
];
