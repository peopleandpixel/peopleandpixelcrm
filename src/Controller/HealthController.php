<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config;

final class HealthController
{
    public function __construct(private readonly Config $config) {}

    public function json(): void
    {
        $cfg = $this->config;
        $now = date('c');
        $dataDir = $cfg->getDataDir();
        $logDir = $cfg->getLogDir();
        $cacheDir = $cfg->getCacheDir();
        $checks = [
            'dataDirExists' => is_dir($dataDir),
            'dataDirWritable' => is_writable($dataDir),
            'logDirWritable' => (!is_dir($logDir) ? @mkdir($logDir, 0777, true) || is_dir($logDir) : true) && is_writable($logDir),
            'cacheDirWritable' => (!is_dir($cacheDir) ? @mkdir($cacheDir, 0777, true) || is_dir($cacheDir) : true) && is_writable($cacheDir),
        ];
        $ok = !in_array(false, $checks, true);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => $ok,
            'time' => $now,
            'env' => $cfg->getAppEnv(),
            'debug' => $cfg->isDebug(),
            'checks' => $checks,
        ], JSON_UNESCAPED_UNICODE);
    }
}
