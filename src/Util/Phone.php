<?php

declare(strict_types=1);

namespace App\Util;

/**
 * Lightweight phone helpers for normalization.
 * Note: We intentionally avoid heavy libphonenumber dependencies.
 */
final class Phone
{
    /**
     * Normalize a phone number to a best-effort E.164 form (e.g., +15551234567).
     * - Strips spaces and punctuation.
     * - Converts leading 00 to +.
     * - If no leading +, attempts to prepend country calling code from DEFAULT_COUNTRY env.
     * - Returns cleaned input with digits and optional leading + if unable to confidently normalize.
     */
    public static function normalizeE164(string $raw, ?string $defaultCountry = null): string
    {
        $raw = trim($raw);
        if ($raw === '') { return ''; }
        $n = preg_replace('/[^0-9+]/', '', $raw) ?? '';
        if ($n === '') { return ''; }
        if (str_starts_with($n, '00')) {
            $n = '+' . substr($n, 2);
        }
        if (str_starts_with($n, '+')) {
            // Already has CC, ensure length plausible (8..15 digits total, excluding +)
            $digits = preg_replace('/\D+/', '', $n) ?? '';
            if (strlen($digits) >= 8 && strlen($digits) <= 15) {
                return '+' . $digits;
            }
            return '+' . $digits; // still return compacted
        }
        // No +: try to use default country calling code
        $cc = self::countryCallingCode($defaultCountry ?: self::envDefaultCountry());
        $digits = preg_replace('/\D+/', '', $n) ?? '';
        if ($cc !== null && $digits !== '') {
            $candidate = '+' . $cc . $digits;
            if (strlen($cc . $digits) >= 8 && strlen($cc . $digits) <= 15) {
                return $candidate;
            }
            return $candidate; // return anyway as best-effort
        }
        // Fallback: return cleaned digits (no country code)
        return $digits;
    }

    /** Return true if number looks like E.164 (+ and 8..15 digits). */
    public static function isE164(string $raw): bool
    {
        return (bool)preg_match('/^\+[1-9]\d{7,14}$/', trim($raw));
    }

    /**
     * Resolve environment default country (2-letter ISO), default to 'US'.
     */
    public static function envDefaultCountry(): string
    {
        $v = $_ENV['DEFAULT_COUNTRY'] ?? getenv('DEFAULT_COUNTRY') ?: 'US';
        $v = strtoupper(trim((string)$v));
        if ($v === '') { $v = 'US'; }
        return $v;
    }

    /**
     * Minimal map for common countries.
     * Extend as needed without adding heavy deps.
     */
    private static function countryCallingCode(?string $country): ?string
    {
        if ($country === null) return null;
        $c = strtoupper($country);
        $map = [
            'US' => '1', 'CA' => '1',
            'DE' => '49', 'AT' => '43', 'CH' => '41',
            'GB' => '44', 'IE' => '353',
            'FR' => '33', 'ES' => '34', 'IT' => '39', 'NL' => '31', 'BE' => '32', 'DK' => '45', 'SE' => '46', 'NO' => '47', 'FI' => '358',
            'PL' => '48', 'CZ' => '420', 'SK' => '421', 'HU' => '36',
            'PT' => '351', 'GR' => '30',
            'AU' => '61', 'NZ' => '64',
            'BR' => '55', 'MX' => '52', 'AR' => '54',
            'IN' => '91', 'CN' => '86', 'JP' => '81', 'KR' => '82',
            'ZA' => '27',
        ];
        return $map[$c] ?? null;
    }
}
