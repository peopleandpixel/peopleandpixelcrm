<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;

/**
 * Lightweight file-backed metrics recorder (NDJSON) with simple summaries.
 * Stores events under var/metrics/app-YYYY-MM-DD.ndjson
 */
final class MetricsService
{
    private string $dir;
    private bool $enabled;
    private int $retentionDays;

    public function __construct(private readonly Config $config)
    {
        $var = rtrim($this->config->getVarDir(), '/');
        $this->dir = $var . '/metrics';
        $this->enabled = (int)($this->config->getEnv('METRICS_ENABLED') ?? '1') === 1;
        $this->retentionDays = (int)($this->config->getEnv('METRICS_RETENTION_DAYS') ?? '14');
        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0777, true);
        }
    }

    public function isEnabled(): bool { return $this->enabled; }

    /**
     * Record a metrics event to today's NDJSON file.
     * @param array<string,mixed> $data
     * @param array<string,mixed> $ctx
     */
    public function record(string $type, array $data, array $ctx = []): void
    {
        if (!$this->enabled) return;
        $ts = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(DATE_ATOM);
        $row = json_encode([
            'ts' => $ts,
            'type' => $type,
            'ctx' => $ctx,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
        if (!is_string($row)) return;
        $path = $this->dir . '/app-' . (new \DateTimeImmutable('now'))->format('Y-m-d') . '.ndjson';
        $this->appendLine($path, $row . "\n");
        // opportunistic prune daily
        static $lastPruneDay = '';
        $day = substr($path, -18, 10); // YYYY-MM-DD
        if ($day !== $lastPruneDay) {
            $lastPruneDay = $day;
            $this->prune($this->retentionDays);
        }
    }

    /** @param array<string,mixed> $ctx */
    public function recordRequest(array $data, array $ctx = []): void
    {
        $this->record('request', $data, $ctx);
    }

    /** @param array<string,mixed> $ctx */
    public function recordError(array $data, array $ctx = []): void
    {
        $this->record('error', $data, $ctx);
    }

    /** @param array<string,mixed> $ctx */
    public function recordBackup(array $data, array $ctx = []): void
    {
        $this->record('backup', $data, $ctx);
    }

    /**
     * Prune metrics files older than $days.
     */
    public function prune(int $days): void
    {
        if ($days <= 0) return;
        $cutoff = (new \DateTimeImmutable('-' . $days . ' days'))->setTime(0,0);
        foreach (glob($this->dir . '/app-*.ndjson') ?: [] as $file) {
            $base = basename($file);
            $date = substr($base, 4, 10); // YYYY-MM-DD
            try {
                $d = new \DateTimeImmutable($date);
            } catch (\Throwable) { continue; }
            if ($d < $cutoff) { @unlink($file); }
        }
    }

    /**
     * Summarize metrics in [from, to].
     * @return array{requests: array<string,mixed>, errors: array<string,mixed>, backups: array<string,mixed>}
     */
    public function summarize(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $files = $this->filesInRange($from, $to);
        $reqDurations = [];
        $reqTtfb = [];
        $totalReq = 0; $successReq = 0; $errors4 = 0; $errors5 = 0;
        $errorCount = 0; $errorByFp = [];
        $backupTotal = 0; $backupOk = 0; $lastBackupTs = null; $lastBackupFailTs = null; $backupBytes = 0;

        foreach ($files as $file) {
            $fh = @fopen($file, 'r');
            if (!$fh) continue;
            while (!feof($fh)) {
                $line = fgets($fh);
                if (!is_string($line) || trim($line) === '') break;
                $row = json_decode($line, true);
                if (!is_array($row)) continue;
                $ts = isset($row['ts']) ? (string)$row['ts'] : null;
                if ($ts === null) continue;
                $t = null;
                try { $t = new \DateTimeImmutable($ts); } catch (\Throwable) { $t = null; }
                if (!$t || $t < $from || $t > $to) continue;
                $type = (string)($row['type'] ?? '');
                $data = is_array($row['data'] ?? null) ? $row['data'] : [];
                if ($type === 'request') {
                    $totalReq++;
                    $status = (int)($data['status'] ?? 0);
                    if ($status < 400 && $status > 0) $successReq++;
                    if ($status >= 500) $errors5++;
                    elseif ($status >= 400) $errors4++;
                    $dur = (float)($data['duration_ms'] ?? 0.0); if ($dur > 0) $reqDurations[] = $dur;
                    $ttfb = (float)($data['ttfb_ms'] ?? 0.0); if ($ttfb > 0) $reqTtfb[] = $ttfb;
                } elseif ($type === 'error') {
                    $errorCount++;
                    $fp = (string)($data['fingerprint'] ?? ($data['message_hash'] ?? 'unknown'));
                    $errorByFp[$fp] = ($errorByFp[$fp] ?? 0) + 1;
                } elseif ($type === 'backup') {
                    $backupTotal++;
                    $ok = (bool)($data['ok'] ?? false);
                    if ($ok) $backupOk++;
                    $when = $t->getTimestamp();
                    $lastBackupTs = $lastBackupTs !== null ? max($lastBackupTs, $when) : $when;
                    if (!$ok) { $lastBackupFailTs = $lastBackupFailTs !== null ? max($lastBackupFailTs, $when) : $when; }
                    $bytes = (int)($data['bytes'] ?? 0); $backupBytes += $bytes;
                }
            }
            fclose($fh);
        }

        sort($reqDurations);
        sort($reqTtfb);
        $pct = function(array $arr, float $p): float {
            $n = count($arr); if ($n === 0) return 0.0; $rank = ($p/100.0) * ($n - 1); $lo = (int)floor($rank); $hi = (int)ceil($rank); if ($lo === $hi) return (float)$arr[$lo]; $w = $rank - $lo; return (1-$w)*(float)$arr[$lo] + $w*(float)$arr[$hi];
        };

        $requests = [
            'count' => $totalReq,
            'success_rate' => $totalReq > 0 ? ($successReq / $totalReq) : 0.0,
            'errors4xx' => $errors4,
            'errors5xx' => $errors5,
            'duration_ms' => [
                'p50' => $pct($reqDurations, 50),
                'p95' => $pct($reqDurations, 95),
                'p99' => $pct($reqDurations, 99),
            ],
            'ttfb_ms' => [
                'p50' => $pct($reqTtfb, 50),
                'p95' => $pct($reqTtfb, 95),
                'p99' => $pct($reqTtfb, 99),
            ],
        ];

        arsort($errorByFp);
        $errors = [
            'count' => $errorCount,
            'top_fingerprints' => array_slice($errorByFp, 0, 5, true),
        ];

        $backups = [
            'count' => $backupTotal,
            'success_rate' => $backupTotal > 0 ? ($backupOk / $backupTotal) : 0.0,
            'last_run_ts' => $lastBackupTs,
            'last_failure_ts' => $lastBackupFailTs,
            'bytes' => $backupBytes,
        ];

        return [
            'requests' => $requests,
            'errors' => $errors,
            'backups' => $backups,
        ];
    }

    /** @return array<int,string> */
    private function filesInRange(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $files = [];
        $d = $from->setTime(0,0);
        while ($d <= $to) {
            $files[] = $this->dir . '/app-' . $d->format('Y-m-d') . '.ndjson';
            $d = $d->modify('+1 day');
        }
        return array_values(array_filter($files, 'is_file'));
    }

    private function appendLine(string $path, string $line): void
    {
        $fh = @fopen($path, 'ab');
        if (!$fh) return;
        try {
            if (@flock($fh, LOCK_EX)) {
                fwrite($fh, $line);
                fflush($fh);
                @flock($fh, LOCK_UN);
            }
        } finally {
            fclose($fh);
        }
    }
}
