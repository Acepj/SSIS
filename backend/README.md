# SSIS Backend — Plain PHP REST API

JSON-file-backed REST API for the Student Services Information System.
No framework, no database: plain PHP 8.2 with a small layered architecture
(Controllers → Services → Repositories → JsonStore).

## Requirements

- PHP >= 8.2 with `json`, `mbstring`, `openssl`, `session`, `curl` (for tests)
- Composer (only needed for PHPUnit; the app itself has zero dependencies)

> On this machine PHP lives at `C:\xammp\php\php.exe` (not on PATH). Prefix the
> commands below with that path, or add it to PATH.

## Setup

```bash
cd backend
cp .env.example .env          # local settings (git-ignored)

# Development-only account provisioning (prints generated passwords ONCE):
php scripts/create-demo-admin.php --username=ssis.admin --name="SSIS Administrator"
php scripts/seed-demo-data.php    # demo student accounts, idempotent

# Run the API (front controller routes everything):
php -S 127.0.0.1:8000 -t public public/index.php
```

The frontend dev server proxies `/api/*` to `http://127.0.0.1:8000`, so the
login page at `http://localhost:5173` works end-to-end once both servers run.

**Reset a password / re-provision:** re-run `create-demo-admin.php` with the
same `--username`; it resets the password and reactivates the account. No
default admin password ships with the project — generated passwords are shown
exactly once, and only bcrypt hashes are stored in `storage/data/users.json`.

## API conventions

Success envelope: `{ "success": true, "data": ..., "message": "..." }`
Error envelope: `{ "success": false, "error": { "code", "message", "fields"? } }`

| Status | Meaning                                                     |
| ------ | ----------------------------------------------------------- |
| 200/201 | OK / created                                                |
| 400    | Malformed request (invalid JSON)                            |
| 401    | Unauthenticated / invalid credentials / inactive account    |
| 403    | Forbidden, CSRF failure, or disallowed CORS origin          |
| 404    | Resource or route not found                                 |
| 409    | Conflict (duplicate username / student ID)                  |
| 422    | Validation error (field messages in `error.fields`)         |
| 429    | Rate limited (Retry-After header set)                       |
| 500    | Unexpected server error (details logged, never returned)    |

### Endpoints (Phase 2 — authentication)

| Method | Path                      | Auth     | Description                                    |
| ------ | ------------------------- | -------- | ---------------------------------------------- |
| GET    | /api/v1/auth/csrf         | none     | Issues a CSRF token bound to the session       |
| POST   | /api/v1/auth/login        | CSRF+rate | Creates a session for valid credentials       |
| POST   | /api/v1/auth/logout       | CSRF     | Destroys the server-side session               |
| GET    | /api/v1/auth/me           | session  | Current authenticated user                     |

Student and admin endpoints are appended in later phases (see routes/api.php).

Example:

```bash
# 1. Get CSRF token + session cookie
curl -c jar.txt http://127.0.0.1:8000/api/v1/auth/csrf
# 2. Login (use the token from step 1)
curl -b jar.txt -c jar.txt -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" -H "X-CSRF-Token: <token>" \
  -d '{"identifier":"ssis.admin","password":"<generated>","remember":false}'
```

## Security model

- **Sessions**: HttpOnly + SameSite=Lax cookies (Secure under HTTPS), strict
  mode, ID regenerated on login. The PHP session is the only auth state; roles
  are re-read from `users.json` on every request — never from the browser.
- **Passwords**: `password_hash()` (bcrypt) / `password_verify()` with
  opportunistic rehashing. Hashes never appear in API responses or logs.
- **CSRF**: all POST/PATCH/PUT/DELETE requests require a valid
  `X-CSRF-Token` bound to the session (403 `CSRF_TOKEN_INVALID`).
- **CORS**: credentialed responses only for origins listed in `CORS_ORIGINS`;
  disallowed origins get 403. No wildcard credentialed CORS.
- **Rate limiting**: login is limited per IP+identifier (default 5 attempts /
  15 min) → 429 with `Retry-After`.
- **Errors**: warnings/notices are converted to exceptions and logged; clients
  only ever see the JSON envelope — never stack traces or paths.
- **Provisioning guards**: demo scripts refuse to run when `APP_ENV=production`.

## JSON storage

Files in `storage/data/` (outside `public/`, never web-accessible):
`users.json`, `students.json`, plus files for later phases (document requests,
enrollment, courses, notifications, activity logs).

Safeguards in `JsonStore`:

- **Locking**: a sidecar `.lock` file with `flock()` serializes every
  read/write/transaction, preventing lost updates between processes.
- **Atomic writes**: temp file + `rename()` — a crash can never truncate the
  live file.
- **Automatic recovery**: each write keeps the previous good state as
  `.bak`; a corrupt main file is restored from it on the next read.
- **Backups**: `scripts/backup-data.php` makes timestamped copies under
  `storage/backups/` and prunes old ones (keep=10).

**Recovery procedure**: stop the app → copy the newest backup's `*.json` into
`storage/data/` → start the app. If both the main file and `.bak` are corrupt,
restore from a timestamped backup.

### Limitations (documented, not hidden)

JSON storage is fine for a small prototype, not for production concurrency:
there are no cross-file transactions, no indexes (every query scans a file),
no replication, and unbounded growth. The repository layer is the single seam
to replace with a real database — business logic never touches files directly.

## Scripts

| Script                          | Purpose                                            |
| ------------------------------- | -------------------------------------------------- |
| `scripts/create-demo-admin.php` | Create/reset a dev administrator (random password) |
| `scripts/seed-demo-data.php`    | Seed demo students (idempotent, random passwords)  |
| `scripts/backup-data.php`       | Timestamped backup + pruning                       |

## Testing

```bash
composer install            # once (installs PHPUnit; use --prefer-source without zip ext)
php vendor/phpunit/phpunit/phpunit
```

- `tests/Unit` — JsonStore (locking, atomicity, corruption recovery),
  IdGenerator, LoginValidator, Csrf, UserRepository.
- `tests/Integration` — real HTTP flows against an isolated `php -S` instance
  with isolated storage: login/logout, CSRF rejection, inactive accounts,
  malformed JSON, 404s, CORS rejection, and the 429 rate limit.

## Layout

```
public/index.php   front controller / dispatcher (CORS, routing, middleware)
routes/api.php     route table (method, path, controller, middleware)
src/Controllers    request/response handling
src/Services       business rules (auth, ...)
src/Repositories   data access (JsonStore-backed)
src/Middleware     auth, admin, CSRF, rate limiting
src/Validators     server-side input validation
src/Helpers        Response, Request, Session, Csrf, Authorization, Logger...
config/            app, cors, storage configuration (.env-driven)
bootstrap/app.php   env, autoloading, error handlers, sessions
storage/           data (JSON), logs, backups — never web-accessible
tests/             Unit + Integration (PHPUnit)
```
