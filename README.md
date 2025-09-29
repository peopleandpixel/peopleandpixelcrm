# People & Pixel – Basic CRM

A small PHP application to manage contacts, tasks, times, payments, employees, candidates, and simple inventory. It can run entirely on JSON files, or on a database via PDO.

Highlights
- Clean front controller and path-based router (GET/POST, 404/405 handlers)
- Lightweight service container (Config, Logger, Router, Twig, Stores)
- Pluggable persistence: JSON files (atomic, locked) or DB (with migrations)
- Twig templates with i18n helpers and basic view utilities
- Validation layer and normalized domain models
- Error handling with Monolog and friendly error pages

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

Security and safety
- CSRF tokens for forms, sanitization helpers, and output escaping in templates.
- Basic session auth can be enabled/used for protected actions.

Documentation
- Routing: docs/routing.md
- Entities: docs/entities.md
- Contributing: docs/CONTRIBUTING.md

Troubleshooting
- Logs: var/log/app.log (level depends on APP_ENV/LOG_LEVEL)
- Clear Twig cache by removing var/cache/twig when developing with caching enabled.
