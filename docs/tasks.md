# People & Pixel – Improvement Tasks Checklist

Note: Check off items as you complete them. The list is ordered to reduce risk and group related concerns.

1. [x] Establish project goals and quality bar
   - [x] Document non-goals and priorities in README (features, performance targets, supported PHP versions, envs)
   - [x] Define coding standards (PSR-12, type usage, strict_types) and linters to enforce them

2. [x] Adopt consistent typing and strict mode
   - [x] Enable declare(strict_types=1) across src/ and tests/
   - [x] Add missing scalar/array types and return types in public APIs (e.g., src/Controller/*.php, src/Util/*.php)
   - [x] Replace mixed/array with dedicated DTOs/value objects where beneficial (Domain layer)
now do 
3. [x] Introduce static analysis and code quality tooling
   - [x] Add PHPStan (or Psalm) with a baseline, level 6–8, and CI job
   - [x] Add PHP-CS-Fixer (PSR-12 ruleset) and a composer script
   - [x] Add Rector for gradual refactorings (optional, with baseline)

4. [x] Dependency management and DI
   - [x] Review Container (src/Container.php) and ensure single-responsibility (construction vs configuration)
   - [x] Replace ad-hoc new in Router handler resolution with container-based controller instantiation
   - [x] Centralize configuration in src/Config.php and .env, typed accessors with defaults and validation

5. [x] HTTP foundation alignment
   - [x] Expand App\Http\Request/Response to encapsulate headers, cookies, files, JSON, redirects
   - [x] Replace direct $_GET/$_POST/$_FILES usage in controllers/utilities with Request abstraction
   - [x] Introduce middleware interfaces and typed signatures; document lifecycle

6. [x] Routing and error handling
  - [x] Add route parameters and basic pattern matching to App\Router (e.g., /contacts/{id})
  - [x] Implement route grouping and middleware per group
  - [x] Standardize 404/405/500 rendering via templates/errors and dedicated error controller
  - [x] Ensure base path detection is well-tested (unit tests around App\Router::dispatch)

7. [x] Domain modeling and persistence boundaries
   - [x] Review Domain/* DTOs; ensure invariants via constructors and validation
   - [x] Define repository interfaces (StoreInterface refinements) per aggregate (Contacts, Tasks, Storage)
   - [x] Separate JsonStore/DbStore behind interfaces, allow swapping via configuration
   - [x] Add migrations/versioning strategy in src/Migrator.php with idempotent up/down

8. [x] Validation and sanitization
   - [x] Centralize validation rules in src/Validation/Validator.php; define schemas per entity
   - [x] Replace scattered input filtering (e.g., in controllers) with reusable validators
   - [x] Add HTML escaping contract and enforce in twig (audit |e usage)
   - [x] Strengthen App\Util\Sanitizer; add tests for XSS and injection cases

9. [x] Controller refactors and UX consistency
   - [x] Normalize method names and signatures (index/list/view/new/edit/update/delete)
   - [x] Remove side effects from GET endpoints; ensure POST/redirect-GET pattern and Flash messaging consistency
   - [x] Extract repeated list/sort/filter logic to dedicated service instead of static util
   - [x] Ensure CSRF tokens on all mutating forms (check templates/partials/header.twig and form partials)

10. [x] List sorting, filtering, and pagination
     - [x] Generalize App\Util\ListSort::getSortedList: inject Request, accept dynamic filterable fields, null-safe compare
     - [x] Replace hard-coded search fields ['name','email','company','phone','notes'] with schema-driven config (App\Domain\Schemas)
     - [x] Stabilize sorting: use locale-aware collation and numeric-aware comparison when needed
     - [x] Add unit tests for sorting/filtering/paging edge cases (empty, large pages, invalid params)

11. [x] File uploads and storage
    - [x] Audit App\Util\Uploader and Upload for security (MIME/type checking, size limits, path traversal)
    - [x] Move uploaded files outside webroot or enforce randomized names with allowlist extensions
    - [x] Stream large files, avoid reading to memory; add integration tests

12. [x] Authentication and authorization
    - [x] Review App\Util\Auth and Permission; define roles/permissions and enforce in controllers
    - [x] Add login throttling and session fixation protections
    - [x] Ensure sensitive routes are protected and covered by tests

13. [x] Internationalization (i18n)
    - [x] Audit I18n implementation (src/I18n.php, lang/*); ensure fallback locale and pluralization
    - [x] Extract strings in templates to translation files; remove stray literals
    - [x] Add language switcher UI and persist preference

14. [x] Templating and UI consistency
    - [x] Standardize layouts (templates/layout.twig) and components (templates/partials/*)
    - [x] Add reusable dynamic list component (templates/partials/dynamic_list.twig) driven by schema metadata
    - [x] Ensure accessibility (labels, aria, color contrast) and responsive behavior of cards (home.twig)

15. [x] Storage/Inventory flows
    - [x] Clarify adjustments history model; split write models from read models if needed
    - [x] In StorageController, validate quantities and prevent negative stock when not allowed
    - [x] Add transactional semantics to multi-step updates; surface errors via Flash

16. [x] Error handling and logging
    - [x] Centralize exception handling in App\Util\ErrorHandler; convert to friendly error pages in prod
    - [x] Add Monolog integration with channels and rotating handlers
    - [x] Introduce domain exceptions and map to HTTP statuses

17. [x] Performance and caching
    - [x] Profile hot paths (routing, list pages) and add simple caching for schema lookups (Schemas::get)
    - [x] Add HTTP caching headers for list endpoints (ETag/Last-Modified) where feasible
    - [x] Avoid N+1 and repeated disk I/O in JsonStore (batch reads/writes)

18. [ ] Testing strategy
    - [ ] Set up PHPUnit with a phpunit.xml.dist and base TestCase
    - [ ] Add unit tests for Router (404/405, base path, middleware order)
    - [ ] Add controller tests for CRUD flows (contacts, storage) using in-memory stores/fixtures in data/
    - [ ] Add validation and sanitizer tests; XSS vectors in templates

19. [ ] Continuous Integration
    - [ ] Add GitHub Actions (or other CI) workflow running: composer validate, install, cs-fix dry-run, phpstan, phpunit
    - [ ] Upload coverage report; set a minimum threshold
    - [ ] Cache composer deps in CI for performance

20. [x] Developer Experience (DX)
    - [x] Add makefile or composer scripts: setup, serve, test, lint, stan, fix
    - [x] Provide seed data and reset scripts for local env (data/*)
    - [x] Document environment variables (.env.example) and Docker setup (optional)

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


25. [ ] Market-driven Improvements & Differentiators
    - [x] Permissions v2: Add owner/object-level access controls with per-entity toggles; document policies and add tests
    - [x] Bulk operations: Multi-select and batch actions on list pages with CSRF, audit logging, and undo where feasible
    - [x] Import/Export hardening: Schema validation, dry-run previews, configurable dedupe/merge strategies
    - [x] Reporting v2: Saved charts with breakdowns (tag/status/owner), date ranges, and CSV across entities
    - [x] Backups & recovery: One-click JSON/DB backup and restore (UI + CLI) with integrity checks and retention
    - [x] Comments & mentions: Threaded comments on contacts/tasks/projects with @mentions and optional email notices
    - [x] Watch/follow entities: Allow users to follow records and receive digest notifications
    - [x] Automations MVP: Simple rule engine (event → conditions → actions) with safe, auditable execution
    - [x] PWA offline-first: Cache recent lists and enable offline task edits with queued background sync
    - [x] Install UX polish: A2HS prompts, full icon set, and install docs for major browsers
    - [x] Integrations: CalDAV/ICS inbound (read-only), optional IMAP email ingest, webhook retry/backoff and catalog
    - [x] Data quality: Optional email DNS/MX checks, E.164 phone normalization, background validators
    - [x] Privacy-first enrichment: Optional third-party enrichment behind user-provided API keys, fully auditable
    - [x] Admin & DX: Health dashboard (storage, jobs, errors), downloadable logs, docker-compose sample, seed/reset scripts
    - [x] Success metrics instrumentation: Log key metrics (TTFV, errors, backups) and surface in admin dashboard
    - [ ] Documentation: Add docs/competitive-analysis.md reference in README (done) and expand feature comparison page


26. [ ] CMS/DMS Leadership Roadmap
    - [x] Competitive benchmarking and strategy
      - [x] Identify top CMS/DMS competitors by segment (SMB, Mid‑market, Enterprise) and map differentiators
      - [x] Define target personas and primary jobs‑to‑be‑done (Content Managers, Knowledge Workers, Compliance Officers, IT Admins)
      - [x] Publish docs/competitive-analysis.md and keep updated quarterly
    - [ ] CMS core (headless + editorial)
      - [ ] Content modeling UI: create/edit content types with fields, validations, relations, reusable blocks
      - [ ] Headless APIs: REST + GraphQL with schema introspection, webhooks, SDKs (PHP/JS) and API keys/roles
      - [ ] Content authoring: structured editor (blocks), media embeds, link picker, internal references, shortcuts
      - [ ] Real‑time preview and environments: draft/preview/live with branchable environments and scheduled publish
      - [ ] Content lifecycle: versioning, diff, rollback; workflows with review/approval and assignments
      - [ ] Localization/i18n at entry + field level with fallbacks and translation workflow
      - [ ] Media library: image/video/doc management, transformations (resize/crop), metadata, focal point, renditions
      - [ ] Public site delivery: caching, CDN headers, image optimization, sitemap, SEO meta, OpenGraph
    - [ ] DMS core (documents + records)
      - [ ] Ingest: bulk uploads, email ingest (IMAP rules), scanner support; dedupe and checksum
      - [ ] OCR & text extraction pipeline for PDFs/images (tesseract or service), language detection
      - [ ] Metadata templates, tags, taxonomies, and custom file plans
      - [ ] Versioning with check‑in/out, lock indicators, and merge notes
      - [ ] Annotations (highlights, comments) and redaction tools for PDFs/images
      - [ ] E‑signature integration and signature status tracking
      - [ ] Records management: retention schedules, legal hold, disposition review
      - [ ] Immutable audit trail: who/what/when, exportable and tamper‑evident
    - [ ] Search and discovery
      - [ ] Permissions‑aware full‑text search (OpenSearch/Elasticsearch) over entries, files, comments
      - [ ] Facets/filters, saved searches, alerts, and relevance tuning (boost by recency/popularity)
      - [ ] Synonyms, typo tolerance, and stemming per language; OCR text indexed per page
      - [ ] In‑document search with hit highlighting and page thumbnails
    - [ ] Security, compliance, and governance
      - [ ] SSO (SAML/OIDC), MFA, SCIM provisioning; fine‑grained RBAC + ABAC (attributes on users/resources)
      - [ ] Document/field‑level permissions and secure shared links (expiry, watermark, view‑only)
      - [ ] Encryption at rest (KMS) and in transit; key rotation and per‑tenant keys
      - [ ] Data residency and regional storage selection; audit logging with retention policies
      - [ ] DLP: antivirus scanning, sensitive data detectors (PII, IBAN), quarantine & review workflow
      - [ ] GDPR/DSAR tooling: export, delete, and access logs; consent for enrichment
    - [ ] Collaboration and workflow
      - [ ] Comments, @mentions, and notifications (email, Slack/Teams) with digest options
      - [ ] Real‑time co‑editing for entries and simultaneous annotation sessions for documents
      - [ ] Workflow builder (visual): event → conditions → actions with templates (approval, review, publish, move)
      - [ ] Subscriptions: watch lists/folders/entries and batch digests
    - [ ] Scale and operations
      - [ ] Object storage support (S3/compatible) with signed URLs and lifecycle policies
      - [ ] Background jobs/queue with retry/backoff for OCR, transformations, and webhooks
      - [ ] Caching layers and CDN integration; image/media proxy with transformation cache
      - [ ] Multi‑tenant mode: per‑tenant configs, quotas, isolation, and usage metering
      - [ ] Backup/restore at tenant and global level; DR playbooks (RPO/RTO targets) and chaos drills
      - [ ] Observability: metrics, tracing, and dashboards for OCR, queues, webhooks, API usage
    - [ ] AI‑assisted features
      - [ ] Auto‑classification and tagging from content/OCR; entity extraction (names, orgs, dates)
      - [ ] AI summaries and key points for long docs; suggested titles/descriptions
      - [ ] Semantic search (vector index) and "find similar" recommendations; hybrid BM25+vector
      - [ ] Generative document templates with placeholders and policy‑aware outputs
      - [ ] Smart automation triggers from detected intents (e.g., contracts expiring → create tasks)
    - [ ] Integrations and ecosystem
      - [ ] Office integrations (Word/Excel/PowerPoint) and Google Docs/Drive; in‑app open/edit
      - [ ] Microsoft 365/SharePoint/OneDrive, Box, Dropbox connectors with delta sync
      - [ ] Slack/Teams bots for search, previews, and approvals
      - [ ] Email archiving and journaling; outbound mail with DKIM/DMARC configuration
      - [ ] CRM/ERP connectors (e.g., HubSpot/Salesforce) and webhooks catalog; iPaaS templates (Zapier/Make)
      - [ ] Public SDKs and extension framework with signed apps and marketplace basics
    - [ ] Productization and UX
      - [ ] Onboarding wizard, sample content space, and guided tours
      - [ ] Theming/branding per tenant and style tokens for frontends
      - [ ] Pricing/billing: plans, seats, overages, trials; licensing for on‑prem
      - [ ] Migration tools: importers from popular CMS/DMS and CSV/ZIP with mapping wizards
      - [ ] Accessibility AA conformance audit and keyboard/reader workflows
