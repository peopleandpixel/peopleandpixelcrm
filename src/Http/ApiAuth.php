<?php

declare(strict_types=1);

namespace App\Http;

use App\Config;

final class ApiAuth
{
    /**
     * Validate Bearer token from Authorization header or `token` query.
     * Returns true if valid, otherwise sends 401 and returns false.
     */
    public static function enforceToken(Config $config): bool
    {
        $provided = self::getProvidedToken();
        $valids = self::getAllowedTokens($config);
        if ($provided === null || empty($valids) || !in_array($provided, $valids, true)) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Unauthorized', 'code' => 401]);
            return false;
        }
        // basic rate limiting
        if (!self::rateLimit($config, $provided)) {
            http_response_code(429);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Too Many Requests', 'code' => 429]);
            return false;
        }
        return true;
    }

    private static function getProvidedToken(): ?string
    {
        $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? '';
        if (is_string($hdr) && preg_match('/^Bearer\s+(.+)$/i', trim($hdr), $m)) {
            return trim($m[1]);
        }
        if (isset($_GET['token']) && is_string($_GET['token']) && $_GET['token'] !== '') {
            return (string)$_GET['token'];
        }
        return null;
    }

    /** @return array<int,string> */
    private static function getAllowedTokens(Config $config): array
    {
        $env = $config->getEnv('API_TOKEN');
        if ($env === '') return [];
        $parts = array_map('trim', explode(',', $env));
        return array_values(array_filter($parts, fn($v) => $v !== ''));
    }

    /** Simple per-token per-IP rate limit: max 60 requests per minute. */
    private static function rateLimit(Config $config, string $token): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = sha1($token . '|' . $ip);
        $dir = $config->getCacheDir() . '/ratelimit';
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        $file = $dir . '/' . $key . '.json';
        $now = time();
        $window = 60; $limit = 60; // 60/min
        $bucket = ['start' => $now, 'count' => 0];
        if (is_file($file)) {
            $raw = @file_get_contents($file);
            $data = $raw ? json_decode($raw, true) : null;
            if (is_array($data) && isset($data['start'], $data['count'])) {
                $bucket = $data;
            }
        }
        if ($bucket['start'] + $window <= $now) {
            $bucket = ['start' => $now, 'count' => 0];
        }
        $bucket['count'] = (int)$bucket['count'] + 1;
        @file_put_contents($file, json_encode($bucket));
        return $bucket['count'] <= $limit;
    }
}
