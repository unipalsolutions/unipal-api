<?php

namespace Modules\Identity\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Identity\Models\Company;

/** @mixin Company */
class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'domain' => $this->domain,
            'is_parent' => $this->is_parent,
            'role' => $this->whenHas('role', fn () => $this->role?->value),
            'abilities' => $this->whenHas('role', fn () => array_map(fn ($a) => $a->value, $this->role?->abilities() ?? [])),
        ];
    }
}
