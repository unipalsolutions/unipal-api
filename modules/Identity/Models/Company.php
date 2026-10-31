<?php

namespace Modules\Identity\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A company of the group. Slugs match the brand ids of unipal-brand (group, advisors, tax, solutions),
 * so the web and mobile apps can map a company to its theme.
 */
#[Fillable(['slug', 'name', 'name_ar', 'domain', 'is_parent'])]
class Company extends Model
{
    use HasUlids;

    public const PARENT = 'group';

    protected function casts(): array
    {
        return ['is_parent' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsToMany<User, $this, Membership> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(Membership::class)->withPivot('role')->withTimestamps();
    }

    public static function parent(): self
    {
        return static::where('slug', self::PARENT)->firstOrFail();
    }
}
