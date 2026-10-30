<?php

namespace Modules\Identity\Console;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Identity\Enums\CompanyRole;
use Modules\Identity\Models\Company;

/** Creates or updates a user and their role in one company. The first owner is created this way on each server. */
class CreateUserCommand extends Command
{
    protected $signature = 'unipal:user
        {email}
        {--name= : Display name (defaults to the part of the email before @)}
        {--company=group : Company slug}
        {--role=owner : owner, admin, manager or member}
        {--password= : Leave empty to be asked, or pass --generate}
        {--generate : Generate a random password and print it once}';

    protected $description = 'Create or update a UniPal user and their role in a company';

    public function handle(): int
    {
        $email = strtolower($this->argument('email'));
        $company = Company::where('slug', $this->option('company'))->first();
        $role = CompanyRole::tryFrom($this->option('role'));
        if (! $company || ! $role) {
            $this->error('Unknown company or role.');

            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $password = $this->option('password')
            ?: ($this->option('generate') ? Str::password(20) : null)
            ?: ($user->exists ? null : $this->secret('Password'));
        if (! $user->exists && ! $password) {
            $this->error('A password is required for a new user.');

            return self::FAILURE;
        }

        $user->name = $this->option('name') ?: ($user->name ?? Str::before($email, '@'));
        if ($password) {
            $user->password = $password;
        }
        $user->save();
        $user->companies()->syncWithoutDetaching([$company->id => ['role' => $role->value]]);

        $this->info("{$email} is {$role->value} of {$company->name}.");
        if ($this->option('generate')) {
            $this->line("Password: {$password}");
        }

        return self::SUCCESS;
    }
}
