<?php

namespace Modules\Projects\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Identity\Http\Resources\UserResource;
use Modules\Projects\Models\Project;

/** @mixin Project */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status->value,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'starts_on' => $this->starts_on?->toDateString(),
            'due_on' => $this->due_on?->toDateString(),
            'open_tasks' => $this->whenHas('open_tasks'),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
