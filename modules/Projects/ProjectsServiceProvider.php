<?php

namespace Modules\Projects;

use App\Support\ModuleServiceProvider;
use Modules\Identity\Models\Company;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

/** Projects and their task board. */
class ProjectsServiceProvider extends ModuleServiceProvider
{
    protected function bootModule(): void
    {
        Company::resolveRelationUsing('projects', fn (Company $company) => $company->hasMany(Project::class));
        Company::resolveRelationUsing('tasks', fn (Company $company) => $company->hasMany(Task::class));
    }
}
