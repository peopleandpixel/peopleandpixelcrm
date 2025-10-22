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

    /**
     * Apply all pending "up" migrations. Backward compatible with previous behavior.
     */
    public function migrate(): void
    {
        $this->migrateTo(null);
    }

    /**
     * Migrate database to a target version.
     *
     * - Version is parsed from migration filenames: NNN_description.sql
     * - Down migrations are executed from files named NNN_description.down.sql when rolling back
     * - If $targetVersion is null, migrates to the latest available version (all ups)
     */
    public function migrateTo(?int $targetVersion): void
    {
        if (!is_dir($this->migrationsDir)) {
            return; // nothing to do
        }
        $map = $this->discoverMigrations(); // version => ['up'=>file, 'down'=>?file, 'checksum'=>sha1(upSql)]
        if ($map === []) { return; }
        ksort($map, SORT_NUMERIC);
        $applied = $this->fetchApplied();
        $current = $this->currentVersion($applied, $map);
        $latest = (int)max(array_keys($map));
        $target = $targetVersion === null ? $latest : max(0, $targetVersion);

        if ($target > $current) {
            // Apply ups from current+1 .. target
            for ($v = $current + 1; $v <= $target; $v++) {
                if (!isset($map[$v])) { continue; }
                $upFile = $map[$v]['up'];
                $filename = basename($upFile);
                $sql = file_get_contents($upFile) ?: '';
                $checksum = sha1($sql);
                if (isset($applied[$filename]) && $applied[$filename] === $checksum) {
                    continue; // idempotent
                }
                $this->runSqlInTransaction($sql);
                $this->recordApplied($filename, $checksum);
            }
            return;
        }

        if ($target < $current) {
            // Rollback downs from current .. target+1
            for ($v = $current; $v > $target; $v--) {
                if (!isset($map[$v])) { continue; }
                $upFile = $map[$v]['up'];
                $downFile = $map[$v]['down'] ?? null;
                $filename = basename($upFile);
                if (!isset($applied[$filename])) {
                    // Not marked applied; skip
                    continue;
                }
                if ($downFile && is_file($downFile)) {
                    $sql = file_get_contents($downFile) ?: '';
                    $this->runSqlInTransaction($sql);
                }
                $this->removeApplied($filename);
                unset($applied[$filename]);
            }
        }
    }

    /**
     * Convenience: rollback a number of last applied migrations.
     */
    public function rollbackLast(int $steps = 1): void
    {
        $map = $this->discoverMigrations();
        $applied = $this->fetchApplied();
        $current = $this->currentVersion($applied, $map);
        $this->migrateTo(max(0, $current - max(0, $steps)));
    }

    private function runSqlInTransaction(string $sql): void
    {
        $this->pdo->beginTransaction();
        try {
            $statements = array_filter(array_map('trim', preg_split('/;\s*\n|;\s*$/m', $sql)));
            foreach ($statements as $stmtSql) {
                if ($stmtSql === '') continue;
                $this->pdo->exec($stmtSql);
            }
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Build map of available migrations and optional downs.
     * @return array<int, array{up:string, down: (string|null), checksum:string}>
     */
    private function discoverMigrations(): array
    {
        $files = glob($this->migrationsDir . '/*.sql') ?: [];
        $map = [];
        foreach ($files as $file) {
            $base = basename($file);
            if (preg_match('/^(\d+)_.+\.sql$/', $base, $m)) {
                if (str_ends_with($base, '.down.sql')) { continue; }
                $v = (int)$m[1];
                $down = $this->migrationsDir . '/' . preg_replace('/\.sql$/', '.down.sql', $base);
                $sql = file_get_contents($file) ?: '';
                $map[$v] = [
                    'up' => $file,
                    'down' => is_file($down) ? $down : null,
                    'checksum' => sha1($sql),
                ];
            }
        }
        return $map;
    }

    /**
     * Determine current version from applied filenames and known map.
     * Version = highest version whose up filename is recorded as applied with matching checksum (if known).
     * @param array<string,string> $applied
     * @param array<int, array{up:string, down:(string|null), checksum:string}> $map
     */
    private function currentVersion(array $applied, array $map): int
    {
        $versions = [0];
        foreach ($map as $v => $info) {
            $fname = basename($info['up']);
            if (isset($applied[$fname])) {
                // If checksum in table mismatches current file (edited), we still treat as applied for safety
                $versions[] = (int)$v;
            }
        }
        return (int)max($versions);
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

    private function removeApplied(string $filename): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM migrations WHERE filename = :f');
        $stmt->execute(['f' => $filename]);
    }
}
