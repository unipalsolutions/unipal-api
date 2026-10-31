<?php

namespace Modules\Projects\Tests;

use App\Models\User;
use Modules\Identity\Enums\CompanyRole;
use Modules\Projects\Models\Project;
use Tests\TestCase;

class BoardTest extends TestCase
{
    public function test_members_create_projects_and_tasks(): void
    {
        $user = $this->actingAsMember('solutions');

        $project = $this->postJson('/v1/companies/solutions/projects', ['name' => 'UniPal platform'])
            ->assertCreated()
            ->json('data.id');

        $this->postJson("/v1/companies/solutions/projects/{$project}/tasks", [
            'title' => 'Launch the four sites on staging',
            'priority' => 'high',
            'assignee_id' => $user->id,
        ])->assertCreated()->assertJsonPath('data.status', 'todo');

        $this->getJson('/v1/companies/solutions/projects')->assertJsonPath('data.0.open_tasks', 1);
    }

    public function test_moving_a_card_to_done_sets_completed_at(): void
    {
        $this->actingAsMember('solutions');
        $task = Project::factory()->create()->tasks()->create(['title' => 'Brand tokens']);

        $this->patchJson("/v1/companies/solutions/tasks/{$task->id}", ['status' => 'done', 'position' => 2])
            ->assertOk()
            ->assertJsonPath('data.completed_at', fn ($v) => $v !== null);

        $this->patchJson("/v1/companies/solutions/tasks/{$task->id}", ['status' => 'doing'])
            ->assertJsonPath('data.completed_at', null);
    }

    public function test_the_board_filters_by_status_and_assignee(): void
    {
        $user = $this->actingAsMember('solutions');
        $project = Project::factory()->create();
        $project->tasks()->create(['title' => 'Mine', 'assignee_id' => $user->id, 'status' => 'doing']);
        $project->tasks()->create(['title' => 'Other', 'status' => 'doing']);
        $project->tasks()->create(['title' => 'Later', 'status' => 'backlog']);

        $this->getJson('/v1/companies/solutions/tasks?status=doing')->assertJsonCount(2, 'data');
        $this->getJson('/v1/companies/solutions/tasks?mine=1')->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Mine');
    }

    public function test_tasks_of_another_company_are_out_of_reach(): void
    {
        $task = Project::factory()->create()->tasks()->create(['title' => 'Secret']);
        $this->actingAsMember('tax', CompanyRole::Owner);

        $this->getJson('/v1/companies/solutions/tasks')->assertForbidden();
        $this->patchJson("/v1/companies/tax/tasks/{$task->id}", ['status' => 'done'])->assertNotFound();
    }

    public function test_assignees_must_belong_to_the_company(): void
    {
        $this->actingAsMember('solutions');
        $outsider = User::factory()->create();
        $project = Project::factory()->create();

        $this->postJson("/v1/companies/solutions/projects/{$project->id}/tasks", ['title' => 'X', 'assignee_id' => $outsider->id])
            ->assertJsonValidationErrors('assignee_id');
    }
}
