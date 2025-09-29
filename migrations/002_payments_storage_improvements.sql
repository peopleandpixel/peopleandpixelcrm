-- Payments: add category and tags
ALTER TABLE payments ADD COLUMN category TEXT;
ALTER TABLE payments ADD COLUMN tags TEXT;
CREATE INDEX IF NOT EXISTS idx_payments_category ON payments(category);

-- Storage: add low_stock_threshold
ALTER TABLE storage ADD COLUMN low_stock_threshold INTEGER DEFAULT 0;
CREATE INDEX IF NOT EXISTS idx_storage_quantity ON storage(quantity);

-- Storage adjustments history
CREATE TABLE IF NOT EXISTS storage_adjustments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id INTEGER NOT NULL,
    delta INTEGER NOT NULL,
    note TEXT,
    created_at TEXT NOT NULL,
    FOREIGN KEY(item_id) REFERENCES storage(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_storage_adj_item ON storage_adjustments(item_id);
CREATE INDEX IF NOT EXISTS idx_storage_adj_created ON storage_adjustments(created_at);
