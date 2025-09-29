<?php

namespace App\Util;

use Random\RandomException;

class Csrf
{
    private const SESSION_KEY = '_csrf_token';
    private const FIELD_NAME = '_csrf';

    /**
     * @throws RandomException
     */
    public static function getToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function fieldName(): string
    {
        return self::FIELD_NAME;
    }

    /**
     * @throws RandomException
     */
    public static function validate(?string $token): bool
    {
        if ($token === null) {
            return false;
        }
        $expected = self::getToken();
        return hash_equals($expected, $token);
    }
}
