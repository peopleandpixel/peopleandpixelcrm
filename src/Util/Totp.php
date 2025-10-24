<?php

declare(strict_types=1);

namespace App\Util;

final class Totp
{
    /** Generate the current TOTP for a given base32 secret. */
    public static function currentCode(string $base32Secret, int $period = 30, int $digits = 6, string $algo = 'sha1'): string
    {
        $timeStep = (int)floor(time() / $period);
        return self::hotp(self::base32Decode($base32Secret), $timeStep, $digits, $algo);
    }

    /** Verify a provided code within a window of +/- $window steps. */
    public static function verify(string $base32Secret, string $code, int $period = 30, int $digits = 6, int $window = 1, string $algo = 'sha1'): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if ($code === null) $code = '';
        if ($code === '') return false;
        $secret = self::base32Decode($base32Secret);
        if ($secret === '') return false;
        $timeStep = (int)floor(time() / $period);
        for ($i = -$window; $i <= $window; $i++) {
            $calc = self::hotp($secret, $timeStep + $i, $digits, $algo);
            if (hash_equals($calc, $code)) return true;
        }
        return false;
    }

    private static function hotp(string $secret, int $counter, int $digits, string $algo): string
    {
        $binCounter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac($algo, $binCounter, $secret, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $part = substr($hash, $offset, 4);
        $value = unpack('N', $part)[1] & 0x7FFFFFFF;
        $mod = 10 ** $digits;
        $code = (string)($value % $mod);
        return str_pad($code, $digits, '0', STR_PAD_LEFT);
    }

    /** Decode a base32-encoded string (RFC 4648, no padding required). */
    private static function base32Decode(string $b32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $b32 = strtoupper(preg_replace('/[^A-Z2-7]/', '', $b32) ?? '');
        if ($b32 === '') return '';
        $bits = '';
        for ($i = 0, $len = strlen($b32); $i < $len; $i++) {
            $val = strpos($alphabet, $b32[$i]);
            if ($val === false) continue;
            $bits .= str_pad(decbin($val), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        for ($i = 0; $i + 8 <= strlen($bits); $i += 8) {
            $out .= chr(bindec(substr($bits, $i, 8)));
        }
        return $out;
    }
}
