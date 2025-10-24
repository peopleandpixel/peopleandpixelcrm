<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;

final class BackupService
{
    public function __construct(private readonly Config $config)
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
        if ($this->config->useDb()) {
            // For now, only JSON backend is supported for automated backups
            throw new \RuntimeException('Backups via UI are supported only for JSON storage mode at the moment.');
        }
        $dataDir = $this->config->getDataDir();
        $backupDir = $this->getBackupDir();
        $stamp = date('Ymd-His');
        $zipPath = $backupDir . '/snapshot-' . $stamp . '.zip';

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

        return $zipPath;
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
        $path = $this->resolvePath($file);
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) { return ['ok' => false, 'errors' => ['Unable to open snapshot']]; }
        $manifestRaw = $zip->getFromName('manifest.json');
        if (!is_string($manifestRaw)) { $zip->close(); return ['ok' => false, 'errors' => ['Manifest not found']]; }
        $manifest = json_decode($manifestRaw, true);
        $errors = [];
        if (!is_array($manifest) || !isset($manifest['files']) || !is_array($manifest['files'])) {
            $zip->close();
            return ['ok' => false, 'errors' => ['Invalid manifest']];
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
        return ['ok' => empty($errors), 'errors' => $errors];
    }

    /** Restore snapshot into data directory after verification. Creates a pre-restore backup automatically. */
    public function restoreSnapshot(string $file): void
    {
        if ($this->config->useDb()) {
            throw new \RuntimeException('Restore via UI is supported only for JSON storage mode at the moment.');
        }
        $path = $this->resolvePath($file);
        $verify = $this->verifySnapshot($file);
        if (!$verify['ok']) {
            throw new \RuntimeException('Snapshot failed verification: ' . implode('; ', $verify['errors']));
        }
        // Pre-restore safety backup
        try { $this->createSnapshot(); } catch (\Throwable $e) { /* best effort */ }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) { throw new \RuntimeException('Unable to open snapshot.'); }
        $dataDir = $this->config->getDataDir();
        // Extract to temp dir then move
        $tmp = $this->getBackupDir() . '/.restore-' . bin2hex(random_bytes(6));
        @mkdir($tmp, 0777, true);
        if (!$zip->extractTo($tmp)) { $zip->close(); throw new \RuntimeException('Failed to extract snapshot.'); }
        $zip->close();
        // Move files from tmp/data/*.json to dataDir
        $extracted = glob($tmp . '/data/*.json') ?: [];
        foreach ($extracted as $src) {
            $dest = $dataDir . '/' . basename($src);
            // Write atomically: write to temp then rename
            $contents = file_get_contents($src);
            if ($contents === false) { throw new \RuntimeException('Failed to read extracted file'); }
            $this->writeAtomic($dest, $contents);
        }
        // Cleanup
        $this->rrmdir($tmp);
    }

    public function deleteSnapshot(string $file): bool
    {
        $path = $this->resolvePath($file);
        return @unlink($path);
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
