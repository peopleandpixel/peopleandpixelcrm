# People & Pixel – Improvement Tasks Checklist

Note: Check off items as you complete them. The list is ordered to reduce risk and group related concerns.

1. [x] Establish project goals and quality bar
   - [x] Document non-goals and priorities in README (features, performance targets, supported PHP versions, envs)
   - [x] Define coding standards (PSR-12, type usage, strict_types) and linters to enforce them

2. [x] Adopt consistent typing and strict mode
   - [x] Enable declare(strict_types=1) across src/ and tests/
   - [x] Add missing scalar/array types and return types in public APIs (e.g., src/Controller/*.php, src/Util/*.php)
   - [x] Replace mixed/array with dedicated DTOs/value objects where beneficial (Domain layer)

3. [x] Introduce static analysis and code quality tooling
   - [x] Add PHPStan (or Psalm) with a baseline, level 6–8, and CI job
   - [x] Add PHP-CS-Fixer (PSR-12 ruleset) and a composer script
   - [x] Add Rector for gradual refactorings (optional, with baseline)

4. [ ] Dependency management and DI
   - [ ] Review Container (src/Container.php) and ensure single-responsibility (construction vs configuration)
   - [x] Replace ad-hoc new in Router handler resolution with container-based controller instantiation
   - [ ] Centralize configuration in src/Config.php and .env, typed accessors with defaults and validation

5. [ ] HTTP foundation alignment
   - [ ] Expand App\Http\Request/Response to encapsulate headers, cookies, files, JSON, redirects
   - [ ] Replace direct $_GET/$_POST/$_FILES usage in controllers/utilities with Request abstraction
   - [ ] Introduce middleware interfaces and typed signatures; document lifecycle

6. [ ] Routing and error handling
   - [ ] Add route parameters and basic pattern matching to App\Router (e.g., /contacts/{id})
   - [ ] Implement route grouping and middleware per group
   - [ ] Standardize 404/405/500 rendering via templates/errors and dedicated error controller
   - [ ] Ensure base path detection is well-tested (unit tests around App\Router::dispatch)

7. [ ] Domain modeling and persistence boundaries
   - [ ] Review Domain/* DTOs; ensure invariants via constructors and validation
   - [ ] Define repository interfaces (StoreInterface refinements) per aggregate (Contacts, Tasks, Storage)
   - [ ] Separate JsonStore/DbStore behind interfaces, allow swapping via configuration
   - [ ] Add migrations/versioning strategy in src/Migrator.php with idempotent up/down

8. [ ] Validation and sanitization
   - [ ] Centralize validation rules in src/Validation/Validator.php; define schemas per entity
   - [ ] Replace scattered input filtering (e.g., in controllers) with reusable validators
   - [ ] Add HTML escaping contract and enforce in twig (audit |e usage)
   - [ ] Strengthen App\Util\Sanitizer; add tests for XSS and injection cases

9. [ ] Controller refactors and UX consistency
   - [ ] Normalize method names and signatures (index/list/view/new/edit/update/delete)
   - [ ] Remove side-effects from GET endpoints; ensure POST/redirect-GET pattern and Flash messaging consistency
   - [ ] Extract repeated list/sort/filter logic to dedicated service instead of static util
   - [ ] Ensure CSRF tokens on all mutating forms (check templates/partials/header.twig and form partials)

10. [ ] List sorting, filtering, and pagination
    - [ ] Generalize App\Util\ListSort::getSortedList: inject Request, accept dynamic filterable fields, null-safe compare
    - [ ] Replace hard-coded search fields ['name','email','company','phone','notes'] with schema-driven config (App\Domain\Schemas)
    - [ ] Stabilize sorting: use locale-aware collation and numeric-aware comparison when needed
    - [ ] Add unit tests for sorting/filtering/paging edge cases (empty, large pages, invalid params)

11. [ ] File uploads and storage
    - [ ] Audit App\Util\Uploader and Upload for security (MIME/type checking, size limits, path traversal)
    - [ ] Move uploaded files outside webroot or enforce randomized names with allowlist extensions
    - [ ] Stream large files, avoid reading to memory; add integration tests

12. [ ] Authentication and authorization
    - [ ] Review App\Util\Auth and Permission; define roles/permissions and enforce in controllers
    - [ ] Add login throttling and session fixation protections
    - [ ] Ensure sensitive routes are protected and covered by tests

13. [ ] Internationalization (i18n)
    - [ ] Audit I18n implementation (src/I18n.php, lang/*); ensure fallback locale and pluralization
    - [ ] Extract strings in templates to translation files; remove stray literals
    - [ ] Add language switcher UI and persist preference

14. [ ] Templating and UI consistency
    - [ ] Standardize layouts (templates/layout.twig) and components (templates/partials/*)
    - [ ] Add reusable dynamic list component (templates/partials/dynamic_list.twig) driven by schema metadata
    - [ ] Ensure accessibility (labels, aria, color contrast) and responsive behavior of cards (home.twig)

15. [ ] Storage/Inventory flows
    - [ ] Clarify adjustments history model; split write models from read models if needed
    - [ ] In StorageController, validate quantities and prevent negative stock when not allowed
    - [ ] Add transactional semantics to multi-step updates; surface errors via Flash

16. [ ] Error handling and logging
    - [ ] Centralize exception handling in App\Util\ErrorHandler; convert to friendly error pages in prod
    - [ ] Add Monolog integration with channels and rotating handlers
    - [ ] Introduce domain exceptions and map to HTTP statuses

17. [ ] Performance and caching
    - [ ] Profile hot paths (routing, list pages) and add simple caching for schema lookups (Schemas::get)
    - [ ] Add HTTP caching headers for list endpoints (ETag/Last-Modified) where feasible
    - [ ] Avoid N+1 and repeated disk I/O in JsonStore (batch reads/writes)

18. [ ] Testing strategy
    - [ ] Set up PHPUnit with a phpunit.xml.dist and base TestCase
    - [ ] Add unit tests for Router (404/405, base path, middleware order)
    - [ ] Add controller tests for CRUD flows (contacts, storage) using in-memory stores/fixtures in data/
    - [ ] Add validation and sanitizer tests; XSS vectors in templates

19. [ ] Continuous Integration
    - [ ] Add GitHub Actions (or other CI) workflow running: composer validate, install, cs-fix dry-run, phpstan, phpunit
    - [ ] Upload coverage report; set a minimum threshold
    - [ ] Cache composer deps in CI for performance

20. [ ] Developer Experience (DX)
    - [ ] Add makefile or composer scripts: setup, serve, test, lint, stan, fix
    - [ ] Provide seed data and reset scripts for local env (data/*)
    - [ ] Document environment variables (.env.example) and Docker setup (optional)

21. [ ] Data integrity and migrations
    - [ ] Ensure Migrator supports repeated runs and schema versioning; add tests
    - [ ] Add export/import safeguards in ImportController/ExportController (validate schema, backup before overwrite)
    - [ ] Provide recovery tools for corrupted JSON/DB data

22. [ ] Security hardening
    - [ ] Ensure CSRF coverage on all forms and verify App\Util\Csrf usage
    - [ ] Set secure cookie flags (HttpOnly, Secure, SameSite) and session settings
    - [ ] Validate and sanitize all URL parameters and IDs (route params once added)

23. [ ] Documentation
    - [ ] Expand README with architecture overview (layers, data flow, request lifecycle)
    - [ ] Add CONTRIBUTING.md with branching, commit style, reviews
    - [ ] Document error codes, logging, and troubleshooting

24. [ ] Release and versioning
    - [ ] Establish CHANGELOG.md and semantic versioning policy
    - [ ] Add version constant and surface in footer/admin
    - [ ] Automate release notes generation (optional)
