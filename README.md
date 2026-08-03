# Task Management API

A RESTful Task Management System built with Laravel 13 and Sanctum. The codebase
uses domain modules, form requests, API resources, repositories, services,
integer-backed enums, UUID route identifiers, soft deletes, pagination, and
membership-scoped authorization.

## Requirements

For Docker installation, only Docker Engine with the Compose plugin is needed.
For native installation, use:

- PHP 8.3 or newer
- Composer 2
- MySQL 8+ (SQLite is used by the automated tests)
- A queue worker for overdue-task email notifications

## Docker installation

Build and start the API, MySQL, migration runner, queue worker, scheduler, and
Nginx with one command:

```bash
docker compose up --build -d
docker compose ps
```

The stack works without a Docker environment file and uses development-only
defaults. To customize ports, credentials, or Laravel settings, create a local
file and pass it explicitly:

```bash
cp .env.docker.example .env.docker
docker compose --env-file .env.docker up --build -d
```

The application is available at `http://127.0.0.1:8000`, Swagger UI at
`http://127.0.0.1:8000/docs`, and MySQL is exposed to the host on port `3307`.
The `/api` path returns a small JSON index linking to the documentation and
health endpoint.

Migrations run automatically before PHP, the queue worker, and scheduler start.
Load sample data once with:

```bash
docker compose exec app php artisan db:seed --force
```

Useful development commands:

```bash
docker compose logs -f
docker compose exec app php artisan test
docker compose exec app vendor/bin/pint --test
docker compose exec app php artisan migrate:fresh --seed --force
docker compose down
```

`docker compose down` preserves the MySQL data volume. Use
`docker compose down --volumes` only when you intentionally want to delete the
Docker database and start from an empty volume.

## Native installation

```bash
git clone git@github.com:KarimEl-Kady/projectManager.git
cd projectManager
composer install
cp .env.example .env
php artisan key:generate
```

Configure the database in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=projectmanager
DB_USERNAME=root
DB_PASSWORD=
```

Create the schema and sample data, then start the API:

```bash
php artisan migrate --seed
php artisan serve
```

The seeded demo account is:

```text
Email: demo@example.com
Password: password
```

For queued overdue notifications, run the worker and scheduler in separate
terminals:

```bash
php artisan queue:work
php artisan schedule:work
```

## API authentication

The default base URL is `http://127.0.0.1:8000/api`. Register or log in to receive
a Sanctum token. Protected endpoints require:

```http
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

## Endpoints

| Method | Endpoint | Description |
| --- | --- | --- |
| `POST` | `/auth/register` | Register and receive a token |
| `POST` | `/auth/login` | Log in and receive a token |
| `GET` | `/auth/me` | Return the authenticated user |
| `POST` | `/auth/logout` | Revoke the current token |
| `GET` | `/projects` | Paginated projects belonging to the user |
| `POST` | `/projects` | Create a project and attach the user |
| `GET` | `/projects/{project_uuid}` | View an accessible project |
| `PATCH` | `/projects/{project_uuid}` | Update an accessible project |
| `DELETE` | `/projects/{project_uuid}` | Soft-delete a project and its tasks |
| `GET` | `/projects/{project_uuid}/tasks` | Paginated and filtered task list |
| `POST` | `/projects/{project_uuid}/tasks` | Create a task |
| `GET` | `/projects/{project_uuid}/tasks/{task_uuid}` | View a task |
| `PATCH` | `/projects/{project_uuid}/tasks/{task_uuid}` | Update a task |
| `DELETE` | `/projects/{project_uuid}/tasks/{task_uuid}` | Soft-delete a task |
| `GET` | `/dashboard` | Return project and task metrics |

### Project payload

```json
{
  "title": "Task Management API",
  "description": "Laravel technical assessment",
  "status": "active"
}
```

Project statuses are `active`, `completed`, and `archived`.

Project listing supports:

```text
?status=active&search=management&sort=created_at&direction=desc&per_page=15&page=1
```

### Task payload

```json
{
  "title": "Write feature tests",
  "description": "Cover authorization and filtering",
  "priority": "high",
  "status": "in_progress",
  "due_date": "2026-08-10"
}
```

Task statuses are `todo`, `in_progress`, and `done`. Priorities are `low`,
`medium`, and `high`.

Task listing supports:

```text
?status=in_progress&priority=high&search=tests&sort=due_date&direction=asc&per_page=15&page=1
```

## Response behavior

- Creation returns `201 Created`.
- Successful deletion returns `204 No Content`.
- Invalid input returns `422 Unprocessable Entity` with Laravel's `errors` map.
- Missing/invalid authentication returns `401 Unauthorized`.
- Resources outside the authenticated user's project membership return `404`
  to avoid disclosing their existence.
- Collection endpoints include standard Laravel pagination `links` and `meta`.

## Architecture

```text
app/Modules/
├── Core/       shared requests, repositories, services, module generators
├── User/       Sanctum authentication and user domain
├── Project/    project CRUD and membership scope
├── Task/       task CRUD, filters, activities, and overdue notification job
└── Dashboard/  aggregate metrics
```

`project_users` represents project membership. Every project/task query is
scoped through that relationship. Database values use compact integer-backed
enums while API requests and responses use readable strings.

Task changes are written to `task_activities`. A daily queued job finds overdue,
unfinished tasks, emails project members once, and records
`overdue_notified_at`.

## Database and sample data

Migration files create:

- `users`
- `personal_access_tokens`
- `projects`
- `project_users`
- `tasks`
- `task_notes`
- `task_activities`
- Laravel cache, session, and queue tables

Run `php artisan migrate:fresh --seed` to rebuild all tables and create the demo
user plus sample projects, tasks, notes, and activities.

## Tests and quality checks

```bash
php artisan test
vendor/bin/pint --test
composer validate --no-check-publish
```

The feature suite covers auth, validation, user isolation, project CRUD, task
CRUD and filters, soft deletes, activity logging, dashboard metrics, factories,
module generation, and one-time overdue notifications.

## API documentation

- Interactive Swagger UI: `http://127.0.0.1:8000/docs`
- Swagger aliases: `/swagger` and `/api/documentation`
- OpenAPI: [`docs/openapi.yaml`](docs/openapi.yaml)
- Postman: [`docs/Task-Management.postman_collection.json`](docs/Task-Management.postman_collection.json)
- Module generator guide: [`app/Modules/README.md`](app/Modules/README.md)
