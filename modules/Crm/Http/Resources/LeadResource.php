<?php

namespace Modules\Crm\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Crm\Models\Lead;
use Modules\Identity\Http\Resources\UserResource;

/** @mixin Lead */
class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference(),
            'stage' => $this->stage->value,
            'source' => $this->source->value,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'organization_name' => $this->organization_name,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_alt' => $this->phone_alt,
            'industry' => $this->industry,
            'services' => $this->services ?? [],
            'client_type' => $this->client_type,
            'proposal_value' => $this->proposal_value,
            'probability' => $this->probability,
            'weighted_value' => $this->weightedValue(),
            'currency' => 'AED',
            'expected_close_on' => $this->expected_close_on?->toDateString(),
            'last_contact_at' => $this->last_contact_at?->toIso8601String(),
            'days_since_last_contact' => $this->last_contact_at ? (int) $this->last_contact_at->diffInDays(now()) : null,
            'next_action' => $this->next_action,
            'next_follow_up_on' => $this->next_follow_up_on?->toDateString(),
            'lost_reason' => $this->lost_reason?->value,
            'message' => $this->message,
            'notes' => $this->notes,
            'stage_changed_at' => $this->stage_changed_at->toIso8601String(),
            'days_in_stage' => (int) $this->stage_changed_at->diffInDays(now()),
            'won_at' => $this->won_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'activities' => LeadActivityResource::collection($this->whenLoaded('activities')),
        ];
    }
}
