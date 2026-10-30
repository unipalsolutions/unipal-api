<?php

namespace Modules\Crm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Crm\Models\LeadActivity;
use Modules\Identity\Http\Resources\UserResource;

/** @mixin LeadActivity */
class LeadActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'outcome' => $this->outcome,
            'notes' => $this->notes,
            'duration_minutes' => $this->duration_minutes,
            'next_follow_up_on' => $this->next_follow_up_on?->toDateString(),
            'occurred_at' => $this->occurred_at->toIso8601String(),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
