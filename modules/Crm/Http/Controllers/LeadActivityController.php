<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Crm\Enums\ActivityType;
use Modules\Crm\Http\Resources\LeadActivityResource;
use Modules\Crm\Models\Lead;
use Modules\Identity\Enums\Ability;
use Modules\Identity\Models\Company;

class LeadActivityController
{
    /** Logs a call, meeting, email or note, and moves the lead's last contact and next follow-up with it. */
    public function store(Request $request, Company $company, Lead $lead): LeadActivityResource
    {
        $request->user()->authorizeIn($company, Ability::CrmManage);
        $data = $request->validate([
            'type' => ['required', Rule::enum(ActivityType::class)],
            'outcome' => ['nullable', Rule::in(['positive', 'neutral', 'negative', 'no_response', 'won', 'lost'])],
            'notes' => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['nullable', 'integer', 'between:0,1440'],
            'next_follow_up_on' => ['nullable', 'date'],
            'occurred_at' => ['nullable', 'date'],
        ]);
        $data['occurred_at'] ??= now();

        $activity = DB::transaction(function () use ($request, $lead, $data) {
            $activity = $lead->activities()->make($data);
            $activity->user()->associate($request->user());
            $activity->save();

            if ($activity->type !== ActivityType::Note) {
                $lead->last_contact_at = max($lead->last_contact_at, $activity->occurred_at);
            }
            if ($activity->next_follow_up_on) {
                $lead->next_follow_up_on = $activity->next_follow_up_on;
            }
            $lead->save();

            return $activity;
        });

        return new LeadActivityResource($activity->load('user'));
    }
}
