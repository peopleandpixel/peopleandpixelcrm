# Entities

Below is a summary of core entities, their key fields, and validation hints. Actual rules live in src/Domain/* and src/Validation.

Contact (src/Domain/Contact.php)
- name (required)
- email (email format)
- phone
- company
- notes

TimeEntry (src/Domain/TimeEntry.php)
- contact_id (int)
- employee_id (int; 0 = Unassigned)
- date (Y-m-d)
- hours (float; > 0 unless valid start/end is supplied)
- description
- start_time (HH:MM, optional)
- end_time (HH:MM, optional)
- If start_time and end_time are present and valid, hours is computed.

Task (src/Domain/Task.php)
- contact_id (int)
- employee_id (int; 0 = Unassigned)
- title (required)
- due_date (Y-m-d or empty)
- done_date (Y-m-d or empty)
- status (open|done)
- notes

Employee (src/Domain/Employee.php)
- name (required)
- email (email format)
- phone
- role
- salary (>= 0)
- hired_at (Y-m-d or empty)
- notes

Candidate (src/Domain/Candidate.php)
- name (required)
- email (email format)
- phone
- position
- status (applied|interview|hired|rejected)
- notes

Payment (src/Domain/Payment.php)
- date (Y-m-d)
- type (income|expense)
- amount (> 0)
- counterparty
- description
- category
- tags (comma-separated)

StorageItem (src/Domain/StorageItem.php)
- sku (unique SKU recommended; uniqueness validated in UI)
- name (required)
- quantity (int >= 0)
- location
- notes
- low_stock_threshold (int >= 0)

Relationships
- Many records reference contacts or employees by their numeric IDs. In DB mode, foreign keys are enforced; in JSON mode, validations ensure integrity on write.

Persistence
- JSON mode stores data under DATA_DIR as <entity>.json; writes are atomic with locking and integrity checks.
- DB mode stores data in SQL tables; migrations run automatically via src/Migrator.php when DB is used.
