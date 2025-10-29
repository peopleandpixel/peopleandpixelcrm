<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;

final class BackupService
{
    public function __construct(private readonly \App\Config $config, private readonly ?\App\Service\MetricsService $metrics = null)
    {
    }

    public function getBackupDir(): string
    {
        $dir = $this->config->getVarDir() . '/backups';
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        return $dir;
    }

    /**
     * Create a snapshot ZIP of the JSON data directory with a manifest including checksums.
     * Returns the absolute path to the created snapshot file.
     */
    public function createSnapshot(): string
    {
        $t0 = microtime(true);
        if ($this->config->useDb()) {
            // For now, only JSON backend is supported for automated backups
            $e = new \RuntimeException('Backups via UI are supported only for JSON storage mode at the moment.');
            if ($this->metrics && $this->metrics->isEnabled()) {
                $this->metrics->recordBackup(['action' => 'create', 'ok' => false, 'error' => $e->getMessage(), 'duration_ms' => 0]);
            }
            throw $e;
        }
        $dataDir = $this->config->getDataDir();
        $backupDir = $this->getBackupDir();
        $stamp = date('Ymd-His');
        $zipPath = $backupDir . '/snapshot-' . $stamp . '.zip';

        try {
            $files = glob($dataDir . '/*.json') ?: [];
            $manifest = [
                'created_at' => date(DATE_ATOM),
                'storage' => 'json',
                'data_dir' => $dataDir,
                'files' => [],
                'version' => 1,
            ];
            foreach ($files as $file) {
                $base = basename($file);
                $raw = @file_get_contents($file);
                if ($raw === false) { throw new \RuntimeException('Failed to read ' . $file); }
                $decoded = json_decode($raw, true);
                if (!is_array($decoded) && $decoded !== []) {
                    throw new \RuntimeException('Integrity check failed for ' . $base . ' (invalid JSON).');
                }
                $manifest['files'][] = [
                    'name' => $base,
                    'size' => filesize($file) ?: 0,
                    'sha256' => hash('sha256', $raw),
                    'count' => is_array($decoded) ? count($decoded) : 0,
                ];
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Unable to create backup file.');
            }
            // Add data files
            foreach ($files as $file) {
                $zip->addFile($file, 'data/' . basename($file));
            }
            // Add manifest.json
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $zip->close();

            // Retention enforcement
            $this->enforceRetention($this->getRetention());

            if ($this->metrics && $this->metrics->isEnabled()) {
                $bytes = @filesize($zipPath) ?: 0;
                $this->metrics->recordBackup([
                    'action' => 'create',
                    'ok' => true,
                    'filename' => basename($zipPath),
                    'bytes' => $bytes,
                    'duration_ms' => (float)round((microtime(true)-$t0)*1000, 3)
                ]);
            }
            return $zipPath;
        } catch (\Throwable $e) {
            if ($this->metrics && $this->metrics->isEnabled()) {
                $this->metrics->recordBackup([
                    'action' => 'create',
                    'ok' => false,
                    'error' => $e->getMessage(),
                    'duration_ms' => (float)round((microtime(true)-$t0)*1000, 3)
                ]);
            }
            throw $e;
        }
    }

    /** Return a list of snapshots with metadata (sorted newest first). */
    public function listSnapshots(): array
    {
        $dir = $this->getBackupDir();
        $files = glob($dir . '/*.zip') ?: [];
        $out = [];
        foreach ($files as $path) {
            $meta = $this->readManifest($path);
            $out[] = [
                'file' => basename($path),
                'path' => $path,
                'size' => filesize($path) ?: 0,
                'created_at' => $meta['created_at'] ?? date(DATE_ATOM, @filemtime($path) ?: time()),
                'files_count' => isset($meta['files']) && is_array($meta['files']) ? count($meta['files']) : 0,
                'storage' => $meta['storage'] ?? 'json',
            ];
        }
        usort($out, fn($a,$b) => strcmp($b['file'], $a['file']));
        return $out;
    }

    /** Verify all checksums in the snapshot; return [ok=>bool, errors=>string[]] */
    public function verifySnapshot(string $file): array
    {
        $t0 = microtime(true);
        $path = $this->resolvePath($file);
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            $res = ['ok' => false, 'errors' => ['Unable to open snapshot']];
            if ($this->metrics && $this->metrics->isEnabled()) {
                $this->metrics->recordBackup(['action' => 'verify', 'ok' => false, 'filename' => basename($path), 'error' => 'open_failed', 'duration_ms' => (float)round((microtime(true)-$t0)*1000,3)]);
            }
            return $res;
        }
        $manifestRaw = $zip->getFromName('manifest.json');
        if (!is_string($manifestRaw)) { $zip->close();
            $res = ['ok' => false, 'errors' => ['Manifest not found']];
            if ($this->metrics && $this->metrics->isEnabled()) {
                $this->metrics->recordBackup(['action' => 'verify', 'ok' => false, 'filename' => basename($path), 'error' => 'no_manifest', 'duration_ms' => (float)round((microtime(true)-$t0)*1000,3)]);
            }
            return $res; }
        $manifest = json_decode($manifestRaw, true);
        $errors = [];
        if (!is_array($manifest) || !isset($manifest['files']) || !is_array($manifest['files'])) {
            $zip->close();
            $res = ['ok' => false, 'errors' => ['Invalid manifest']];
            if ($this->metrics && $this->metrics->isEnabled()) {
                $this->metrics->recordBackup(['action' => 'verify', 'ok' => false, 'filename' => basename($path), 'error' => 'invalid_manifest', 'duration_ms' => (float)round((microtime(true)-$t0)*1000,3)]);
            }
            return $res;
        }
        foreach ($manifest['files'] as $fi) {
            $name = 'data/' . ($fi['name'] ?? '');
            $content = $zip->getFromName($name);
            if (!is_string($content)) { $errors[] = 'Missing file in archive: ' . $name; continue; }
            $hash = hash('sha256', $content);
            if (($fi['sha256'] ?? '') !== $hash) {
                $errors[] = 'Checksum mismatch for ' . ($fi['name'] ?? 'unknown');
            }
            $decoded = json_decode($content, true);
            if (!is_array($decoded) && $decoded !== []) {
                $errors[] = 'Invalid JSON in ' . ($fi['name'] ?? 'unknown');
            }
        }
        $zip->close();
        $ok = empty($errors);
        if ($this->metrics && $this->metrics->isEnabled()) {
            $this->metrics->recordBackup(['action' => 'verify', 'ok' => $ok, 'filename' => basename($path), 'duration_ms' => (float)round((microtime(true)-$t0)*1000,3)]);
        }
        return ['ok' => $ok, 'errors' => $errors];
    }

    /** Restore snapshot into data directory after verification. Creates a pre-restore backup automatically. */
    public function restoreSnapshot(string $file): void
    {
        $t0 = microtime(true);
        if ($this->config->useDb()) {
            $e = new \RuntimeException('Restore via UI is supported only for JSON storage mode at the moment.');
            if ($this->metrics && $this->metrics->isEnabled()) {
                $this->metrics->recordBackup(['action' => 'restore', 'ok' => false, 'filename' => basename($file), 'error' => 'db_mode', 'duration_ms' => 0]);
            }
            throw $e;
        }
        $path = $this->resolvePath($file);
        $verify = $this->verifySnapshot($file);
        if (!$verify['ok']) {
            $e = new \RuntimeException('Snapshot failed verification: ' . implode('; ', $verify['errors']));
            if ($this->metrics && $this->metrics->isEnabled()) {
                $this->metrics->recordBackup(['action' => 'restore', 'ok' => false, 'filename' => basename($path), 'error' => 'verify_failed', 'duration_ms' => (float)round((microtime(true)-$t0)*1000,3)]);
            }
            throw $e;
        }
        // Pre-restore safety backup
        try { $this->createSnapshot(); } catch (\Throwable $e) { /* best effort */ }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) { throw new \RuntimeException('Unable to open snapshot.'); }
        $dataDir = $this->config->getDataDir();
        // Extract to temp dir then move
        $tmp = $this->getBackupDir() . '/.restore-' . bin2hex(random_bytes(6));
        @mkdir($tmp, 0777, true);
        if (!$zip->extractTo($tmp)) { $zip->close(); $e = new \RuntimeException('Failed to extract snapshot.'); if ($this->metrics && $this->metrics->isEnabled()) { $this->metrics->recordBackup(['action'=>'restore','ok'=>false,'filename'=>basename($path),'error'=>'extract_failed','duration_ms'=>(float)round((microtime(true)-$t0)*1000,3)]);} throw $e; }
        $zip->close();
        // Move files from tmp/data/*.json to dataDir
        $extracted = glob($tmp . '/data/*.json') ?: [];
        foreach ($extracted as $src) {
            $dest = $dataDir . '/' . basename($src);
            // Write atomically: write to temp then rename
            $contents = file_get_contents($src);
            if ($contents === false) { $e = new \RuntimeException('Failed to read extracted file'); if ($this->metrics && $this->metrics->isEnabled()) { $this->metrics->recordBackup(['action'=>'restore','ok'=>false,'filename'=>basename($path),'error'=>'read_extracted_failed','duration_ms'=>(float)round((microtime(true)-$t0)*1000,3)]);} throw $e; }
            $this->writeAtomic($dest, $contents);
        }
        // Cleanup
        $this->rrmdir($tmp);
        if ($this->metrics && $this->metrics->isEnabled()) {
            $this->metrics->recordBackup(['action' => 'restore', 'ok' => true, 'filename' => basename($path), 'duration_ms' => (float)round((microtime(true)-$t0)*1000,3)]);
        }
    }

    public function deleteSnapshot(string $file): bool
    {
        $t0 = microtime(true);
        $path = $this->resolvePath($file);
        $ok = @unlink($path);
        if ($this->metrics && $this->metrics->isEnabled()) {
            $this->metrics->recordBackup([
                'action' => 'delete',
                'ok' => $ok,
                'filename' => basename($path),
                'duration_ms' => (float)round((microtime(true)-$t0)*1000,3)
            ]);
        }
        return $ok;
    }

    public function getRetention(): int
    {
        $v = $this->config->getEnv('BACKUP_RETENTION');
        $n = is_numeric($v) ? (int)$v : 10;
        return max(1, min(1000, $n));
    }

    public function enforceRetention(int $keep): void
    {
        $dir = $this->getBackupDir();
        $files = glob($dir . '/*.zip') ?: [];
        if (count($files) <= $keep) { return; }
        usort($files, fn($a,$b) => strcmp($a, $b)); // oldest first by name (timestamp in name)
        $toDelete = array_slice($files, 0, count($files) - $keep);
        foreach ($toDelete as $f) { @unlink($f); }
    }

    private function writeAtomic(string $path, string $contents): void
    {
        $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
        if (@file_put_contents($tmp, $contents) === false) {
            throw new \RuntimeException('Failed writing temp file for restore');
        }
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            throw new \RuntimeException('Failed moving restored file into place');
        }
    }

    /** @return array<string,mixed> */
    private function readManifest(string $zipPath): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) { return []; }
        $raw = $zip->getFromName('manifest.json');
        $zip->close();
        if (!is_string($raw)) { return []; }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function resolvePath(string $file): string
    {
        $path = $file;
        if (!str_contains($file, DIRECTORY_SEPARATOR)) {
            $path = $this->getBackupDir() . '/' . $file;
        }
        if (!is_file($path)) { throw new \RuntimeException('Snapshot not found: ' . basename($file)); }
        return $path;
    }

    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $it = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $f) {
            if ($f->isDir()) { @rmdir($f->getRealPath()); } else { @unlink($f->getRealPath()); }
        }
        @rmdir($dir);
    }
}
