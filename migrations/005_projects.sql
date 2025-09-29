-- 005_projects.sql
-- Create projects table and add project_id to tasks

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    contact_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    description TEXT DEFAULT '',
    start_date TEXT DEFAULT '',
    end_date TEXT DEFAULT '',
    status TEXT DEFAULT 'planned',
    created_at TEXT DEFAULT (datetime('now'))
);

-- Add project_id column to tasks if not exists (SQLite compatible)
DO $$ BEGIN
    -- PostgreSQL/others: guard
    BEGIN
        ALTER TABLE tasks ADD COLUMN project_id INTEGER DEFAULT 0;
    EXCEPTION WHEN duplicate_column THEN
        -- ignore
    END;
END $$;

-- SQLite fallback: add column only if missing
PRAGMA foreign_keys = OFF;
CREATE TABLE IF NOT EXISTS _tasks_tmp_check (project_id INTEGER);
DROP TABLE _tasks_tmp_check;
-- Try adding column (will fail silently in SQLite with duplicate)
ALTER TABLE tasks ADD COLUMN project_id INTEGER DEFAULT 0;
PRAGMA foreign_keys = ON;

-- Basic triggers to enforce FK-like constraints in SQLite (if contacts/projects exist)
CREATE TRIGGER IF NOT EXISTS trg_projects_contact_fk_insert
BEFORE INSERT ON projects
FOR EACH ROW
BEGIN
    SELECT CASE WHEN (NEW.contact_id IS NULL OR NEW.contact_id <= 0 OR (SELECT id FROM contacts WHERE id = NEW.contact_id) IS NULL)
        THEN RAISE(ABORT, 'projects.contact_id must reference existing contacts.id') END;
END;

CREATE TRIGGER IF NOT EXISTS trg_projects_contact_fk_update
BEFORE UPDATE OF contact_id ON projects
FOR EACH ROW
BEGIN
    SELECT CASE WHEN (NEW.contact_id IS NULL OR NEW.contact_id <= 0 OR (SELECT id FROM contacts WHERE id = NEW.contact_id) IS NULL)
        THEN RAISE(ABORT, 'projects.contact_id must reference existing contacts.id') END;
END;

-- Tasks.project_id should reference projects.id when > 0
CREATE TRIGGER IF NOT EXISTS trg_tasks_project_fk_insert
BEFORE INSERT ON tasks
FOR EACH ROW
BEGIN
    SELECT CASE WHEN (NEW.project_id IS NOT NULL AND NEW.project_id > 0 AND (SELECT id FROM projects WHERE id = NEW.project_id) IS NULL)
        THEN RAISE(ABORT, 'tasks.project_id must reference existing projects.id or be 0') END;
END;

CREATE TRIGGER IF NOT EXISTS trg_tasks_project_fk_update
BEFORE UPDATE OF project_id ON tasks
FOR EACH ROW
BEGIN
    SELECT CASE WHEN (NEW.project_id IS NOT NULL AND NEW.project_id > 0 AND (SELECT id FROM projects WHERE id = NEW.project_id) IS NULL)
        THEN RAISE(ABORT, 'tasks.project_id must reference existing projects.id or be 0') END;
END;
