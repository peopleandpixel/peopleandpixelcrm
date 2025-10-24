<?php

declare(strict_types=1);

use App\Http\Request;
use App\I18n;
use App\Util\Csrf;
use App\Util\View;
use App\Util\Auth;
use JetBrains\PhpStorm\NoReturn;
use Random\RandomException;
use Twig\Environment;

// Translation helpers
function __(string $key, array $repl = []): string { return I18n::t($key, $repl); }
function n__(string $key, int|float $count, array $repl = []): string { return I18n::plural($key, $count, $repl + ['count' => $count]); }
function format_date(DateTimeInterface $date, int $dateType = \IntlDateFormatter::MEDIUM, int $timeType = \IntlDateFormatter::NONE): string { return I18n::formatDate($date, $dateType, $timeType); }
function format_datetime(DateTimeInterface $dateTime, int $dateType = \IntlDateFormatter::MEDIUM, int $timeType = \IntlDateFormatter::SHORT): string { return I18n::formatDate($dateTime, $dateType, $timeType); }
function format_number(int|float $number, int $style = \NumberFormatter::DECIMAL, int $precision = 2): string { return I18n::formatNumber($number, $style, $precision); }

// Request helper
function request(): Request {
    global $container;
    /** @var Request $req */
    $req = $container->get('request');
    return $req;
}

// CSRF helpers
/**
 * @throws RandomException
 */
function csrf_token(): string { return Csrf::getToken(); }
/**
 * @throws RandomException
 */
function csrf_field(): string {
    $name = Csrf::fieldName();
    $val = htmlspecialchars(Csrf::getToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="' . $name . '" value="' . $val . '">';
}

// Simple helper to render templates via Twig only
function render(string $template, array $params = []): void {
    $twigTemplate = $template . '.twig';
    if (!class_exists(Environment::class)) {
        throw new \RuntimeException('Twig is required for rendering templates.');
    }
    global $container;
    /** @var Environment $twig */
    $twig = $container->get('twig');
    // ensure current language is up-to-date
    $twig->addGlobal('currentLang', I18n::getLang());
    if (!$twig->getLoader()->exists($twigTemplate)) {
        http_response_code(500);
        echo 'Template not found: ' . htmlspecialchars($twigTemplate);
        return;
    }
    echo $twig->render($twigTemplate, $params);
}

#[NoReturn]
function redirect(string $path): void {
    // If absolute URL, redirect as-is
    if (preg_match('#^https?://#i', $path)) {
        header('Location: ' . $path);
        exit;
    }
    // Ensure we respect base path if installed under a subdirectory
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    $final = $path;
    if ($path === '' || $path[0] !== '/') {
        // relative path → build with url()
        $final = url($path);
    } else {
        // absolute path starting with '/': if not already prefixed with base, prefix it
        if ($basePath !== '' && $basePath !== '/' && !str_starts_with($path, $basePath . '/')) {
            $final = $basePath . $path;
        }
    }
    header('Location: ' . $final);
    exit;
}

// URL helpers
function can_url(string $urlOrPath, string $method = 'GET'): bool {
    // Accept absolute URLs and extract path
    $path = $urlOrPath;
    if (preg_match('#^https?://#i', $urlOrPath)) {
        $parts = parse_url($urlOrPath);
        $path = isset($parts['path']) ? $parts['path'] : '/';
    }
    // Normalize to app-relative path (remove base path if present)
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath)) ?: '/';
    }
    // Drop trailing slash (except root)
    if ($path !== '/' && str_ends_with($path, '/')) {
        $path = rtrim($path, '/');
    }
    // Map to entity/action and check permissions
    $map = \App\Util\Permission::mapPathToCheck(strtoupper($method), $path);
    if ($map === null) return true; // not a protected route → visible
    return \App\Util\Permission::can($map[0], $map[1]);
}

function current_path(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    // strip base path if app is installed under a subdirectory
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath)) ?: '/';
    }
    // normalize
    $path = preg_replace('#/+#', '/', $path) ?? $path;
    if ($path !== '/' && str_ends_with($path, '/')) {
        $path = rtrim($path, '/');
    }
    return $path;
}

function url(string $path, array $params = []): string {
    global $container;
    /** @var \App\Http\UrlGenerator $url */
    $url = $container->get('url');
    return $url->url($path, $params);
}

function canonical_url(?string $path = null, array $params = []): string {
    global $container;
    /** @var \App\Http\UrlGenerator $url */
    $url = $container->get('url');
    return $url->canonical($path, $params);
}

function active_class(string $pattern, string $class = 'active'): string {
    $path = current_path();
    // treat pattern as prefix match; allow exact or starts with pattern + '/'
    if ($pattern === '/') {
        return $path === '/' ? $class : '';
    }
    if ($path === $pattern || str_starts_with($path, rtrim($pattern, '/') . '/')) {
        return $class;
    }
    return '';
}

// View helpers
function e(null|string|int|float $value): string { return View::e($value); }
function nl2br_e(?string $value): string { return View::nl2brE($value); }
function sort_link(string $label, string $key, ?string $currentKey, string $currentDir, string $path, array $extraQuery = []): string { return View::sortLink($label, $key, $currentKey, $currentDir, $path, $extraQuery); }
function paginate(int $total, int $page, int $perPage, string $path, array $extraQuery = []): string { return View::paginate($total, $page, $perPage, $path, $extraQuery); }

// Lightweight HTTP caching for list pages (JSON mode only)
function send_list_cache_headers(array $files, int $ttl = 60): void {
    // Skip caching in dev/debug
    $env = strtolower($_ENV['APP_ENV'] ?? 'prod');
    $debug = in_array(strtolower($_ENV['APP_DEBUG'] ?? ''), ['1','true','yes'], true);
    if ($env === 'dev' || $debug) {
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        return;
    }
    $mtimes = [];
    $sizes = [];
    foreach ($files as $f) {
        if (is_string($f) && is_file($f)) {
            $mt = @filemtime($f) ?: 0;
            $sz = @filesize($f) ?: 0;
            $mtimes[] = $mt;
            $sizes[] = $sz;
        }
    }
    $lastMod = $mtimes ? max($mtimes) : time();
    $etag = 'W/"' . sha1(json_encode([$mtimes, $sizes])) . '"';

    header('Cache-Control: public, max-age=' . $ttl);
    header('ETag: ' . $etag);
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastMod) . ' GMT');

    $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
    $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
    $sinceTs = $ifModifiedSince ? strtotime($ifModifiedSince) : false;

    if ($ifNoneMatch === $etag || ($sinceTs !== false && $sinceTs >= $lastMod)) {
        http_response_code(304);
        exit;
    }
}
