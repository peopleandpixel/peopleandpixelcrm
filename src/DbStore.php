<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Simple DB-backed store with the same public API as JsonStore: all(), add(), saveAll().
 *
 * Defaults to SQLite in data/app.sqlite but can be configured via env:
 * - DB_DSN (e.g., sqlite:/path/to/db.sqlite, pgsql:host=...;dbname=..., mysql:host=...;dbname=...)
 * - DB_USER
 * - DB_PASS
 *
 * The constructor accepts a table name ("contacts" or "times").
 */
class DbStore implements StoreInterface
{
    private PDO $pdo;
    private string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
        $this->pdo = self::connect();
        $this->ensureSchema();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        try {
            $stmt = $this->pdo->query('SELECT * FROM ' . $this->qi($this->table) . ' ORDER BY id ASC');
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            return array_map(fn($r) => $this->castRow($r), $rows);
        } catch (PDOException $e) {
            throw new PDOException('Failed fetching all from table ' . $this->table . ': ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function paginate(int $limit, int $offset = 0, ?string $sort = 'id', string $dir = 'asc', array $filters = []): array
    {
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);
        $cols = $this->columnsForTable();
        // Build WHERE
        $where = [];
        $params = [];
        foreach ($filters as $k => $v) {
            if (in_array($k, $cols, true)) {
                $where[] = $this->qi($k) . ' = :' . $k;
                $params[$k] = $v;
            }
        }
        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
        // Total
        $stmt = $this->run('SELECT COUNT(*) AS c FROM ' . $this->qi($this->table) . $whereSql, $params);
        $total = (int)($stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
        // Order
        $orderCol = in_array((string)$sort, $cols, true) ? (string)$sort : 'id';
        $direction = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
        $sql = 'SELECT * FROM ' . $this->qi($this->table) . $whereSql . ' ORDER BY ' . $this->qi($orderCol) . ' ' . $direction . ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        $stmt2 = $this->run($sql, $params);
        $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $items = array_map(fn($r) => $this->castRow($r), $rows);
        return [
            'items' => $items,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'sort' => $orderCol,
            'dir' => strtolower($dir) === 'desc' ? 'desc' : 'asc',
            'filters' => $filters,
        ];
    }

    /**
     * Get a single row by id or null
     * @return array<string,mixed>|null
     */
    public function get(int $id): ?array
    {
        try {
            $stmt = $this->run('SELECT * FROM ' . $this->qi($this->table) . ' WHERE id = :id', ['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $this->castRow($row) : null;
        } catch (PDOException $e) {
            throw new PDOException('Failed fetching from ' . $this->table . ' by id: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function find(int $id): ?array
    {
        return $this->get($id);
    }

    /**
     * @param array<string,mixed> $item
     */
    public function add(array $item): array
    {
        // Filter to known columns
        $cols = $this->columnsForTable();
        $data = [];
        foreach ($cols as $col) {
            if (array_key_exists($col, $item)) {
                $data[$col] = $item[$col];
            }
        }
        // JSON encode composite fields for contacts
        if ($this->table === 'contacts') {
            foreach (['phones','emails','websites','socials'] as $k) {
                if (array_key_exists($k, $data)) {
                    $data[$k] = json_encode($data[$k] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }
        }

        $placeholders = [];
        $columns = [];
        foreach ($data as $col => $val) {
            $columns[] = $this->qi($col);
            $placeholders[] = ':' . $col;
        }

        $sql = 'INSERT INTO ' . $this->qi($this->table) .
            ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
        try {
            $this->run($sql, $data);
            // If explicit id was provided, use it; otherwise fetch lastInsertId
            if (!isset($data['id'])) {
                $item['id'] = (int)$this->pdo->lastInsertId();
            } else {
                $item['id'] = (int)$data['id'];
            }
            return $item;
        } catch (PDOException $e) {
            throw new PDOException('Failed inserting into ' . $this->table . ': ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Update a row by id with provided fields
     * @param array<string,mixed> $fields
     */
    public function update(int $id, array $fields): ?array
    {
        unset($fields['id']);
        if (empty($fields)) {
            return $this->get($id);
        }
        $cols = $this->columnsForTable();
        $setParts = [];
        $data = ['id' => $id];
        foreach ($fields as $k => $v) {
            if (in_array($k, $cols, true)) {
                $setParts[] = $this->qi($k) . ' = :' . $k;
                $data[$k] = $v;
            }
        }
        if ($this->table === 'contacts') {
            foreach (['phones','emails','websites','socials'] as $k) {
                if (array_key_exists($k, $data)) {
                    $data[$k] = json_encode($data[$k] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }
        }
        if (empty($setParts)) {
            return $this->get($id);
        }
        $sql = 'UPDATE ' . $this->qi($this->table) . ' SET ' . implode(', ', $setParts) . ' WHERE id = :id';
        try {
            $this->run($sql, $data);
            return $this->get($id);
        } catch (PDOException $e) {
            throw new PDOException('Failed updating ' . $this->table . ' id ' . $id . ': ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->run('DELETE FROM ' . $this->qi($this->table) . ' WHERE id = :id', ['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new PDOException('Failed deleting from ' . $this->table . ' id ' . $id . ': ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Replace all rows with provided items.
     * @param array<int, array<string,mixed>> $items
     */
    public function saveAll(array $items): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->run('DELETE FROM ' . $this->qi($this->table));
            foreach ($items as $it) {
                $this->add($it);
            }
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Execute a callable within a DB transaction and return its result.
     * @template T
     * @param callable():T $callback
     * @return mixed
     */
    public function withTransaction(callable $callback)
    {
        $this->pdo->beginTransaction();
        try {
            $result = $callback();
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // --- Internals ---

    private static function connect(): PDO
    {
        // Prefer $_ENV (loaded by phpdotenv) and fall back to getenv
        $dsn = $_ENV['DB_DSN'] ?? (getenv('DB_DSN') ?: '');
        $user = $_ENV['DB_USER'] ?? (getenv('DB_USER') ?: '');
        $pass = $_ENV['DB_PASS'] ?? (getenv('DB_PASS') ?: '');

        if (!$dsn) {
            // default to SQLite under data/ (allow override via DATA_DIR)
            $dataDir = $_ENV['DATA_DIR'] ?? (getenv('DATA_DIR') ?: (dirname(__DIR__) . '/data'));
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0777, true);
            }
            $dbFile = rtrim($dataDir, '/') . '/app.sqlite';
            $dsn = 'sqlite:' . $dbFile;
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $pdo = new PDO($dsn, $user ?: null, $pass ?: null, $options);
        // Enforce foreign keys in SQLite
        if (stripos($dsn, 'sqlite:') === 0) {
            $pdo->exec('PRAGMA foreign_keys = ON');
        }
        return $pdo;
    }

    private function ensureSchema(): void
    {
        // Run SQL migrations once per application lifecycle
        $migrator = new Migrator($this->pdo, dirname(__DIR__) . '/migrations');
        $migrator->migrate();
    }

    /**
     * @return list<string>
     */
    private function columnsForTable(): array
    {
        return match ($this->table) {
            'contacts' => ['id', 'name', 'email', 'phone', 'company', 'notes', 'birthdate', 'picture', 'phones', 'emails', 'websites', 'socials', 'created_at'],
            'times' => ['id', 'contact_id', 'employee_id', 'date', 'hours', 'description', 'start_time', 'end_time', 'created_at'],
            'tasks' => ['id', 'project_id', 'contact_id', 'employee_id', 'title', 'due_date', 'done_date', 'status', 'notes', 'created_at'],
            'employees' => ['id', 'name', 'email', 'phone', 'role', 'salary', 'hired_at', 'notes', 'created_at'],
            'candidates' => ['id', 'name', 'email', 'phone', 'position', 'status', 'notes', 'created_at'],
            'payments' => ['id', 'date', 'type', 'amount', 'counterparty', 'description', 'category', 'tags', 'created_at'],
            'storage' => ['id', 'sku', 'name', 'quantity', 'location', 'notes', 'low_stock_threshold', 'created_at'],
            'storage_adjustments' => ['id', 'item_id', 'delta', 'note', 'created_at'],
            default => ['id', 'created_at'],
        };
    }

    /**
     * Quote identifier (very simple; good for SQLite/MySQL/Postgres default cases)
     */
    private function qi(string $ident): string
    {
        // Use double quotes to be ANSI SQL compatible; most drivers accept it.
        return '"' . str_replace('"', '""', $ident) . '"';
    }

    /**
     * Prepare and execute a statement with consistent error handling.
     * @param array<string,mixed> $params
     */
    private function run(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $msg = 'DB error on table ' . $this->table . ' with SQL [' . $sql . ']: ' . $e->getMessage();
            throw new PDOException($msg, (int)$e->getCode(), $e);
        }
    }

    /**
     * Cast numeric fields appropriately.
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    private function castRow(array $row): array
    {
        if (isset($row['id'])) $row['id'] = (int)$row['id'];
        if ($this->table === 'contacts') {
            foreach (['phones','emails','websites','socials'] as $k) {
                if (isset($row[$k]) && is_string($row[$k]) && $row[$k] !== '') {
                    $decoded = json_decode($row[$k], true);
                    if (is_array($decoded)) { $row[$k] = $decoded; }
                }
            }
        } elseif ($this->table === 'times') {
            if (isset($row['contact_id'])) $row['contact_id'] = (int)$row['contact_id'];
            if (isset($row['employee_id'])) $row['employee_id'] = (int)$row['employee_id'];
            if (isset($row['hours'])) $row['hours'] = (float)$row['hours'];
        } elseif ($this->table === 'tasks') {
            if (isset($row['project_id'])) $row['project_id'] = (int)$row['project_id'];
            if (isset($row['contact_id'])) $row['contact_id'] = (int)$row['contact_id'];
            if (isset($row['employee_id'])) $row['employee_id'] = (int)$row['employee_id'];
        } elseif ($this->table === 'employees') {
            if (isset($row['salary'])) $row['salary'] = (float)$row['salary'];
        } elseif ($this->table === 'payments') {
            if (isset($row['amount'])) $row['amount'] = (float)$row['amount'];
        } elseif ($this->table === 'storage') {
            if (isset($row['quantity'])) $row['quantity'] = (int)$row['quantity'];
            if (isset($row['low_stock_threshold'])) $row['low_stock_threshold'] = (int)$row['low_stock_threshold'];
        } elseif ($this->table === 'storage_adjustments') {
            if (isset($row['item_id'])) $row['item_id'] = (int)$row['item_id'];
            if (isset($row['delta'])) $row['delta'] = (int)$row['delta'];
        }
        return $row;
    }
}
