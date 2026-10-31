<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Identity\Enums\Ability;
use Modules\Identity\Enums\CompanyRole;
use Modules\Identity\Models\Company;
use Modules\Identity\Models\Membership;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUlids, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** @return BelongsToMany<Company, $this, Membership> */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->using(Membership::class)->withPivot('role')->withTimestamps();
    }

    /** The user's role in a company: their own membership, or their group-wide role from the parent company. */
    public function roleIn(Company $company): ?CompanyRole
    {
        $memberships = $this->companies->keyBy('slug');
        $own = $memberships->get($company->slug)?->pivot->role;
        if ($own) {
            return $own;
        }
        $parent = $memberships->get(Company::PARENT)?->pivot->role;

        return $parent?->spansGroup() ? $parent : null;
    }

    public function canIn(Company $company, Ability $ability): bool
    {
        return (bool) $this->roleIn($company)?->allows($ability);
    }

    /** @throws AuthorizationException */
    public function authorizeIn(Company $company, Ability $ability): void
    {
        if (! $this->canIn($company, $ability)) {
            throw new AuthorizationException;
        }
    }

    /**
     * Every company the user can act in, each with the role that applies there.
     *
     * @return Collection<int, Company>
     */
    public function accessibleCompanies(): Collection
    {
        return Company::orderByDesc('is_parent')->orderBy('name')->get()
            ->each(fn (Company $c) => $c->setAttribute('role', $this->roleIn($c)))
            ->filter(fn (Company $c) => $c->role !== null)
            ->values();
    }
}
