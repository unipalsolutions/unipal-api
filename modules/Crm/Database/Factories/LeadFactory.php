<?php

namespace Modules\Crm\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Crm\Enums\LeadSource;
use Modules\Crm\Models\Lead;
use Modules\Identity\Models\Company;

/** @extends Factory<Lead> */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'company_id' => fn () => Company::where('slug', 'tax')->value('id'),
            'source' => LeadSource::Referral,
            'organization_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '+9715'.fake()->numerify('########'),
            'services' => ['vat'],
        ];
    }
}
