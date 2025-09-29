<?php

declare(strict_types=1);

namespace App\Util;

/**
 * Minimal flash messaging helper stored in session.
 * Types: success, error, info
 */
class Flash
{
    private const SESSION_KEY = '_flashes';

    private static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
    }

    /**
     * Add a flash message of a given type
     */
    public static function add(string $type, string $message): void
    {
        self::ensureSession();
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
        $_SESSION[self::SESSION_KEY][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public static function success(string $message): void { self::add('success', $message); }
    public static function error(string $message): void { self::add('error', $message); }
    public static function info(string $message): void { self::add('info', $message); }

    /**
     * Get all current flashes without clearing them.
     * @return array<int, array{type:string,message:string}>
     */
    public static function getAll(): array
    {
        self::ensureSession();
        $list = $_SESSION[self::SESSION_KEY] ?? [];
        return is_array($list) ? $list : [];
    }

    /**
     * Consume and clear all flashes (single use).
     * @return array<int, array{type:string,message:string}>
     */
    public static function consumeAll(): array
    {
        self::ensureSession();
        $list = self::getAll();
        unset($_SESSION[self::SESSION_KEY]);
        return $list;
    }
}
