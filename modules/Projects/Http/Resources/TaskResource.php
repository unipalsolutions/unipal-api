<?php

namespace Modules\Projects\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Identity\Http\Resources\UserResource;
use Modules\Projects\Models\Task;

/** @mixin Task */
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project' => $this->whenLoaded('project', fn () => ['id' => $this->project->id, 'name' => $this->project->name]),
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'due_on' => $this->due_on?->toDateString(),
            'position' => $this->position,
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
