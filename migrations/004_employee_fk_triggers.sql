-- Enforce referential integrity for employee relations (SQLite-compatible)
-- - Validate times.employee_id references employees(id) when non-zero
-- - Validate tasks.employee_id references employees(id) when non-zero
-- - Restrict deleting employees when related times/tasks exist

-- Times: validate on INSERT for employee_id (allow 0 meaning Unassigned)
CREATE TRIGGER IF NOT EXISTS trg_times_employee_fk_insert
BEFORE INSERT ON times
FOR EACH ROW
WHEN NEW.employee_id IS NOT NULL AND NEW.employee_id != 0 AND (SELECT id FROM employees WHERE id = NEW.employee_id) IS NULL
BEGIN
    SELECT RAISE(ABORT, 'times.employee_id must reference existing employees.id or be 0 for Unassigned');
END;

-- Times: validate on UPDATE of employee_id
CREATE TRIGGER IF NOT EXISTS trg_times_employee_fk_update
BEFORE UPDATE OF employee_id ON times
FOR EACH ROW
WHEN NEW.employee_id IS NOT NULL AND NEW.employee_id != 0 AND (SELECT id FROM employees WHERE id = NEW.employee_id) IS NULL
BEGIN
    SELECT RAISE(ABORT, 'times.employee_id must reference existing employees.id or be 0 for Unassigned');
END;

-- Tasks: validate on INSERT for employee_id (allow 0 meaning Unassigned)
CREATE TRIGGER IF NOT EXISTS trg_tasks_employee_fk_insert
BEFORE INSERT ON tasks
FOR EACH ROW
WHEN NEW.employee_id IS NOT NULL AND NEW.employee_id != 0 AND (SELECT id FROM employees WHERE id = NEW.employee_id) IS NULL
BEGIN
    SELECT RAISE(ABORT, 'tasks.employee_id must reference existing employees.id or be 0 for Unassigned');
END;

-- Tasks: validate on UPDATE of employee_id
CREATE TRIGGER IF NOT EXISTS trg_tasks_employee_fk_update
BEFORE UPDATE OF employee_id ON tasks
FOR EACH ROW
WHEN NEW.employee_id IS NOT NULL AND NEW.employee_id != 0 AND (SELECT id FROM employees WHERE id = NEW.employee_id) IS NULL
BEGIN
    SELECT RAISE(ABORT, 'tasks.employee_id must reference existing employees.id or be 0 for Unassigned');
END;

-- Employees: restrict delete when referenced by times
CREATE TRIGGER IF NOT EXISTS trg_employees_restrict_delete_times
BEFORE DELETE ON employees
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM times WHERE employee_id = OLD.id)
BEGIN
    SELECT RAISE(ABORT, 'Cannot delete employee: referenced by times');
END;

-- Employees: restrict delete when referenced by tasks
CREATE TRIGGER IF NOT EXISTS trg_employees_restrict_delete_tasks
BEFORE DELETE ON employees
FOR EACH ROW
WHEN EXISTS (SELECT 1 FROM tasks WHERE employee_id = OLD.id)
BEGIN
    SELECT RAISE(ABORT, 'Cannot delete employee: referenced by tasks');
END;
