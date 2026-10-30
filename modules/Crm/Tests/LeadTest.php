<?php

namespace Modules\Crm\Tests;

use Modules\Crm\Enums\LeadStage;
use Modules\Crm\Models\Lead;
use Modules\Identity\Enums\CompanyRole;
use Tests\TestCase;

class LeadTest extends TestCase
{
    public function test_the_website_contact_form_creates_a_lead_for_that_company(): void
    {
        $this->postJson('/v1/public/leads', [
            'brand' => 'tax',
            'name' => 'Sara Ali',
            'email' => 'sara@example.com',
            'message' => 'We need help with our VAT return.',
            'source' => 'website',
        ])->assertAccepted();

        $lead = Lead::sole();
        $this->assertSame('tax', $lead->company->slug);
        $this->assertSame(LeadStage::New, $lead->stage);
        $this->assertSame(10, $lead->probability);
        $this->assertSame('TAX-0001', $lead->reference());
        $this->assertNotNull($lead->next_follow_up_on);
    }

    public function test_the_contact_form_rejects_unknown_brands(): void
    {
        $this->postJson('/v1/public/leads', ['brand' => 'nope', 'name' => 'Sara', 'email' => 'sara@example.com', 'message' => 'Hello there, team'])
            ->assertJsonValidationErrors('brand');
    }

    public function test_members_of_other_companies_cannot_see_leads(): void
    {
        Lead::factory()->create();
        $this->actingAsMember('advisors', CompanyRole::Owner);

        $this->getJson('/v1/companies/tax/leads')->assertForbidden();
        $this->postJson('/v1/companies/tax/leads', ['contact_name' => 'X', 'source' => 'referral'])->assertForbidden();
    }

    public function test_a_lead_is_only_reachable_through_its_own_company(): void
    {
        $lead = Lead::factory()->create();
        $this->actingAsMember('group', CompanyRole::Owner);

        $this->getJson("/v1/companies/tax/leads/{$lead->id}")->assertOk();
        $this->getJson("/v1/companies/advisors/leads/{$lead->id}")->assertNotFound();
    }

    public function test_moving_a_lead_updates_probability_and_won_date(): void
    {
        $user = $this->actingAsMember('tax', CompanyRole::Manager);
        $id = $this->postJson('/v1/companies/tax/leads', [
            'contact_name' => 'Omar',
            'organization_name' => 'Emirates Trading LLC',
            'source' => 'referral',
            'services' => ['vat', 'corporate_tax'],
            'proposal_value' => 12000,
        ])->assertCreated()->assertJsonPath('data.owner.id', $user->id)->json('data.id');

        $this->patchJson("/v1/companies/tax/leads/{$id}", ['stage' => 'proposal_sent'])
            ->assertOk()
            ->assertJsonPath('data.probability', 50)
            ->assertJsonPath('data.weighted_value', '6000.00');

        $this->patchJson("/v1/companies/tax/leads/{$id}", ['stage' => 'won'])
            ->assertJsonPath('data.probability', 100)
            ->assertJsonPath('data.won_at', fn ($v) => $v !== null);
    }

    public function test_losing_a_lead_needs_a_reason(): void
    {
        $this->actingAsMember('tax', CompanyRole::Manager);
        $lead = Lead::factory()->create();

        $this->patchJson("/v1/companies/tax/leads/{$lead->id}", ['stage' => 'lost'])->assertJsonValidationErrors('lost_reason');
        $this->patchJson("/v1/companies/tax/leads/{$lead->id}", ['stage' => 'lost', 'lost_reason' => 'price'])->assertOk();
    }

    public function test_members_can_view_but_not_edit_leads(): void
    {
        $this->actingAsMember('tax', CompanyRole::Member);
        $lead = Lead::factory()->create();

        $this->getJson('/v1/companies/tax/leads')->assertOk()->assertJsonCount(1, 'data');
        $this->patchJson("/v1/companies/tax/leads/{$lead->id}", ['notes' => 'x'])->assertForbidden();
    }

    public function test_logging_an_activity_moves_last_contact_and_follow_up(): void
    {
        $this->actingAsMember('tax', CompanyRole::Manager);
        $lead = Lead::factory()->create();

        $this->postJson("/v1/companies/tax/leads/{$lead->id}/activities", [
            'type' => 'call',
            'outcome' => 'positive',
            'next_follow_up_on' => '2026-11-10',
        ])->assertCreated();

        $lead->refresh();
        $this->assertNotNull($lead->last_contact_at);
        $this->assertSame('2026-11-10', $lead->next_follow_up_on->toDateString());
    }

    public function test_pipeline_sums_value_per_stage(): void
    {
        $this->actingAsMember('tax', CompanyRole::Member);
        Lead::factory()->create(['proposal_value' => 10000, 'stage' => LeadStage::ProposalSent, 'probability' => 50]);
        Lead::factory()->create(['proposal_value' => 4000, 'stage' => LeadStage::Won, 'probability' => 100]);

        $this->getJson('/v1/companies/tax/pipeline')
            ->assertOk()
            ->assertJsonPath('open_leads', 1)
            ->assertJsonPath('weighted_forecast', 5000)
            ->assertJsonPath('stages.4.stage', 'proposal_sent')
            ->assertJsonPath('stages.4.value', 10000);
    }
}
