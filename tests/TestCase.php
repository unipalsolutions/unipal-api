<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;
use Modules\Identity\Enums\CompanyRole;
use Modules\Identity\Models\Company;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function company(string $slug): Company
    {
        return Company::where('slug', $slug)->firstOrFail();
    }

    /** A signed-in user with the given role in one company. */
    protected function actingAsMember(string $company, CompanyRole $role = CompanyRole::Member): User
    {
        $user = User::factory()->create();
        $user->companies()->attach($this->company($company), ['role' => $role->value]);
        Sanctum::actingAs($user);

        return $user;
    }
}
