<?php

declare(strict_types=1);

namespace App\Util;

use App\Config;

final class RateLimit
{
    /**
     * Simple token/IP based sliding window limiter.
     * Returns true when allowed; false when limited. Stores counters in var/cache/ratelimit.
     */
    public static function allow(Config $config, string $key, int $limit, int $windowSeconds = 60): bool
    {
        $dir = $config->getCacheDir() . '/ratelimit';
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        $file = $dir . '/' . sha1($key) . '.json';
        $now = time();
        $bucket = ['start' => $now, 'count' => 0];
        if (is_file($file)) {
            $raw = @file_get_contents($file);
            $data = $raw ? json_decode($raw, true) : null;
            if (is_array($data) && isset($data['start'], $data['count'])) {
                $bucket = $data;
            }
        }
        if ($bucket['start'] + $windowSeconds <= $now) {
            $bucket = ['start' => $now, 'count' => 0];
        }
        $bucket['count'] = (int)$bucket['count'] + 1;
        @file_put_contents($file, json_encode($bucket));
        return $bucket['count'] <= $limit;
    }
}
