<?php

namespace App\Util;

/**
 * Simple per-request in-memory cache helper.
 * Not persistent between requests; only lives within the PHP process lifecycle.
 */
class RequestCache
{
    /**
     * @var array<string, mixed>
     */
    private static array $store = [];

    /**
     * Retrieve a cached value or null if not set.
     * @return mixed|null
     */
    public static function get(string $key)
    {
        return self::$store[$key] ?? null;
    }

    /**
     * Store a value in the cache and return it.
     * @param mixed $value
     * @return mixed
     */
    public static function set(string $key, $value)
    {
        self::$store[$key] = $value;
        return $value;
    }

    /**
     * Remember pattern: returns cached value or computes via $producer and caches it.
     * @template T
     * @param callable():mixed $producer
     * @return mixed
     */
    public static function remember(string $key, callable $producer)
    {
        if (array_key_exists($key, self::$store)) {
            return self::$store[$key];
        }
        $val = $producer();
        self::$store[$key] = $val;
        return $val;
    }

    /**
     * Clear the cache (for tests).
     */
    public static function clear(): void
    {
        self::$store = [];
    }
}
