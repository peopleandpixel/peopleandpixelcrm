-- Add optional start_time and end_time to times for better accountability
ALTER TABLE times ADD COLUMN start_time TEXT;
ALTER TABLE times ADD COLUMN end_time TEXT;

-- Create indexes to help queries if needed (optional)
CREATE INDEX IF NOT EXISTS idx_times_start_time ON times(start_time);
CREATE INDEX IF NOT EXISTS idx_times_end_time ON times(end_time);
