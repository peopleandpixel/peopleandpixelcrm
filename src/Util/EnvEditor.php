<?php

declare(strict_types=1);

namespace App\Util;

use RuntimeException;

/**
 * Minimal .env reader/writer that preserves unknown lines and comments.
 * - Updates only provided keys (simple KEY=VALUE format without quotes escaping sophistication).
 * - Creates timestamped backup before writing.
 */
final class EnvEditor
{
    /**
     * Load .env file as lines.
     * @return array<int,string>
     */
    public static function readLines(string $path): array
    {
        if (!is_file($path)) return [];
        $content = (string)file_get_contents($path);
        return $content === '' ? [] : preg_split("/\r?\n/", $content);
    }

    /**
     * Update given keys in .env file.
     * - $updates: [KEY => VALUE|null]; when VALUE is null, the key is removed.
     * - returns true on success.
     */
    public static function update(string $path, array $updates): bool
    {
        // Normalize keys to upper-case and trim
        $normUpdates = [];
        foreach ($updates as $k => $v) {
            if (!is_string($k) || $k === '') continue;
            $normUpdates[strtoupper($k)] = $v;
        }
        $lines = self::readLines($path);
        $seen = [];
        $out = [];
        foreach ($lines as $line) {
            $m = [];
            if (preg_match('/^\s*([A-Z0-9_]+)\s*=\s*(.*)\s*$/', $line, $m)) {
                $key = $m[1];
                if (array_key_exists($key, $normUpdates)) {
                    $seen[$key] = true;
                    $val = $normUpdates[$key];
                    if ($val === null) {
                        // remove line (skip)
                        continue;
                    }
                    $out[] = $key . '=' . self::encodeValue($val);
                    continue;
                }
            }
            $out[] = $line;
        }
        // Append missing keys
        foreach ($normUpdates as $key => $val) {
            if (isset($seen[$key])) continue;
            if ($val === null) continue; // nothing to add
            $out[] = $key . '=' . self::encodeValue($val);
        }

        self::backup($path);
        $data = implode("\n", $out);
        if (@file_put_contents($path, $data) === false) {
            throw new RuntimeException('Failed to write .env');
        }
        return true;
    }

    /**
     * Create a timestamped backup alongside the file (var/backups if possible).
     */
    public static function backup(string $path): void
    {
        $dir = dirname($path);
        $backupDir = dirname($dir) . '/var/backups';
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0777, true);
        }
        $ts = date('Ymd_His');
        $target = $backupDir . '/.env.' . $ts . '.bak';
        if (is_file($path)) {
            @copy($path, $target);
        }
    }

    /**
     * Very basic encoding: if value contains spaces or special chars, wrap in quotes.
     */
    private static function encodeValue(string $value): string
    {
        // Do not modify empty string
        if ($value === '') return '';
        // If already quoted, keep as-is
        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) return $value;
        if (preg_match('/[^A-Za-z0-9_\-.:]/', $value)) {
            // Escape existing quotes
            $escaped = str_replace(['"', "'"], ['\\"', "'"], $value);
            return '"' . $escaped . '"';
        }
        return $value;
    }
}
