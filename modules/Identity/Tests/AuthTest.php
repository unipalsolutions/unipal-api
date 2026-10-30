<?php

namespace Modules\Identity\Tests;

use App\Models\User;
use Modules\Identity\Enums\CompanyRole;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_issues_a_token_for_valid_credentials(): void
    {
        User::factory()->create(['email' => 'nour@unipal.group', 'password' => 'secret-pass']);

        $this->postJson('/v1/auth/token', ['email' => 'Nour@unipal.group', 'password' => 'secret-pass', 'device_name' => 'hub'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']])
            ->assertJsonPath('user.email', 'nour@unipal.group');
    }

    public function test_rejects_a_wrong_password(): void
    {
        User::factory()->create(['email' => 'nour@unipal.group']);

        $this->postJson('/v1/auth/token', ['email' => 'nour@unipal.group', 'password' => 'nope', 'device_name' => 'hub'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_protected_routes_need_a_token(): void
    {
        $this->getJson('/v1/me')->assertUnauthorized();
    }

    public function test_logout_revokes_the_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('hub')->plainTextToken;

        $this->withToken($token)->postJson('/v1/auth/logout')->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_me_lists_only_the_companies_the_user_belongs_to(): void
    {
        $this->actingAsMember('tax', CompanyRole::Manager);

        $this->getJson('/v1/me')
            ->assertOk()
            ->assertJsonCount(1, 'companies')
            ->assertJsonPath('companies.0.slug', 'tax')
            ->assertJsonPath('companies.0.role', 'manager');
    }

    public function test_group_owners_act_in_every_company(): void
    {
        $this->actingAsMember('group', CompanyRole::Owner);

        $this->getJson('/v1/companies')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.slug', 'group');
    }

    public function test_group_members_do_not_span_the_group(): void
    {
        $this->actingAsMember('group', CompanyRole::Member);

        $this->getJson('/v1/companies')->assertJsonCount(1, 'data');
    }

    public function test_create_user_command_sets_the_role(): void
    {
        $this->artisan('unipal:user', ['email' => 'Admin@unipal.group', '--company' => 'tax', '--role' => 'admin', '--generate' => true])
            ->assertSuccessful();

        $user = User::where('email', 'admin@unipal.group')->firstOrFail();
        $this->assertSame(CompanyRole::Admin, $user->roleIn($this->company('tax')));
    }
}
