<?php

namespace App;

use IntlDateFormatter;
use NumberFormatter;

class I18n
{
    private static string $lang = 'en';
    private static string $fallbackLang = 'en';
    private static array $messages = [];
    private static array $fallbackMessages = [];

    public static function supported(): array
    {
        return ['en', 'de', 'pt'];
    }

    public static function init(?string $lang): void
    {
        $lang = self::normalizeLang($lang) ?? self::detectFromEnv() ?? self::$fallbackLang;
        self::$lang = $lang;
        // Load selected language
        self::$messages = self::loadMessages($lang);
        // Load fallback messages (en)
        self::$fallbackMessages = $lang === self::$fallbackLang ? self::$messages : self::loadMessages(self::$fallbackLang);
    }

    private static function loadMessages(string $lang): array
    {
        // Cache per request to avoid re-loading and parsing language files
        return \App\Util\RequestCache::remember('i18n.messages.' . $lang, function() use ($lang) {
            $file = __DIR__ . '/../lang/' . $lang . '.php';
            if (is_file($file)) {
                /** @var array $msgs */
                $msgs = include $file;
                if (is_array($msgs)) {
                    return $msgs;
                }
            }
            return [];
        });
    }

    private static function normalizeLang(?string $lang): ?string
    {
        if (!$lang) return null;
        $l = strtolower(trim($lang));
        // reduce to primary subtag like en-US -> en
        if (str_contains($l, '-')) {
            $l = explode('-', $l, 2)[0];
        }
        if (str_contains($l, '_')) {
            $l = explode('_', $l, 2)[0];
        }
        return in_array($l, self::supported(), true) ? $l : null;
    }

    private static function detectFromEnv(): ?string
    {
        // 1) Session
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['lang'])) {
            $s = self::normalizeLang((string)$_SESSION['lang']);
            if ($s) return $s;
        }
        // 2) Cookie
        if (isset($_COOKIE['lang'])) {
            $c = self::normalizeLang((string)$_COOKIE['lang']);
            if ($c) return $c;
        }
        // 3) Accept-Language header
        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if ($header) {
            $parts = explode(',', $header);
            foreach ($parts as $part) {
                $code = trim(explode(';', $part, 2)[0]);
                $n = self::normalizeLang($code);
                if ($n) return $n;
            }
        }
        return null;
    }

    public static function t(string $key, array $replacements = []): string
    {
        $text = self::$messages[$key] ?? self::$fallbackMessages[$key] ?? $key;
        if ($replacements) {
            foreach ($replacements as $k => $v) {
                $text = str_replace('{' . $k . '}', (string)$v, $text);
            }
        }
        return $text;
    }

    /**
     * ICU-style pluralization with simple rules: zero, one, other.
     * Pattern example: "{count, plural, one {# item} other {# items}}"
     */
    public static function plural(string $key, int|float $count, array $replacements = []): string
    {
        $pattern = self::t($key, $replacements);
        // If key not found, pattern equals key; provide a generic fallback
        if ($pattern === $key) {
            // fallback: append 's' for plural in English-like manner
            return (int)$count === 1 ? (string)($replacements['one'] ?? $key) : (string)($replacements['other'] ?? ($key . 's'));
        }
        // Very small plural parser
        $map = [
            'zero' => null,
            'one' => null,
            'other' => null,
        ];
        if (preg_match_all('/(zero|one|other)\s*\{([^}]*)\}/', $pattern, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $map[$match[1]] = $match[2];
            }
        } else {
            // No plural sections; just replace # with count
            return str_replace('#', (string)$count, $pattern);
        }
        $form = ((int)$count === 0 && $map['zero'] !== null) ? 'zero' : (((int)$count === 1) ? 'one' : 'other');
        $text = $map[$form] ?? $map['other'] ?? '';
        $text = str_replace('#', (string)$count, $text);
        foreach ($replacements as $k => $v) {
            $text = str_replace('{' . $k . '}', (string)$v, $text);
        }
        return $text ?: (string)$count;
    }

    public static function formatDate(\DateTimeInterface $date, int $dateType = 2, int $timeType = 0): string
    {
        $locale = self::$lang;
        if (class_exists(IntlDateFormatter::class)) {
            $fmt = new IntlDateFormatter($locale, $dateType, $timeType);
            return $fmt->format($date) ?: $date->format('Y-m-d');
        }
        return $date->format('Y-m-d');
    }

    public static function formatNumber(int|float $number, int $style = 1, int $precision = 2): string
    {
        $locale = self::$lang;
        if (class_exists(NumberFormatter::class)) {
            $fmt = new NumberFormatter($locale, $style);
            // 1 corresponds to NumberFormatter::DECIMAL
            if (defined('NumberFormatter::DECIMAL')) {
                $decimalConst = \NumberFormatter::DECIMAL;
            } else {
                $decimalConst = 1;
            }
            if ($style === $decimalConst) {
                $fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $precision);
            }
            $res = $fmt->format($number);
            if ($res !== false) return $res;
        }
        // Fallback
        return number_format((float)$number, $precision);
    }

    public static function getLang(): string
    {
        return self::$lang;
    }
}
