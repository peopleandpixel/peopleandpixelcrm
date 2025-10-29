<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config;
use App\Util\Auth;
use App\Util\Flash;

final class AdminController
{
    public function __construct(private readonly Config $config, private readonly ?\App\Service\MetricsService $metrics = null)
    {
    }

    public function health(): void
    {
        if (!Auth::isAdmin()) { http_response_code(403); render('errors/403'); return; }

        $env = [
            'app_env' => $this->config->getEnv('APP_ENV') ?: 'prod',
            'app_debug' => (bool)($this->config->getEnv('APP_DEBUG') ?: false),
            'php_version' => PHP_VERSION,
            'timezone' => date_default_timezone_get(),
            'locale' => \Locale::getDefault(),
        ];

        $dirs = [
            'data' => $this->config->getDataDir(),
            'var' => $this->config->getVarDir(),
            'logs' => rtrim($this->config->getVarDir(), '/').'/log',
            'cache' => rtrim($this->config->getVarDir(), '/').'/cache',
        ];

        $storage = [];
        foreach ($dirs as $key => $dir) {
            $dir = (string)$dir;
            $exists = is_dir($dir);
            $size = $exists ? $this->dirSize($dir) : 0;
            $free = @disk_free_space($exists ? $dir : dirname($dir));
            $free = $free === false ? null : (int)$free;
            $storage[$key] = [
                'path' => $dir,
                'exists' => $exists,
                'size' => $size,
                'free' => $free,
                'writable' => $exists ? is_writable($dir) : is_writable(dirname($dir)),
            ];
        }

        $logsSummary = $this->summarizeLogs($dirs['logs']);

        // Metrics summaries (if service enabled)
        $metricsSummary = null;
        if ($this->metrics) {
            try {
                $now = new \DateTimeImmutable('now');
                $h1 = $this->metrics->summarize($now->modify('-1 hour'), $now);
                $d1 = $this->metrics->summarize($now->modify('-24 hours'), $now);
                $w1 = $this->metrics->summarize($now->modify('-7 days'), $now);
                $metricsSummary = [ 'last1h' => $h1, 'last24h' => $d1, 'last7d' => $w1 ];
            } catch (\Throwable) { $metricsSummary = null; }
        }

        render('admin/health', [
            'title' => __('Health'),
            'env' => $env,
            'storage' => $storage,
            'logs' => $logsSummary,
            'metrics' => $metricsSummary,
        ]);
    }

    /** @return array<string,int> */
    private function summarizeLogs(string $logDir): array
    {
        $levels = ['DEBUG','INFO','NOTICE','WARNING','ERROR','CRITICAL','ALERT','EMERGENCY'];
        $counts = array_fill_keys($levels, 0);
        if (!is_dir($logDir)) return $counts;
        $files = glob($logDir.'/*.log') ?: [];
        $files = array_slice(array_values($files), -10); // check a few recent logs
        foreach ($files as $file) {
            $fh = @fopen($file, 'r');
            if (!$fh) continue;
            while (!feof($fh)) {
                $line = fgets($fh);
                if (!is_string($line)) break;
                foreach ($levels as $lvl) {
                    if (str_contains($line, $lvl)) { $counts[$lvl]++; break; }
                }
            }
            fclose($fh);
        }
        return $counts;
    }

    private function dirSize(string $dir): int
    {
        if (!is_dir($dir)) return 0;
        $size = 0;
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) { $size += $file->getSize(); }
        return $size;
    }

    public function logsList(): void
    {
        if (!Auth::isAdmin()) { http_response_code(403); render('errors/403'); return; }
        $logDir = rtrim($this->config->getVarDir(), '/').'/log';
        $files = [];
        if (is_dir($logDir)) {
            foreach (glob($logDir.'/*.log') ?: [] as $path) {
                $files[] = [
                    'file' => basename($path),
                    'size' => filesize($path) ?: 0,
                    'mtime' => filemtime($path) ?: 0,
                ];
            }
            usort($files, fn($a,$b) => $b['mtime'] <=> $a['mtime']);
        }
        render('admin/logs', [
            'title' => __('Logs'),
            'files' => $files,
        ]);
    }

    public function logsDownload(): void
    {
        if (!Auth::isAdmin()) { http_response_code(403); render('errors/403'); return; }
        $file = (string)($_GET['file'] ?? '');
        $logDir = rtrim($this->config->getVarDir(), '/').'/log';
        if ($file === '' || !preg_match('/^[A-Za-z0-9._\-]+$/', $file)) { http_response_code(400); render('errors/400'); return; }
        $path = $logDir . '/' . $file;
        if (!is_file($path)) { http_response_code(404); render('errors/404', ['path' => '/admin/logs/download']); return; }
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        readfile($path);
    }
}
