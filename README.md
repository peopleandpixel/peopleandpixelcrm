# People & Pixel – Basic CRM

A small PHP application to manage contacts, tasks, times, payments, employees, candidates, and simple inventory. It can run entirely on JSON files, or on a database via PDO.

Highlights
- Clean front controller and path-based router (GET/POST, 404/405 handlers)
- Lightweight service container (Config, Logger, Router, Twig, Stores)
- Pluggable persistence: JSON files (atomic, locked) or DB (with migrations)
- Twig templates with i18n helpers and basic view utilities
- Validation layer and normalized domain models
- Error handling with Monolog and friendly error pages

Project goals
- Provide a lightweight, self-hostable CRM for small teams with minimal operational overhead.
- Keep the codebase understandable and maintainable without heavy frameworks.
- Support JSON-based storage by default; allow opting into a relational database via PDO.

Non-goals
- Competing with full-featured enterprise CRMs.
- Real-time collaboration or complex access control models beyond simple roles.
- Multi-tenant, horizontally scalable SaaS out of the box.

Priorities
- Data safety and integrity over feature breadth.
- Clear, predictable UX flows (POST/redirect-GET, explicit feedback).
- Simplicity in deployment (PHP built-ins, optional DB) and troubleshooting.

Supported PHP versions and environments
- PHP: 8.4+ (tested locally on 8.4; CI will enforce 8.4+).
- Environments: dev, test, prod (via APP_ENV). JSON storage works without DB; DB mode via PDO is optional.

Performance targets
- List pages should render under 200ms server time with up to 2,000 items in JSON mode on modest hardware.
- JSON store operations are atomic with file locking; DB mode should use prepared statements and indexes for O(log n) lookups on typical keys.

Quality bar and coding standards
- Coding standard: PSR-12.
- Types: use scalar and return types throughout; prefer dedicated value objects/DTOs for complex shapes.
- Strict types: files under src/ and tests/ should declare(strict_types=1) as work progresses.
- Security: escape output in Twig (|e), validate and sanitize inputs in the validation layer, CSRF on all mutating forms.
- Tooling (recommended):
  - Lint/style: PHP-CS-Fixer with PSR-12 ruleset.
  - Static analysis: PHPStan at level 6–8 with a baseline for legacy code.
- Suggested composer scripts (add when tools are installed):
  - "lint": "php-cs-fixer fix --dry-run --diff",
  - "fix": "php-cs-fixer fix",
  - "stan": "phpstan analyse -c phpstan.neon",

Requirements
- PHP 8.4+
- Extensions: pdo, intl
- Composer

Quick start (JSON mode)
1. Install dependencies:
   composer install
2. Create a .env from example:
   cp .env.example .env
3. Ensure DATA_DIR exists (defaults to <project>/data) and is writable.
4. Start the server:
   composer start
5. Open http://localhost:8080/

Switch to database mode (SQLite example)
1. Create a writable location for your SQLite file, e.g. var/app.sqlite
2. Set the following in .env:
   USE_DB=1
   DB_DSN=sqlite:/absolute/path/to/your/project/var/app.sqlite
3. First run will auto-run SQL migrations found in migrations/ via the internal Migrator. For other drivers (MySQL, etc.), set DB_USER/DB_PASS as needed.

Configuration overview
This project uses vlucas/phpdotenv to load environment variables from .env.

Required in all modes:
- APP_ENV: dev | test | prod (default: prod; dev enables debug by default)
- APP_DEBUG: 0/1 (in addition to APP_ENV)
- DEFAULT_LANG: UI language (en, de, pt supported; default: en)
- INSTALLED: 0/1 flag used by the installer redirect (set to 1 after setup)
- DATA_DIR: absolute path for JSON storage (default: <project>/data)

Switching to database mode:
- USE_DB: 0/1 — choose DB instead of JSON (0 by default)
- DB_DSN: PDO DSN (sqlite:/abs/path/app.sqlite, mysql:host=...;dbname=...)
- DB_USER, DB_PASS: as required by your DSN

Failure modes:
- If USE_DB=1 is set but DB_DSN is missing/empty, the app will fail fast with a configuration error at boot (thrown by Config). Set DB_DSN or disable USE_DB.
- If DATA_DIR is not writable in JSON mode, the app will log write errors and operations may fail. Ensure the directory exists and is writable.
- Invalid APP_ENV values are treated as prod.

Architecture overview
- Front controller: public/index.php
  - Boots environment, sets up error handling and the container, registers routes, and dispatches.
- Router: src/Router.php
  - Minimal path+method router with not-found and method-not-allowed handlers.
- Container: src/Container.php
  - Wires services (Config, Logger, Twig, Router, Stores). Stores are resolved per entity and switch between JSON/DB according to Config.
- Config: src/Config.php
  - Centralizes env, paths, language, debug flags, and DB selection. Fails fast on invalid DB config.
- Persistence: src/JsonStore.php and src/DbStore.php, StoreInterface
  - JsonStore uses file locking, integrity checks, and atomic writes.
  - DbStore uses prepared statements and runs migrations via src/Migrator.php.
- Domain models: src/Domain/* with validation in src/Validation
- Views: templates/*.twig with helpers and i18n (see lang/)

Internationalization
- UI strings live under lang/. Default language is DEFAULT_LANG; per-session overrides are supported. Twig exposes __(), n__(), format_date(), etc.

Reminders scheduler
- A simple CLI scheduler emits due task reminders based on each task's reminder_at field and marks last_reminded_at.
- Run manually: php bin/schedule dry-run (no changes) or php bin/schedule run
- Cron example (every minute):
  * * * * * /usr/bin/php /path/to/peopleandpixel/bin/schedule run >> /path/to/peopleandpixel/var/log/reminders.log 2>&1

Security and safety
- CSRF tokens for forms, sanitization helpers, and output escaping in templates.
- Basic session auth can be enabled/used for protected actions.

Documentation
- Routing: docs/routing.md
- Entities: docs/entities.md
- Contributing: docs/CONTRIBUTING.md
- Competitive analysis and improvement roadmap: docs/competitive-analysis.md

Install as an app (PWA)
- Chrome/Edge (desktop & Android): When you see the “Install” banner in the app, click Install. You can also use the browser menu → Install app (or Add to Home screen on Android).
- iOS Safari: Tap the Share button, then “Add to Home Screen”. The app supports offline mode and opens full-screen.
- Requirements: Use https, keep the app open for a moment so the service worker can cache the shell, and then reload.

Troubleshooting
- Logs: var/log/app.log (level depends on APP_ENV/LOG_LEVEL)
- Clear Twig cache by removing var/cache/twig when developing with caching enabled.


## Developer experience

Common composer scripts:
- `composer serve` — start the built-in web server at http://localhost:8080
- `composer setup` — copy `.env.example` to `.env` (if missing), install deps, and seed demo data
- `composer seed` — seed demo data
- `composer reset` — reset local data/cache and re-seed (use `--keep-uploads` with `php bin/reset-dev.php` to preserve uploads)
- `composer lint` — PHP-CS-Fixer dry-run
- `composer fix` — PHP-CS-Fixer apply fixes
- `composer stan` — run PHPStan static analysis

## Docker (optional)

A minimal `docker-compose.sample.yml` is included to run the app via PHP's built-in server:

1. Copy the sample file as `docker-compose.yml`:
   `cp docker-compose.sample.yml docker-compose.yml`
2. Start the container:
   `docker compose up`
3. Open http://localhost:8080

Notes:
- By default, JSON storage is used and the project directory is mounted into the container. Ensure `data/` is writable.
- To use SQLite inside the container, set `USE_DB=1` and `DB_DSN=sqlite:/app/var/app.sqlite` in the `environment` section and create the file under `var/` on your host.
