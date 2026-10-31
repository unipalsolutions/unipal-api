<?php

namespace Modules\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Crm\Enums\LeadSource;
use Modules\Crm\Enums\LeadStage;
use Modules\Crm\Enums\LostReason;
use Modules\Identity\Enums\Ability;

/** Create (POST) and partial update (PATCH) of a lead. */
class LeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canIn($this->route('company'), Ability::CrmManage);
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';
        $company = $this->route('company');

        return [
            'contact_name' => [$required, 'string', 'max:150'],
            'source' => [$required, Rule::enum(LeadSource::class)],
            'stage' => ['sometimes', Rule::enum(LeadStage::class)],
            'owner_id' => ['nullable', Rule::exists('company_user', 'user_id')->where('company_id', $company->id)],
            'organization_name' => ['nullable', 'string', 'max:200'],
            'email' => ['nullable', 'email', 'max:200'],
            'phone' => ['nullable', 'string', 'max:30'],
            'phone_alt' => ['nullable', 'string', 'max:30'],
            'industry' => ['nullable', 'string', 'max:100'],
            'services' => ['nullable', 'array', 'max:20'],
            'services.*' => ['string', 'max:50'],
            'client_type' => ['nullable', Rule::in(['new', 'existing', 'cross_sell'])],
            'proposal_value' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'probability' => ['nullable', 'integer', 'between:0,100'],
            'expected_close_on' => ['nullable', 'date'],
            'next_action' => ['nullable', 'string', 'max:255'],
            'next_follow_up_on' => ['nullable', 'date'],
            'lost_reason' => ['nullable', Rule::enum(LostReason::class), Rule::requiredIf(fn () => in_array($this->input('stage'), ['lost', 'paused'], true))],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
