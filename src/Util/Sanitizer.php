<?php

declare(strict_types=1);

namespace App\Util;

final class Sanitizer
{
    public static function string(?string $value): string
    {
        return trim((string)($value ?? ''));
    }

    public static function nullableString(mixed $value): ?string
    {
        $s = trim((string)($value ?? ''));
        return $s === '' ? null : $s;
    }

    public static function int(mixed $value): int
    {
        return (int)($value ?? 0);
    }

    public static function float(mixed $value): float
    {
        return (float)($value ?? 0);
    }

    public static function email(?string $value): string
    {
        return trim((string)($value ?? ''));
    }
}
