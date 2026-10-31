<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Crm\Enums\LeadSource;
use Modules\Crm\Models\Lead;
use Modules\Identity\Models\Company;

class PublicLeadController
{
    /** Contact form of the public sites. `brand` is the company slug of the site the visitor used. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'brand' => ['required', 'string', 'exists:companies,slug'],
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'email', 'max:200'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $lead = new Lead([
            'source' => LeadSource::Website,
            'contact_name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
            'next_follow_up_on' => today()->addWeekday(),
        ]);
        $lead->company()->associate(Company::where('slug', $data['brand'])->firstOrFail());
        $lead->save();

        return response()->json(['status' => 'received'], 202);
    }
}
