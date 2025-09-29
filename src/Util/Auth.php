<?php

declare(strict_types=1);

namespace App\Util;

use App\Config;

class Auth
{
    private const SESSION_USER_KEY = '_auth_user';

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
     * Attempt login with given username/password.
     * Returns true on success.
     */
    public static function login(Config $config, string $username, string $password): bool
    {
        self::ensureSession();
        $username = trim($username);
        $password = (string)$password;

        // First try users.json (or DB via JsonStore/DbStore API), but here read JSON directly for Auth.
        $usersPath = $config->jsonPath('users.json');
        $users = [];
        if (is_file($usersPath)) {
            $json = @file_get_contents($usersPath);
            $users = $json ? json_decode($json, true) : [];
            if (!is_array($users)) $users = [];
        }

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
                    ];
                    if (function_exists('session_regenerate_id')) {@session_regenerate_id(true);}                    
                    return true;
                }
                // If password does not verify, stop search
                break;
            }
        }

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
            return true;
        }
        $viewerUser = $config->getEnv('VIEWER_USER');
        $viewerPass = $config->getEnv('VIEWER_PASS');
        if ($viewerUser && $username === $viewerUser && hash_equals((string)$viewerPass, $password)) {
            $_SESSION[self::SESSION_USER_KEY] = [
                'username' => $username,
                'role' => 'viewer',
                'permissions' => [],
            ];
            if (function_exists('session_regenerate_id')) {@session_regenerate_id(true);}            
            return true;
        }

        return false;
    }

    public static function logout(): void
    {
        self::ensureSession();
        unset($_SESSION[self::SESSION_USER_KEY]);
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
