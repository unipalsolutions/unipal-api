# unipal-api

UniPal Group platform API: Laravel 13, modular (`modules/`), PostgreSQL, Redis, Sanctum.

```sh
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate
php artisan unipal:user you@unipal.group --role=owner
php artisan serve
```

Point `UNIPAL_API_URL` in `unipal-web` at `http://localhost:8000`. See `CLAUDE.md` for modules, rules and deployment.

## Endpoints (v1)

| Method | Path | |
| --- | --- | --- |
| POST | /v1/auth/token | email, password, device_name → token |
| GET | /v1/me | user and their companies, roles and abilities |
| POST | /v1/public/leads | website contact form (brand, name, email, phone, message) |
| GET | /v1/companies/{company}/pipeline | totals per stage, weighted forecast |
| CRUD | /v1/companies/{company}/leads | leads; POST …/leads/{lead}/activities logs activity |
| CRUD | /v1/companies/{company}/projects | projects; POST …/projects/{project}/tasks adds a task |
| GET, PATCH, DELETE | /v1/companies/{company}/tasks | the task board |
