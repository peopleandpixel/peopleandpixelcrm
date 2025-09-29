<?php

declare(strict_types=1);

namespace App\Util;

use DateTimeImmutable;

final class Dates
{
    /**
     * Strictly parse a date/time string using DateTimeImmutable and exact format.
     * Returns DateTimeImmutable on success or null on failure.
     */
    public static function parseExact(string $value, string $format): ?DateTimeImmutable
    {
        if ($value === '') return null;
        $dt = DateTimeImmutable::createFromFormat('!' . $format, $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$dt || !$errors || $errors['warning_count'] > 0 || $errors['error_count'] > 0) {
            return null;
        }
        return $dt;
    }

    /**
     * Validate if a value matches a given date format exactly.
     */
    public static function isValid(string $value, string $format): bool
    {
        return self::parseExact($value, $format) instanceof DateTimeImmutable;
    }

    /**
     * Convert a value in known format into ISO date (Y-m-d). If invalid, returns null.
     */
    public static function toIsoDate(string $value, string $fromFormat = 'Y-m-d'): ?string
    {
        $dt = self::parseExact($value, $fromFormat);
        return $dt?->format('Y-m-d');
    }

    /**
     * Current timestamp in RFC3339 (ATOM) format using DateTimeImmutable.
     */
    public static function nowAtom(): string
    {
        return new DateTimeImmutable('now')->format(DATE_ATOM);
    }
}
