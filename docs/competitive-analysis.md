# Competitive Analysis: People & Pixel vs. CRM/Project Management Tools

Scope: This document benchmarks People & Pixel (P&P) against common capabilities in lightweight CRMs and SMB project management tools (e.g., HubSpot Free CRM, Pipedrive, Zoho CRM (Standard), Trello/Asana, Monday, Basecamp, Notion Projects). It identifies gaps and proposes practical improvements aligned with P&P’s philosophy: lightweight, self‑hostable, simple.

Summary
- P&P is a compact, self-hosted CRM with contacts, tasks, time tracking, basic payments, employees/candidates, and simple inventory. JSON storage mode makes it easy to deploy; a DB mode exists for scale.
- Strengths: simplicity, low‑ops, JSON mode, i18n, validation, templating, basic access roles, Monolog, migrations, and a unified list/sort/filter component.
- Gaps vs. sector norms: pipelines/deals, activity timelines, reminders/recurrence, email/calendar integration, Kanban/Gantt views, saved filters and reports, API/webhooks/integrations, mobile UX, automation, bulk edits, granular permissions, and audit logs.

Feature comparison (high‑level)
- Contacts/Companies: P&P ✓ basic. Sector norm: activity timeline, custom fields, tags, ownership, dedupe, merge.
- Deals/Pipeline: P&P ✗. Sector norm: pipeline stages, value/forecasting, Kanban board, probability, win/loss.
- Tasks/Projects: P&P ✓ basic list; ✗ Kanban/Gantt, ✗ dependencies, ✓ i18n. Sector norm: boards, timelines, recurring, reminders, assignees, watchers, batch ops.
- Time tracking: P&P ✓ basic (times.json). Sector norm: per‑task timers, billable rates, approvals, reporting.
- Calendar: P&P ✓ basic templates; ✗ ICS/Google sync, ✗ reminders.
- Email/Comms: P&P ✗ native. Sector norm: send/track emails, IMAP/SMTP, templates, logging to timeline.
- Reporting: P&P ✓ minimal (lists), ✗ charts/dashboards, ✗ saved reports.
- Files: P&P ✓ uploads with safety checks. Sector norm: previews, versioning, linking to entities.
- Search/Filters: P&P ✓ schema-driven list filtering; ✗ global search, ✗ saved views, ✗ full‑text.
- Permissions: P&P ✓ basic roles; ✗ granular object‑level perms, ✗ audit log.
- Integrations/API: P&P ✗ public REST, ✗ webhooks, ✗ Zapier/Make. Sector norm: REST/GraphQL, webhooks, integrations.
- Mobile: P&P responsive ✓; ✗ PWA/offline, push notifications.

Recommendations (prioritized, low risk to higher impact/effort)

Quick wins (1–2 days each)
1) Saved views for lists
- Let users save current filters/sort/page/visible columns as a named view per entity.
- Store in JSON under data/views.json by user or global; or in DB table views.
- UI: add a “Save view” and a selector in dynamic_list.twig.

2) Tags and custom fields (contacts/tasks)
- Add tags array and a flexible custom_fields map per entity in schema.
- UI chips with filter by tag; custom fields rendered by schema metadata.

3) Reminders and recurring tasks
- Add reminder_at and recurrence (none|daily|weekly|monthly|custom RRULE-lite) to tasks.
- Cron-compatible CLI (bin/schedule) to emit due reminders to email or on‑screen alerts.

4) Kanban view for tasks
- Alternate route /tasks/board grouping by status with drag‑and‑drop.
- Reuse existing list service; status transitions update and POST/redirect-GET.

5) Basic dashboards
- A lightweight /dashboard showing counts (open tasks by status, upcoming reminders, recent contacts, low stock items).
- Render via Twig; cache with simple per-request memoization.

Medium impact (up to a week)
6) Deals/pipeline (optional module)
- New entity Deal { title, contact_id, stage, value, currency, probability, expected_close, owner }.
- Views: list, Kanban by stage, forecasting rollups.

7) Activity timeline on contacts
- Normalize activities (note, email, call, task change, file upload) with created_by, created_at.
- Append events from controllers; render a timeline tab.

8) Calendar and ICS exports
- Server-side ICS feeds per user or per entity; subscribe from Google/Outlook.
- Add reminders integration; iCal library is optional—ICS is simple text.

9) Global search
- Index JSON files in memory on boot (or incremental) with trigram/lowercased tokens.
- Provide a single search box that surfaces contacts, tasks, deals.

10) Reporting
- Saved reports with filters + simple charts (counts over time, totals by tag/owner).
- Export CSV; cache results.

Foundational/platform
11) Public REST API and webhooks
- Read-only first (GET /contacts, /tasks), then mutating with CSRF/session or tokens.
- Webhooks on entity changes to enable integrations.

12) Audit log and permission granularity
- Append-only audit trail per entity change (who, when, what). Surface in UI.
- Add ownership model (owner_id) and per-role rules (view own/all, edit own, etc.).

13) Email integration (incremental)
- Outbound: SMTP settings + send templates; log to activity timeline.
- Inbound: IMAP fetcher (optional CLI) to attach emails to contacts by matching address.

14) Performance and search
- Add full-text search for DB mode (SQLite FTS5 or MySQL MATCH AGAINST).
- Cache schema lookups and list queries; batch JSON I/O.

15) Mobile/PWA and notifications
- Add a PWA manifest and service worker for basic offline caching of lists.
- Push notifications (web push) for reminders in supported browsers.

Alignment with current codebase
- The dynamic list component and schema-driven design make Saved Views, Tags, and Custom Fields straightforward.
- The migration system and repository interfaces ease the addition of Deals, Activities, and AuditLog tables.
- Monolog is present; extend with channels for audit and integrations.
- i18n exists; extract new strings and pluralization accordingly.

Risk and sequencing notes
- Start with non-destructive schema extensions (nullable fields, new optional modules).
- Preserve JSON mode by adding defaults and migration scripts for data shape upgrades.
- Provide import/export safeguards (backup, validate) when adding deals/activities.

Proposed 90-day roadmap
- Month 1: Quick wins (1–5) + error-handling/logging completion + global search (9).
- Month 2: Deals (6) + Activity timeline (7) + ICS exports (8) + basic reporting (10).
- Month 3: API/webhooks (11) + audit/permissions (12) + performance/search (14) + PWA (15) as optional.

Success metrics
- Adoption: # of active users/week; setup-to-first-contact time < 10 minutes.
- Productivity: time to find a contact < 3s; create/update task flow < 10s median.
- Data quality: <1% invalid writes; dedupe rate; audit coverage 100% of mutating actions.

Appendix: Implementation notes
- Saved Views: persist as { id, entity, name, user_id|null, config: { filters, sort, columns, pageSize } }.
- Tags: add taggable schema, index tags for quick filtering; ensure XSS-safe rendering.
- Recurrence: generate next occurrences on completion; avoid infinite series.
- Kanban: implement drag via HTML5 or a light library; POST updates per card move.
- ICS: supply Content-Type: text/calendar; entity UID as stable identifier.
- API: start with token in .env; later per-user tokens with scopes; rate limit via middleware.
