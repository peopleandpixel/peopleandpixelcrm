-- Link working times to tasks
ALTER TABLE times ADD COLUMN task_id INTEGER DEFAULT 0;
CREATE INDEX IF NOT EXISTS idx_times_task_id ON times(task_id);

-- Optional: add FK-like checks via triggers in 002_fk_triggers.sql (already used for other FKs)
