<?php

namespace Modules\Projects\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Modules\Identity\Enums\Ability;
use Modules\Identity\Models\Company;
use Modules\Projects\Enums\TaskPriority;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Http\Resources\TaskResource;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

class TaskController
{
    /** The company board: every task across its projects, filtered by project, assignee or status. */
    public function index(Request $request, Company $company): AnonymousResourceCollection
    {
        $request->user()->authorizeIn($company, Ability::ProjectsView);
        $filters = $request->validate([
            'project_id' => ['nullable', 'string'],
            'assignee_id' => ['nullable', 'string'],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'mine' => ['nullable', 'boolean'],
        ]);

        return TaskResource::collection($company->tasks()
            ->with(['project', 'assignee'])
            ->when($filters['project_id'] ?? null, fn ($q, $id) => $q->where('project_id', $id))
            ->when($filters['assignee_id'] ?? null, fn ($q, $id) => $q->where('assignee_id', $id))
            ->when($request->boolean('mine'), fn ($q) => $q->where('assignee_id', $request->user()->id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('position')
            ->limit(500)
            ->get());
    }

    public function store(Request $request, Company $company, Project $project): TaskResource
    {
        $request->user()->authorizeIn($company, Ability::ProjectsManage);
        $task = $project->tasks()->make($this->validated($request, $company));
        $task->created_by = $request->user()->id;
        $task->save();

        return new TaskResource($task->load('project', 'assignee'));
    }

    /** Also used by the board to move a card: send status and position. */
    public function update(Request $request, Company $company, Task $task): TaskResource
    {
        $request->user()->authorizeIn($company, Ability::ProjectsManage);
        $task->update($this->validated($request, $company, partial: true));

        return new TaskResource($task->load('project', 'assignee'));
    }

    public function destroy(Request $request, Company $company, Task $task): Response
    {
        $request->user()->authorizeIn($company, Ability::ProjectsManage);
        $task->delete();

        return response()->noContent();
    }

    private function validated(Request $request, Company $company, bool $partial = false): array
    {
        return $request->validate([
            'title' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'status' => ['sometimes', Rule::enum(TaskStatus::class)],
            'priority' => ['sometimes', Rule::enum(TaskPriority::class)],
            'assignee_id' => ['nullable', Rule::exists('company_user', 'user_id')->where('company_id', $company->id)],
            'due_on' => ['nullable', 'date'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ]);
    }
}
