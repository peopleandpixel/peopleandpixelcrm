<?php

declare(strict_types=1);

namespace App\Util;

/**
 * Minimal .env writer that preserves existing keys (best-effort) and writes atomically.
 * It only updates/sets the provided keys; other lines are kept as-is.
 */
class EnvWriter
{
    /**
     * @param array<string,string> $vars
     */
    public static function write(string $envFile, array $vars): void
    {
        $lines = file_exists($envFile) ? file($envFile, FILE_IGNORE_NEW_LINES) : [];
        if ($lines === false) { $lines = []; }

        // Build a map of existing keys -> line index
        $indexByKey = [];
        foreach ($lines as $i => $line) {
            if ($line === '' || str_starts_with($line, '#')) continue;
            $pos = strpos($line, '=');
            if ($pos === false) continue;
            $key = substr($line, 0, $pos);
            if ($key !== '') {
                $indexByKey[$key] = $i;
            }
        }

        foreach ($vars as $k => $v) {
            // Escape value for .env (wrap in quotes if value contains spaces or special characters)
            $needQuotes = strpbrk($v, " \t\r\n#\"'=") !== false;
            $val = $needQuotes ? '"' . addcslashes($v, "\n\r\t\"\\") . '"' : $v;
            $line = $k . '=' . $val;
            if (isset($indexByKey[$k])) {
                $lines[$indexByKey[$k]] = $line;
            } else {
                $lines[] = $line;
            }
        }

        // Atomic write
        $dir = dirname($envFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $tmp = $envFile . '.tmp';
        file_put_contents($tmp, implode(PHP_EOL, $lines) . PHP_EOL);
        @chmod($tmp, 0664);
        rename($tmp, $envFile);
    }
}
