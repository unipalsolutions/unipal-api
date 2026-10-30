<?php

namespace Modules\Crm\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Crm\Enums\ActivityType;

#[Fillable(['type', 'outcome', 'notes', 'duration_minutes', 'next_follow_up_on', 'occurred_at'])]
class LeadActivity extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'next_follow_up_on' => 'date',
            'occurred_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
