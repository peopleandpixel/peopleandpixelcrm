# Competitive Analysis: People & Pixel vs. CRM/Project Management Tools

Scope: This document benchmarks People & Pixel (P&P) against common capabilities in lightweight CRMs and SMB project management tools (e.g., HubSpot Free CRM, Pipedrive, Zoho CRM (Standard), Trello/Asana, Monday, Basecamp, Notion Projects). It identifies gaps and proposes practical improvements aligned with P&P’s philosophy: lightweight, self‑hostable, simple.

Summary
- P&P is a compact, self-hosted CRM with contacts, tasks, time tracking, basic payments, employees/candidates, and simple inventory. JSON storage mode makes it easy to deploy; a DB mode exists for scale.
- Strengths: simplicity, low‑ops, JSON mode, i18n, validation, templating, basic access roles, Monolog, migrations, and a unified list/sort/filter component.
- Gaps vs. sector norms (updated): email integration, Google Calendar sync, full‑text search (DB mode), richer reporting/visuals, Zapier/Make marketplace, mobile PWA/offline and push, automation, bulk edits, advanced permissions and audit depth.

Feature comparison (high‑level)
- Contacts/Companies: P&P ✓ basic with tags and custom fields. Sector norm: ownership, dedupe, merge, richer timelines.
- Deals/Pipeline: P&P ✓ basic (stages + board). Sector norm: probability/forecasting, win/loss analyses.
- Tasks/Projects: P&P ✓ list + Kanban; ✓ recurrence and reminders; ✗ Gantt/dependencies. Sector norm: timelines, assignees, watchers, batch ops.
- Time tracking: P&P ✓ basic (times.json). Sector norm: per‑task timers, billable rates, approvals, reporting.
- Calendar: P&P ✓ templates + ✓ ICS feed; ✗ Google sync.
- Email/Comms: P&P ✗ native. Sector norm: send/track emails, IMAP/SMTP, templates, logging to timeline.
- Reporting: P&P ✓ saved reports + simple charts and CSV export; depth could improve.
- Files: P&P ✓ uploads with safety checks. Sector norm: previews, versioning, linking to entities.
- Search/Filters: P&P ✓ schema-driven list filtering + ✓ saved views + ✓ global search; ✗ full‑text.
- Permissions: P&P ✓ roles with own/others granularity; ✓ basic audit log; ✗ full object‑level ACLs.
- Integrations/API: P&P ✓ public REST + ✓ webhooks; ✗ Zapier/Make.
- Mobile: P&P responsive ✓; ✗ PWA/offline, push notifications.

Recommendations (prioritized, only items not yet implemented)

Medium impact (up to a week)
1) Email integration (incremental)
- Outbound: SMTP settings + send templates; log to activity timeline.
- Inbound: IMAP fetcher (optional CLI) to attach emails to contacts by matching address.

2) Performance and search
- Add full-text search for DB mode (SQLite FTS5 or MySQL MATCH AGAINST).
- Cache schema lookups and list queries; batch JSON I/O.

3) Mobile/PWA and notifications
- Add a PWA manifest and service worker for basic offline caching of lists.
- Push notifications (web push) for reminders in supported browsers.

Foundational/platform (longer-term)
4) Integrations marketplace
- Zapier/Make connectors; prebuilt recipes for common flows (e.g., form → contact + task).

5) Advanced permissions and audit depth
- Object-level ACLs and field restrictions; exportable audit trail and retention controls.

Additional improvements to consider
- Contact data quality: dedupe/merge workflows; email/phone validation.
- Bulk operations: multi-select actions across lists (assign owner, status changes, delete, tag add/remove).
- Accessibility and UX: keyboard shortcuts, improved focus management, color‑contrast, ARIA in forms/lists.
- Import/export: safer CSV import with preview/validation; full backup/restore helpers in UI.
- Security: rate limiting on auth, optional 2FA (TOTP), per‑user API tokens with scopes when API lands.
- Observability: structured app logs and event metrics; lightweight health/status page.

Alignment with current codebase
- The dynamic list component and schema-driven design make Global Search and Saved Reports tractable.
- The migration system and repository interfaces ease the addition of Activities/AuditLog tables and API routes.
- Monolog is present; extend with channels for audit and integrations.
- i18n exists; extract new strings and pluralization accordingly.

Risk and sequencing notes
- Start with non-destructive schema extensions (nullable fields, new optional modules).
- Preserve JSON mode by adding defaults and migration scripts for data shape upgrades.
- Provide import/export safeguards (backup, validate) for any new entities or bulk operations.

Success metrics
- Adoption: # of active users/week; setup-to-first-contact time < 10 minutes.
- Productivity: time to find a contact < 3s; create/update task flow < 10s median.
- Data quality: <1% invalid writes; dedupe rate; audit coverage 100% of mutating actions.

Appendix: Implementation notes
- ICS: supply Content-Type: text/calendar; entity UID as stable identifier.
- API: start with token in .env; later per-user tokens with scopes; rate limit via middleware.
