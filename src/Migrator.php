<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

/**
 * Very small SQL migrations runner.
 *
 * - Looks for .sql files under migrations/ with names like 001_*.sql
 * - Applies them in lexicographical order inside a transaction (per file)
 * - Records applied migration filename and checksum in a migrations table
 */
class Migrator
{
    private PDO $pdo;
    private string $migrationsDir;

    public function __construct(PDO $pdo, ?string $migrationsDir = null)
    {
        $this->pdo = $pdo;
        $this->migrationsDir = $migrationsDir ?: dirname(__DIR__) . '/migrations';
        $this->ensureMigrationsTable();
    }

    public function migrate(): void
    {
        if (!is_dir($this->migrationsDir)) {
            // No migrations directory: nothing to do
            return;
        }

        $files = glob($this->migrationsDir . '/*.sql');
        if (!$files) {
            return;
        }
        sort($files, SORT_NATURAL | SORT_FLAG_CASE);

        $applied = $this->fetchApplied();

        foreach ($files as $file) {
            $filename = basename($file);
            $sql = file_get_contents($file) ?: '';
            $checksum = sha1($sql);

            if (isset($applied[$filename]) && $applied[$filename] === $checksum) {
                continue; // already applied, matching checksum
            }

            $this->pdo->beginTransaction();
            try {
                // Split by semicolon; keep it naive but robust to simple cases
                $statements = array_filter(array_map('trim', preg_split('/;\s*\n|;\s*$/m', $sql)));
                foreach ($statements as $stmtSql) {
                    if ($stmtSql === '') continue;
                    $this->pdo->exec($stmtSql);
                }
                $this->recordApplied($filename, $checksum);
                $this->pdo->commit();
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        }
    }

    private function ensureMigrationsTable(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            filename TEXT NOT NULL UNIQUE,
            checksum TEXT NOT NULL,
            applied_at TEXT NOT NULL
        )');
    }

    /**
     * @return array<string,string> filename => checksum
     */
    private function fetchApplied(): array
    {
        $stmt = $this->pdo->query('SELECT filename, checksum FROM migrations');
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        $out = [];
        foreach ($rows as $r) {
            $out[$r['filename']] = $r['checksum'];
        }
        return $out;
    }

    private function recordApplied(string $filename, string $checksum): void
    {
        $stmt = $this->pdo->prepare('INSERT OR REPLACE INTO migrations(filename, checksum, applied_at) VALUES (:f, :c, :t)');
        $stmt->execute(['f' => $filename, 'c' => $checksum, 't' => \App\Util\Dates::nowAtom()]);
    }
}
