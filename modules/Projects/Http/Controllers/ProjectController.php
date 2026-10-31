<?php

namespace Modules\Projects\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Modules\Identity\Enums\Ability;
use Modules\Identity\Models\Company;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Http\Resources\ProjectResource;
use Modules\Projects\Models\Project;

class ProjectController
{
    public function index(Request $request, Company $company): AnonymousResourceCollection
    {
        $request->user()->authorizeIn($company, Ability::ProjectsView);

        return ProjectResource::collection($company->projects()
            ->with('owner')
            ->withCount(['tasks as open_tasks' => fn ($q) => $q->where('status', '!=', TaskStatus::Done)])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->get());
    }

    public function store(Request $request, Company $company): ProjectResource
    {
        $request->user()->authorizeIn($company, Ability::ProjectsManage);
        $project = $company->projects()->make($this->validated($request, $company));
        $project->owner_id ??= $request->user()->id;
        $project->save();

        return new ProjectResource($project->load('owner'));
    }

    public function show(Request $request, Company $company, Project $project): ProjectResource
    {
        $request->user()->authorizeIn($company, Ability::ProjectsView);

        return new ProjectResource($project->load('owner'));
    }

    public function update(Request $request, Company $company, Project $project): ProjectResource
    {
        $request->user()->authorizeIn($company, Ability::ProjectsManage);
        $project->update($this->validated($request, $company, partial: true));

        return new ProjectResource($project->load('owner'));
    }

    public function destroy(Request $request, Company $company, Project $project): Response
    {
        $request->user()->authorizeIn($company, Ability::ProjectsManage);
        $project->delete();

        return response()->noContent();
    }

    private function validated(Request $request, Company $company, bool $partial = false): array
    {
        return $request->validate([
            'name' => [$partial ? 'sometimes' : 'required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],
            'status' => ['sometimes', Rule::enum(ProjectStatus::class)],
            'owner_id' => ['nullable', Rule::exists('company_user', 'user_id')->where('company_id', $company->id)],
            'starts_on' => ['nullable', 'date'],
            'due_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
        ]);
    }
}
