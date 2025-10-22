<?php

declare(strict_types=1);

namespace App\Util;

use App\Config;

class Auth
{
    private const SESSION_USER_KEY = '_auth_user';
    private const THROTTLE_FILE = 'auth_throttle.json';

    /**
     * Ensure session started
     */
    private static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Read throttle data from var/cache.
     * @return array<string, array{fails:int,last:int,locked_until:int}>
     */
    private static function readThrottle(Config $config): array
    {
        $path = $config->getVarDir() . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . self::THROTTLE_FILE;
        if (!is_file($path)) return [];
        $json = @file_get_contents($path);
        $data = $json ? json_decode($json, true) : [];
        return is_array($data) ? $data : [];
    }

    /**
     * Write throttle data.
     * @param array<string, array{fails:int,last:int,locked_until:int}> $data
     */
    private static function writeThrottle(Config $config, array $data): void
    {
        $dir = $config->getVarDir() . DIRECTORY_SEPARATOR . 'cache';
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        $path = $dir . DIRECTORY_SEPARATOR . self::THROTTLE_FILE;
        @file_put_contents($path, json_encode($data));
    }

    private static function throttleKey(Config $config, string $username): string
    {
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        return strtolower($username) . '|' . $ip;
    }

    /**
     * Attempt login with given username/password.
     * Returns true on success.
     */
    public static function login(Config $config, string $username, string $password): bool
    {
        self::ensureSession();
        $username = trim($username);
        $password = (string)$password;

        // Throttling: block if locked
        $throttle = self::readThrottle($config);
        $key = self::throttleKey($config, $username);
        $now = time();
        $rec = $throttle[$key] ?? ['fails' => 0, 'last' => 0, 'locked_until' => 0];
        if (($rec['locked_until'] ?? 0) > $now) {
            return false; // temporarily locked
        }

        // First try users.json (or DB via JsonStore/DbStore API), but here read JSON directly for Auth.
        $usersPath = $config->jsonPath('users.json');
        $users = [];
        if (is_file($usersPath)) {
            $json = @file_get_contents($usersPath);
            $users = $json ? json_decode($json, true) : [];
            if (!is_array($users)) $users = [];
        }

        $success = false;
        foreach ($users as $u) {
            $login = (string)($u['login'] ?? '');
            if ($login === $username) {
                $hash = (string)($u['password_hash'] ?? '');
                if ($hash !== '' && password_verify($password, $hash)) {
                    $_SESSION[self::SESSION_USER_KEY] = [
                        'id' => (int)($u['id'] ?? 0),
                        'username' => $login,
                        'fullname' => (string)($u['fullname'] ?? ''),
                        'email' => (string)($u['email'] ?? ''),
                        'role' => (string)($u['role'] ?? 'user'),
                        'permissions' => is_array($u['permissions'] ?? null) ? $u['permissions'] : [],
                        'must_change_password' => (int)($u['must_change_password'] ?? 0) === 1,
                    ];
                    if (function_exists('session_regenerate_id')) {@session_regenerate_id(true);}                    
                    $success = true;
                }
                // If password does not verify, stop search
                break;
            }
        }

        if (!$success) {
            // Fallback to env-defined admin (bootstrap) and optional viewer
            $adminUser = $config->getEnv('ADMIN_USER') ?: 'admin';
            $adminPass = $config->getEnv('ADMIN_PASS') ?: 'admin';
            if ($username === $adminUser && hash_equals((string)$adminPass, $password)) {
                $_SESSION[self::SESSION_USER_KEY] = [
                    'username' => $username,
                    'role' => 'admin',
                    'permissions' => [],
                ];
                if (function_exists('session_regenerate_id')) {@session_regenerate_id(true);}            
                $success = true;
            } else {
                $viewerUser = $config->getEnv('VIEWER_USER');
                $viewerPass = $config->getEnv('VIEWER_PASS');
                if ($viewerUser && $username === $viewerUser && hash_equals((string)$viewerPass, $password)) {
                    $_SESSION[self::SESSION_USER_KEY] = [
                        'username' => $username,
                        'role' => 'viewer',
                        'permissions' => [],
                    ];
                    if (function_exists('session_regenerate_id')) {@session_regenerate_id(true);}            
                    $success = true;
                }
            }
        }

        if ($success) {
            // Reset throttle record
            unset($throttle[$key]);
            self::writeThrottle($config, $throttle);
            return true;
        }

        // Failure: increment and possibly lock
        $rec['fails'] = (int)($rec['fails'] ?? 0) + 1;
        $rec['last'] = $now;
        // Lock durations: after 5 fails → 1 min, after 7 → 5 min, after 10 → 15 min
        if ($rec['fails'] >= 10) {
            $rec['locked_until'] = $now + 15 * 60;
        } elseif ($rec['fails'] >= 7) {
            $rec['locked_until'] = $now + 5 * 60;
        } elseif ($rec['fails'] >= 5) {
            $rec['locked_until'] = $now + 60;
        }
        $throttle[$key] = $rec;
        self::writeThrottle($config, $throttle);
        return false;
    }

    public static function logout(): void
    {
        self::ensureSession();
        unset($_SESSION[self::SESSION_USER_KEY]);
        // Regenerate session id on logout to mitigate fixation
        if (function_exists('session_regenerate_id')) {@session_regenerate_id(true);}        
    }

    public static function user(): ?array
    {
        self::ensureSession();
        $u = $_SESSION[self::SESSION_USER_KEY] ?? null;
        return is_array($u) ? $u : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u && ($u['role'] ?? null) === 'admin';
    }
}
