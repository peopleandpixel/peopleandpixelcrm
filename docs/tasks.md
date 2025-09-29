# People & Pixel – Improvement Tasks Checklist

Note: The checklist is ordered from foundational architecture to developer experience and polish. Every enumerated item starts with [ ] and can be checked off upon completion. Subtasks are optional but also actionable.

1. [x] Establish a robust front controller and routing layer
   - [x] Introduce a Router with HTTP-method-aware routes and middleware support
   - [x] Centralize route registration and remove ad-hoc routing logic from controllers/templates
   - [x] Provide 404/405 handling and a default error route

2. [x] Introduce Request/Response abstractions
   - [x] Wrap global $_GET/$_POST/$_SERVER access behind a Request object
   - [x] Standardize redirects, JSON, and HTML responses via a Response helper
   - [x] Ensure consistent URL generation via a url() service

3. [x] Implement a lightweight Dependency Injection container
   - [x] Register stores, logger, config, translator, and view renderer as services
   - [x] Use constructor injection for controllers to decouple from globals

4. [x] Centralize configuration management
   - [x] Load environment variables using vlucas/phpdotenv
   - [x] Provide a Config service with typed getters and sensible defaults
   - [x] Document required env vars and failure modes in README

5. [x] Strengthen domain layer
   - [x] Ensure DTOs (Candidate, Contact, Employee, Task, TimeEntry, Payment, StorageItem) are immutable or defensively copied
   - [x] Add factory methods and fromInput()/toArray() consistency across DTOs
   - [x] Co-locate validation rules with DTOs or a dedicated Validator namespace

6. [x] Validation framework
   - [x] Create reusable validators (required, email, enum, date, length) with error codes
   - [x] Normalize error format and localization keys for templates

7. [x] Persistence abstraction and repositories
   - [x] Define StoreInterface/Repository interfaces with find/get/add/update/delete and pagination support
   - [x] Make JsonStore and DbStore conform; add transaction-like guards for multi-write ops
   - [x] Add optimistic concurrency or last-updated fields where relevant

8. [x] Data integrity and migrations
   - [x] Add migration runner; keep schema changes in migrations/ with idempotent scripts
   - [x] Enforce foreign keys in DB mode; validate referential integrity in JSON mode

9. [ ] Security hardening
   - [ ] CSRF tokens on all forms (generate, store in session, validate)
   - [ ] Enable output escaping in templates by default; audit for raw output
   - [ ] Add common security headers (CSP, X-Content-Type-Options, Referrer-Policy, HSTS when HTTPS)
   - [ ] Validate and clamp input sizes; reject overly large payloads

10. [ ] Authentication and authorization
   - [ ] Add session-based login for write operations
   - [ ] Introduce roles (viewer/admin) and guard sensitive actions and routes

11. [ ] Error handling and logging
   - [ ] Global exception/error handlers with user-friendly pages
   - [ ] PSR-3 logging (monolog) configured to write into var/log with rotation
   - [ ] Replace die/echo error paths with exceptions and structured logs

12. [ ] Internationalization (i18n)
   - [ ] Ensure all user-facing strings are in lang files and localized via __()
   - [ ] Provide pluralization and date/number formatting helpers per locale
   - [ ] Add fallback chains and validate requested locale

13. [ ] Date/time and formatting
   - [ ] Standardize on DateTimeImmutable in DTOs and persistence
   - [ ] Centralize parsing/formatting helpers with locale awareness

14. [ ] Controllers: consistency and type safety
   - [ ] Add strict types and explicit return types across controllers
   - [ ] Replace inline superglobal usage with Request accessors
   - [ ] Extract shared logic (pagination/sorting/search) into reusable helpers

15. [ ] Views and templates
   - [ ] Ensure autoescape is enabled; remove any unsafe raw output
   - [ ] Create partials for header, footer, flash messages, pagination controls
   - [ ] Move non-trivial logic from templates into view models/helpers

16. [ ] UX and accessibility
   - [ ] Ensure labels, aria-attributes, and focus states are present
   - [ ] Verify color contrast (WCAG AA) and keyboard navigation
   - [ ] Provide inline form validation hints synced with server errors

17. [ ] Sorting, filtering, and pagination standardization
   - [ ] Create a unified Pagination object and helpers for building sort/filter links
   - [ ] Normalize allowed sort keys per entity to prevent invalid input

18. [ ] Performance and caching
   - [ ] Profile list/detail pages; remove N+1 patterns
   - [ ] Add HTTP caching headers (ETag/Last-Modified) for list pages where safe
   - [ ] Cache computed lists and i18n dictionaries per request

19. [ ] File-based store resilience (JSON)
   - [ ] Add file locking and atomic writes (temp + rename)
   - [ ] Validate JSON and create rolling backups with restore capability

20. [ ] Testing foundation
   - [ ] Add PHPUnit with a phpunit.xml and bootstrap
   - [ ] Unit tests for validators, DTOs, and stores (JSON/DB)
   - [ ] Feature tests for core flows (create/list/update/delete entities)

21. [ ] Static analysis and code style
   - [ ] Add PHPStan with level tuned to project; create baseline
   - [ ] Add PHPCS or PHP-CS-Fixer configuration and composer scripts
   - [ ] Fail CI on static analysis or style violations

22. [ ] Continuous Integration
   - [ ] GitHub Actions workflow running tests, static analysis, and linting
   - [ ] Cache composer dependencies for speed; upload coverage artifact

23. [ ] Developer experience
   - [ ] Composer scripts or Makefile for serve, test, lint, fix, seed
   - [ ] Seed/dev fixtures command and documentation
   - [ ] Local .env.example with sensible defaults

24. [ ] Deployment readiness
   - [ ] Provide Nginx/Apache sample configs and PHP-FPM guidance
   - [ ] Document required PHP extensions and minimal versions
   - [ ] Add production env recommendations (opcache, error reporting)

25. [ ] Backup and recovery
   - [ ] Automated JSON and SQLite backups with rotation policy
   - [ ] Simple restore CLI with dry-run and verification

26. [ ] Observability
   - [ ] Structured logs with context (request id, user id)
   - [ ] Error tracking integration placeholder (Sentry-compatible interface)
   - [ ] Basic health-check endpoint

27. [ ] Data privacy and compliance
   - [ ] Privacy notice and data retention policy in docs
   - [ ] Mask/redact sensitive fields in logs and exports
   - [ ] Add data export/anonymization script for user data

28. [ ] API readiness (optional)
   - [ ] Define JSON API endpoints and error format
   - [ ] Add authentication (token/session) and rate limiting for API
   - [ ] Document endpoints in docs/api.md

29. [ ] Error pages
   - [ ] Friendly localized 404/500 templates with links back to home
   - [ ] Ensure they render without depending on broken services

30. [ ] Internationalization completeness
   - [ ] Audit and complete translations for all supported locales
   - [ ] Persist language preference (cookie or URL param) with validation

31. [ ] Security: sessions and cookies
   - [ ] Set secure, HttpOnly, SameSite cookie flags; regenerate session IDs on login
   - [ ] Implement session timeout and inactivity logout

32. [ ] Rate limiting and abuse controls
   - [ ] Simple per-IP/session rate limits for write operations
   - [ ] Honeypot or captcha option for public forms if any

33. [ ] SEO and metadata
   - [ ] Add canonical URLs, meta descriptions, and open graph tags where applicable
   - [ ] Ensure consistent titles and breadcrumbs across pages

34. [ ] Data exports/imports
   - [ ] Export entities to CSV/JSON with proper escaping and encoding
   - [ ] Import with validation, preview (dry-run), and error reporting

35. [ ] Documentation improvements
   - [ ] Expand README with architecture overview and setup (JSON vs DB)
   - [ ] Add contribution guide and coding standards
   - [ ] Diagram of module relationships and request flow in docs/

36. [ ] Housekeeping
   - [ ] Remove dead code and unused helpers
   - [ ] Add strict_types declarations and type hints across the codebase
   - [ ] Normalize namespaces and directory structure
