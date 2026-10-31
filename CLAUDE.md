# unipal-api

The core of the UniPal Group platform: one Laravel 13 API (PHP 8.3, PostgreSQL, Redis, Sanctum) behind
`api.unipal.group/v1`. Every client uses it: the public sites and the hub (`unipal-web`), and the mobile and
desktop apps (`unipal-apps`).

## Modules

Business code lives in `modules/<Name>` (namespace `Modules\<Name>`), one folder per domain:

- the provider, which extends `App\Support\ModuleServiceProvider`, is registered in `bootstrap/providers.php`;
- `routes.php` is served under `/v1` with the `api` middleware;
- migrations go in `Database/Migrations` and factories in `Database/Factories`;
- tests go in `Tests` (namespace `Modules\<Name>\Tests`, extending `Tests\TestCase`);
- the domain's own classes go in `Models`, `Enums`, `Http/Controllers`, `Http/Requests` and `Http/Resources`.

| Module | Owns |
| --- | --- |
| Identity | companies, memberships and roles, token auth, `unipal:user` command |
| Crm | leads, activities, pipeline, the public contact-form endpoint |
| Projects | projects and the task board |

Planned modules, one per hub section: Files, Archive, FollowUps, Employees, Payroll, Services
(paid/free services and subscriptions), Partnerships.

Dependencies point one way. A module may use Identity, and Identity never imports another module.
A module adds the relations it needs to `Company` with `Company::resolveRelationUsing(...)` in its provider.

## Rules

- Everything is scoped to a company: `/v1/companies/{company:slug}/...` with `->scopeBindings()`,
  so a record of one company can never be reached through another company's URL.
- Check permissions with abilities, never roles:
  - in a controller, `$request->user()->authorizeIn($company, Ability::X)`;
  - in a FormRequest, `authorize()`.

  `CompanyRole::abilities()` maps roles to abilities. Owners and admins of the `group` company act in every company.
- Company slugs (`group`, `advisors`, `tax`, `solutions`) match the brand ids in `unipal-brand`.
- Primary keys are ULIDs (`HasUlids`). Money is `decimal(14,2)` in AED. Times are stored in UTC.
- Always return API Resources, never models. Error responses are always JSON.
- CRM fields and stage probabilities follow the BD pipeline sheet (`/mnt/project-files/reference/` in the project).

## Commands

- `composer install && cp .env.example .env && php artisan key:generate && touch database/database.sqlite && php artisan migrate`
- `php artisan unipal:user you@unipal.group --role=owner` creates a user. Without `--company` the role is in `group`.
- `php artisan test` runs the tests. They use sqlite in memory; CI runs them on PostgreSQL 16.
- `vendor/bin/pint` formats the code; CI runs `pint --test`.

## Branches and deploy

- `main` is production and `develop` is staging. Work on a feature branch and open a PR into `develop`.
- When CI passes on `main` or `develop`, `.github/workflows/deploy.yml` calls the Forge deploy hook (secret `FORGE_DEPLOY_HOOK_API`).
- The Forge deploy script is in `deploy/forge-deploy.sh`.
