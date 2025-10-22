<?php

declare(strict_types=1);

namespace App\Util;

final class Sanitizer
{
    /**
     * Normalize a string for storage: trim, strip tags, remove control chars.
     */
    public static function string(?string $value): string
    {
        $s = (string)($value ?? '');
        $s = strip_tags($s);
        // Remove NULL bytes and control characters except common whitespace (tab, newline, carriage return)
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $s) ?? $s;
        return trim($s);
    }

    public static function nullableString(mixed $value): ?string
    {
        $s = self::string(is_string($value) ? $value : (string)($value ?? ''));
        return $s === '' ? null : $s;
    }

    public static function int(mixed $value): int
    {
        return max(0, (int)($value ?? 0));
    }

    public static function float(mixed $value): float
    {
        return (float)($value ?? 0);
    }

    public static function email(?string $value): string
    {
        $s = self::string($value);
        return mb_strtolower($s);
    }

    /**
     * Escape a string for safe HTML output (rarely needed when Twig autoescape is enabled).
     */
    public static function escapeHtml(?string $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
