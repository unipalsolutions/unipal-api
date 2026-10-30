<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Modules\Crm\Enums\LeadStage;
use Modules\Crm\Http\Requests\LeadRequest;
use Modules\Crm\Http\Resources\LeadResource;
use Modules\Crm\Models\Lead;
use Modules\Identity\Enums\Ability;
use Modules\Identity\Models\Company;

class LeadController
{
    public function index(Request $request, Company $company): AnonymousResourceCollection
    {
        $request->user()->authorizeIn($company, Ability::CrmView);
        $filters = $request->validate([
            'stage' => ['nullable', Rule::enum(LeadStage::class)],
            'owner_id' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:100'],
            'follow_up_due' => ['nullable', 'boolean'],
        ]);

        $leads = $company->leads()
            ->with(['owner', 'company'])
            ->when($filters['stage'] ?? null, fn ($q, $stage) => $q->where('stage', $stage))
            ->when($filters['owner_id'] ?? null, fn ($q, $owner) => $q->where('owner_id', $owner))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where(fn ($q) => $q
                ->whereLike('contact_name', "%{$term}%")
                ->orWhereLike('organization_name', "%{$term}%")
                ->orWhereLike('email', "%{$term}%")
                ->orWhereLike('phone', "%{$term}%")))
            ->when($request->boolean('follow_up_due'), fn ($q) => $q->whereDate('next_follow_up_on', '<=', today()))
            ->latest()
            ->paginate(50);

        return LeadResource::collection($leads);
    }

    public function store(LeadRequest $request, Company $company): LeadResource
    {
        $request->user()->authorizeIn($company, Ability::CrmManage);
        $lead = new Lead($request->validated());
        $lead->company()->associate($company);
        $lead->owner_id ??= $request->user()->id;
        $lead->save();

        return new LeadResource($lead->load('owner', 'company'));
    }

    public function show(Request $request, Company $company, Lead $lead): LeadResource
    {
        $request->user()->authorizeIn($company, Ability::CrmView);

        return new LeadResource($lead->load('owner', 'company', 'activities.user'));
    }

    public function update(LeadRequest $request, Company $company, Lead $lead): LeadResource
    {
        $request->user()->authorizeIn($company, Ability::CrmManage);
        $lead->update($request->validated());

        return new LeadResource($lead->load('owner', 'company'));
    }

    public function destroy(Request $request, Company $company, Lead $lead): Response
    {
        $request->user()->authorizeIn($company, Ability::CrmManage);
        $lead->delete();

        return response()->noContent();
    }
}
