-- Add done_date column to tasks and index
ALTER TABLE tasks ADD COLUMN done_date TEXT;
CREATE INDEX IF NOT EXISTS idx_tasks_done_date ON tasks(done_date);
