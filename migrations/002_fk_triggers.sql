-- Enforce referential integrity via triggers (SQLite-compatible)
-- - Validate times.contact_id references contacts(id)
-- - Validate tasks.contact_id references contacts(id)
-- - Restrict deleting contacts when related times/tasks exist

-- Times: validate on INSERT
CREATE TRIGGER IF NOT EXISTS trg_times_contact_fk_insert
BEFORE INSERT ON times
FOR EACH ROW
WHEN (SELECT id FROM contacts WHERE id = NEW.contact_id) IS NULL
BEGIN
    SELECT RAISE(ABORT, 'times.contact_id must reference existing contacts.id');
END;

-- Times: validate on UPDATE of contact_id
CREATE TRIGGER IF NOT EXISTS trg_times_contact_fk_update
BEFORE UPDATE OF contact_id ON times
FOR EACH ROW
WHEN (SELECT id FROM contacts WHERE id = NEW.contact_id) IS NULL
BEGIN
    SELECT RAISE(ABORT, 'times.contact_id must reference existing contacts.id');
END;

-- Tasks: validate on INSERT
CREATE TRIGGER IF NOT EXISTS trg_tasks_contact_fk_insert
BEFORE INSERT ON tasks
FOR EACH ROW
WHEN (SELECT id FROM contacts WHERE id = NEW.contact_id) IS NULL
BEGIN
    SELECT RAISE(ABORT, 'tasks.contact_id must reference existing contacts.id');
END;

-- Tasks: validate on UPDATE of contact_id
CREATE TRIGGER IF NOT EXISTS trg_tasks_contact_fk_update
BEFORE UPDATE OF contact_id ON tasks
FOR EACH ROW
WHEN (SELECT id FROM contacts WHERE id = NEW.contact_id) IS NULL
BEGIN
    SELECT RAISE(ABORT, 'tasks.contact_id must reference existing contacts.id');
END;

-- Contacts: restrict delete when referenced by times
CREATE TRIGGER IF NOT EXISTS trg_contacts_restrict_delete_times
BEFORE DELETE ON contacts
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM times WHERE contact_id = OLD.id)
BEGIN
    SELECT RAISE(ABORT, 'Cannot delete contact: referenced by times');
END;

-- Contacts: restrict delete when referenced by tasks
CREATE TRIGGER IF NOT EXISTS trg_contacts_restrict_delete_tasks
BEFORE DELETE ON contacts
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM tasks WHERE contact_id = OLD.id)
BEGIN
    SELECT RAISE(ABORT, 'Cannot delete contact: referenced by tasks');
END;
