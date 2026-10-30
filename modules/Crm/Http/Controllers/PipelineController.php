<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Crm\Enums\LeadStage;
use Modules\Crm\Models\Lead;
use Modules\Identity\Enums\Ability;
use Modules\Identity\Models\Company;

class PipelineController
{
    /** Totals per stage for the dashboard, like the sheet's Dashboard tab. */
    public function __invoke(Request $request, Company $company): JsonResponse
    {
        $request->user()->authorizeIn($company, Ability::CrmView);

        $rows = Lead::where('company_id', $company->id)
            ->selectRaw('stage, count(*) as leads, coalesce(sum(proposal_value), 0) as value, coalesce(sum(proposal_value * probability / 100.0), 0) as weighted')
            ->groupBy('stage')
            ->get()
            ->keyBy(fn (Lead $row) => $row->stage->value);

        $stages = array_map(fn (LeadStage $stage) => [
            'stage' => $stage->value,
            'leads' => (int) ($rows[$stage->value]->leads ?? 0),
            'value' => round((float) ($rows[$stage->value]->value ?? 0), 2),
            'weighted_value' => round((float) ($rows[$stage->value]->weighted ?? 0), 2),
        ], LeadStage::cases());

        $open = array_filter($stages, fn ($s) => ! LeadStage::from($s['stage'])->isClosed());

        return response()->json([
            'currency' => 'AED',
            'stages' => $stages,
            'open_leads' => array_sum(array_column($open, 'leads')),
            'weighted_forecast' => round(array_sum(array_column($open, 'weighted_value')), 2),
            'follow_ups_due' => Lead::where('company_id', $company->id)->whereDate('next_follow_up_on', '<=', today())->count(),
        ]);
    }
}
