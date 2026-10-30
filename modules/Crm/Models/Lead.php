<?php

namespace Modules\Crm\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Crm\Database\Factories\LeadFactory;
use Modules\Crm\Enums\LeadSource;
use Modules\Crm\Enums\LeadStage;
use Modules\Crm\Enums\LostReason;
use Modules\Identity\Models\Company;

#[Fillable([
    'owner_id', 'source', 'stage', 'organization_name', 'contact_name', 'email', 'phone', 'phone_alt',
    'industry', 'services', 'client_type', 'proposal_value', 'probability', 'expected_close_on',
    'last_contact_at', 'next_action', 'next_follow_up_on', 'lost_reason', 'message', 'notes',
])]
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'source' => LeadSource::class,
            'stage' => LeadStage::class,
            'lost_reason' => LostReason::class,
            'services' => 'array',
            'proposal_value' => 'decimal:2',
            'probability' => 'integer',
            'expected_close_on' => 'date',
            'next_follow_up_on' => 'date',
            'last_contact_at' => 'datetime',
            'stage_changed_at' => 'datetime',
            'won_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Lead $lead) {
            $lead->stage ??= LeadStage::New;
            $lead->probability ??= $lead->stage->defaultProbability();
            $lead->stage_changed_at ??= now();
            // Per-company running number, shown as the lead reference (e.g. TAX-0042).
            $lead->number ??= (int) DB::table('leads')->where('company_id', $lead->company_id)->max('number') + 1;
        });

        static::updating(function (Lead $lead) {
            if ($lead->isDirty('stage')) {
                $lead->stage_changed_at = now();
                $lead->won_at = $lead->stage === LeadStage::Won ? now() : null;
                if (! $lead->isDirty('probability')) {
                    $lead->probability = $lead->stage->defaultProbability();
                }
            }
        });
    }

    protected static function newFactory(): LeadFactory
    {
        return LeadFactory::new();
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<LeadActivity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest('occurred_at');
    }

    public function reference(): string
    {
        return strtoupper($this->company->slug).'-'.str_pad((string) $this->number, 4, '0', STR_PAD_LEFT);
    }

    public function weightedValue(): ?string
    {
        return $this->proposal_value === null ? null : number_format($this->proposal_value * $this->probability / 100, 2, '.', '');
    }
}
