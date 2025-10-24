# People & Pixel — Competitive Analysis and Improvement Roadmap

Date: 2025-10-24

Purpose
This document compares People & Pixel to adjacent tools on the market and distills a prioritized, actionable roadmap. It focuses on the target segment: small teams and freelancers who prefer self‑hosting, simplicity, and data ownership over deep enterprise features.

Target Segment and JTBD
- Self‑hosted micro/small teams (1–20 people) and privacy‑minded freelancers
- Jobs to be done:
  - Keep contacts, tasks, deals, and projects in one simple place
  - Track activities, files, and time without SaaS lock‑in
  - Export/import data easily; run on modest infrastructure

Competitor Landscape (snapshot)
- Open source/self‑hosted:
  - Monica (personal CRM): strong contacts and activities; less business flows.
  - EspoCRM: broad CRM modules (leads/opportunities), heavier, extensible; steeper learning curve.
  - SuiteCRM: enterprise‑leaning breadth; heavy UI and ops overhead for small teams.
  - Dolibarr: ERP/CRM suite; wide scope, not minimalistic.
- Popular SaaS (as reference):
  - HubSpot, Pipedrive, Zoho CRM: polished UI, deep reporting, marketplaces, and automation; cost and lock‑in not ideal for our segment.

Current Strengths (People & Pixel)
- Lightweight, understandable PHP app with JSON or DB storage
- Pragmatic features already implemented: contacts, tasks, projects, deals, global search, ICS calendar, reports, time tracking, files, import/export, API/webhooks, audit log, PWA basics
- Simplicity of deployment; minimal dependencies; good i18n foundations
- Clear UX principles (POST/redirect-GET, validation, error handling)

Gaps vs. Market (high‑level)
- Automation: limited rules/automation compared to SaaS (workflows, triggers).
- Collaboration UX: comments/mentions/notifications are basic; no watch/follow model.
- Mobile experience: PWA exists but offline and push are limited; no install guidance.
- Integrations: few out-of-the-box connectors beyond SMTP and webhooks.
- Reporting depth: basic charts; limited pivoting, cohort and funnel analysis.
- Permissions: roles exist but lack fine‑grained, object‑level controls.
- Data quality: dedupe exists; enrichment and validation (email/phone) could improve.
- Admin/ops: backups/restore flows and health checks are basic.

Differentiation Opportunities
- First‑class self‑host UX: one‑command setup, backups, and updates tailored for micro teams.
- Privacy‑first enrichment: local or user‑provided API keys, with clear limits and auditability.
- Simple automations: readable, file‑backed rule engine (YAML/JSON) with safe sandboxed actions.
- Better PWA: robust offline caching for key views, background sync for drafts/uploads, and install prompts.
- Extensibility: small plugin hooks (events + webhooks) and template overrides without forking.

Prioritized Improvement Roadmap
1) Must‑have market parity (near‑term)
- Permissions: introduce owner/object‑level controls with simple policies (own/role/team) and per-entity toggles.
  - Scope: Add owner field on core entities; policies: owner, same-role, same-team; per-entity enable/disable. UI for admins to configure defaults.
  - Acceptance: Non-owners blocked per policy; admin override works; audit log records denials; tests cover view/edit/delete matrix. See docs/tasks.md §25 "Permissions v2".
  - Dependencies: Auth/Session roles, Team model (if enabled). Out-of-scope: row-level sharing links, ACL editor.
  - First milestones: (M1) Policy engine + config flags; (M2) Enforce in controllers; (M3) Admin UI + docs.
- Bulk operations: multi‑select and batch edits on list pages with CSRF and audit logging.
  - Scope: Checkbox multi-select on contacts/tasks/deals; actions: assign owner, add/remove tags, status change, delete, export. Confirm dialogs + CSRF.
  - Acceptance: Selection persists across pagination; actions logged with before/after counts; failures surfaced; undo for tag/assign where feasible. See docs/tasks.md §25 "Bulk operations".
  - Dependencies: List component, CSRF, AuditLog. Out-of-scope: cross-entity bulk editing modals.
  - First milestones: (M1) Shared BulkAction service; (M2) Wire on contacts list; (M3) Extend to tasks/deals.
- Import/export hardening: schema validation, dry‑run preview, dedupe merge strategies.
  - Scope: JSON/CSV schema validation per entity; dry-run shows row-level issues; dedupe by email/phone/name with merge strategies (skip, overwrite, merge-missing).
  - Acceptance: Dry-run never mutates; import produces summary with created/updated/skipped; backup before destructive import; tests with corrupt/malformed data. See docs/tasks.md §25 "Import/Export hardening".
  - Dependencies: Validator, Schemas, Backup service. Out-of-scope: third-party enrichment during import.
  - First milestones: (M1) Schema JSON for entities; (M2) Import dry-run; (M3) Merge strategies + backup hook.
- Reporting v2: saved charts with breakdowns by tag/status/owner and CSV export across date ranges.
  - Scope: Allow saving report definitions (entity, filters, breakdown, metric); render simple bar/line; CSV export respects filters and timezone.
  - Acceptance: Reports persist in data/reports.json (or DB); shareable link with perms; P95 render <200ms on 2k items; tests for CSV headers and date ranges. See docs/tasks.md §25 "Reporting v2".
  - Dependencies: ReportsStore, List filter pipeline, Timezone handling. Out-of-scope: pivot tables and drill-down.
  - First milestones: (M1) Report DTO + store; (M2) Save/load UI; (M3) CSV export + cache.
- Backups and recovery: one‑click JSON/DB backup, download, and restore with integrity checks.
  - Scope: UI button to create zip with data/, uploads metadata, and schema version; download link; restore flow with pre-check and confirmation.
  - Acceptance: Backup zip includes manifest and checksums; restore validates version and checksum; errors are recoverable; CLI parity for headless. See docs/tasks.md §25 "Backups & recovery".
  - Dependencies: File storage service, Migrator. Out-of-scope: incremental backups and remote storage.
  - First milestones: (M1) Backup manifest + zip; (M2) Restore dry-run; (M3) UI wiring + docs.

2) Collaboration & workflow (mid‑term)
- Comments and mentions: comment threads on contacts/tasks with @mentions and email notifications (opt‑in).
- Watch/follow: users can follow entities to get digest notifications.
- Automations MVP: event → conditions → actions engine (e.g., when deal stage changes to Won, add follow‑up task; when task overdue, notify assignee).

3) PWA & mobile excellence (mid‑term)
- Offline‑first lists: cache recent contacts/tasks/projects; allow offline task edits queued for sync.
- Background sync: service worker sync for queued operations; conflict resolution rules.
- Install UX: add “Add to Home Screen” prompts and guidance across major browsers; icon set polish.

4) Integrations (ongoing)
- CalDAV/ICS inbound: allow subscribing to external calendars (read‑only) and mapping to tasks/events.
- Email ingest: optional IMAP connector to log inbound emails to contacts by matching addresses.
- Webhooks catalog: documented examples and test webhook receiver; retry with exponential backoff.

5) Data quality & enrichment (optional, privacy‑first)
- Validation services: optional DNS/MX check for emails, E.164 phone normalization; run in background.
- Local enrichment: company logo via Clearbit‑like service with user‑provided key; configurable and auditable.

6) Admin & DX
- Backup/restore CLI and UI, with scheduled backups (zip) and retention.
- Health dashboard: storage usage, job status, error rates; downloadable logs.
- One‑file docker-compose with volumes and sample .env; seed/reset scripts.

Success Metrics
- Time‑to‑value: new user can import contacts and complete a task flow in under 10 minutes.
- Page performance: P95 list render < 200ms server time at 2k items in JSON mode.
- Reliability: zero data loss incidents across routine operations; automated daily backups.
- Adoption: increase in weekly active users and integrations enabled.

Risks and Mitigations
- Scope creep: keep automations readable and limited; avoid building a full BPM engine.
- Privacy/legal: enrichment features off by default; all calls require explicit user‑provided API keys.
- Complexity: prefer simple, composable features over deep customizations; document trade‑offs.

Appendix: Quick Feature Matrix (very high‑level)
- Contacts/Tasks/Deals/Projects: P&P ✓ / Monica ✓ / Espo ✓ / Suite ✓ / SaaS ✓
- Import/Export: P&P ✓ / Monica ✓ / Espo ✓ / Suite ✓ / SaaS ✓
- Reporting: P&P basic / Monica basic / Espo medium / Suite medium / SaaS advanced
- Automations: P&P limited / Monica limited / Espo medium / Suite medium / SaaS advanced
- Permissions: P&P basic / Monica basic / Espo medium / Suite advanced / SaaS advanced
- Integrations: P&P few / Monica few / Espo medium / Suite many / SaaS many
- Mobile/PWA: P&P basic / Monica basic / Espo basic / Suite basic / SaaS polished
