# Permissions v2 — Owner/Object-level Access Controls

This document describes the Permissions v2 model for People & Pixel. It adds owner/object-level authorization on top of the existing entity-level permissions.

Overview
- Each user has a permissions matrix per entity with two scopes: own and others. Actions: view, create, edit, delete.
- Admin users bypass all checks.
- Object-level checks consider record ownership via owner_user_id.
- Per-entity toggles control whether object-level checks are enforced.

Per-entity toggles
- Controlled via env variable PERMISSIONS_OBJECT_LEVEL.
- Value: comma-separated list of entities that enforce owner-aware checks.
- When empty or not set, defaults apply: contacts,times,tasks,employees,candidates,payments,storage,projects,deals.

Ownership detection
- A record is considered owned by a user when record[owner_user_id] equals the user id.
- When owner_user_id is absent or 0, the record is treated as "others" scope.
- JsonStore automatically assigns owner_user_id for new records when missing.

Developer API
- Permission::can(entity, action): entity-level decision (own OR others allow).
- Permission::canOnRecord(entity, action, record): owner-aware decision for a specific record.
- Permission::isObjectLevelEnabled(entity): whether the entity should enforce object-level checks.
- Permission::enforce(method, path): middleware-friendly check based on route path for non-record endpoints.
- Permission::enforceRecord(entity, action, record): renders 403 and returns false when denied.

Controller integration
- For actions operating on a specific record (view/edit/delete), load the item first and call:
  if (!Permission::enforceRecord('contacts', 'edit', $item)) { return; }
- This project integrates enforceRecord in Contacts, Deals, and Projects controllers.

Notes
- Admin override always applies.
- The Users admin area remains protected by separate admin checks.
- This feature is backward compatible with the legacy flat permission matrix; when the own/others structure is absent, legacy keys are used.
