# Contributing

Thanks for your interest in improving People & Pixel!

Development setup
- Requirements: PHP 8.4+, Composer, extensions pdo and intl.
- Install deps: composer install
- Copy env: cp .env.example .env
- Start server: composer start and open http://localhost:8080/

Storage modes
- JSON (default): simple flat files under data/. Make sure DATA_DIR is writable.
- DB: set USE_DB=1 and DB_DSN in .env (e.g., sqlite:/abs/path/var/app.sqlite). Migrations run automatically on first use.

Coding guidelines
- PHP: follow PSR-12 style where practical; keep functions small and focused.
- Strict types: enabled across the codebase; prefer DateTimeImmutable for dates.
- Security: validate input via src/Validation and sanitize/escape outputs in templates.
- I18n: user-facing strings should live in lang/ and be referenced via helpers.
- Templates: use Twig (templates/*.twig). Keep business logic in controllers/services.

Commit messages
- Use imperative mood ("Add X", "Fix Y"). Reference the area (router, store, docs) when helpful.

Pull requests
- Describe what you changed and why.
- Include screenshots for UI tweaks if relevant.
- If you add config/env variables, update README.md.

Testing
- A tests/ folder exists. Add unit tests for validators and stores when changing them. If you introduce new helpers, cover edge cases.

Issue reporting
- Include environment (PHP version, DB driver), steps to reproduce, and relevant logs from var/log/app.log.

Security
- Please avoid sharing sensitive data in issues. Report security concerns privately when possible.
