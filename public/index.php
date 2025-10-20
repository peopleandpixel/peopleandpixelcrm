<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\I18n;
use App\Container;
use App\Router;
use App\Controller\HomeController;
use App\Controller\ContactsTemplateController;
use App\Controller\TimesController;
use App\Controller\TasksController;
use App\Controller\EmployeesTemplateController;
use App\Controller\CandidatesTemplateController;
use App\Controller\PaymentsController;
use App\Controller\StorageController;
use App\Controller\InstallerController;
use App\Controller\ExportController;
use App\Controller\ImportController;
use App\Util\Csrf;
use App\Util\Auth;
use App\Util\Permission;
use App\Util\ErrorHandler;
use App\Util\View;
use JetBrains\PhpStorm\NoReturn;
use Random\RandomException;
use Twig\Environment;

// Load environment from .env using vlucas/phpdotenv if available
$projectRoot = dirname(__DIR__);
if (class_exists(\Dotenv\Dotenv::class)) {
    $dotenv = \Dotenv\Dotenv::createImmutable($projectRoot);
    // safeLoad won't throw if file is missing, keeping defaults
    $dotenv->safeLoad();
}

// Start session and language handling
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$container = new Container();
$config = $container->get('config');

// Register global error & exception handlers early
/** @var ErrorHandler $errorHandler */
$errorHandler = $container->get('errorHandler');
$errorHandler->register();

if (isset($_GET['lang'])) {
    $langParam = strtolower((string)$_GET['lang']);
    // validate against supported languages
    if (in_array($langParam, I18n::supported(), true)) {
        $_SESSION['lang'] = $langParam;
        // persist in cookie for 1 year
        setcookie('lang', $langParam, [
            'expires' => time() + 31536000,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        $config->setLang($langParam);
    }
}
I18n::init($config->getLang());
if (!function_exists('__')) {
    function __(string $key, array $repl = []): string { return I18n::t($key, $repl); }
}
if (!function_exists('n__')) {
    function n__(string $key, int|float $count, array $repl = []): string { return I18n::plural($key, $count, $repl + ['count' => $count]); }
}
if (!function_exists('format_date')) {
    function format_date(DateTimeInterface $date, int $dateType = \IntlDateFormatter::MEDIUM, int $timeType = \IntlDateFormatter::NONE): string { return I18n::formatDate($date, $dateType, $timeType); }
}
if (!function_exists('format_datetime')) {
    function format_datetime(DateTimeInterface $dateTime, int $dateType = \IntlDateFormatter::MEDIUM, int $timeType = \IntlDateFormatter::SHORT): string { return I18n::formatDate($dateTime, $dateType, $timeType); }
}
if (!function_exists('format_number')) {
    function format_number(int|float $number, int $style = \NumberFormatter::DECIMAL, int $precision = 2): string { return I18n::formatNumber($number, $style, $precision); }
}

// Request helper
if (!function_exists('request')) {
    function request(): \App\Http\Request {
        global $container;
        /** @var \App\Http\Request $req */
        $req = $container->get('request');
        return $req;
    }
}

// CSRF helpers
if (!function_exists('csrf_token')) {
    /**
     * @throws RandomException
     */
    function csrf_token(): string { return Csrf::getToken(); }
}
if (!function_exists('csrf_field')) {
    /**
     * @throws RandomException
     */
    function csrf_field(): string {
        $name = Csrf::fieldName();
        $val = htmlspecialchars(Csrf::getToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="' . $name . '" value="' . $val . '">';
    }
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
if (!function_exists('e')) {
    function e(null|string|int|float $value): string { return View::e($value); }
}
if (!function_exists('nl2br_e')) {
    function nl2br_e(?string $value): string { return View::nl2brE($value); }
}
if (!function_exists('sort_link')) {
    function sort_link(string $label, string $key, ?string $currentKey, string $currentDir, string $path, array $extraQuery = []): string {
        return View::sortLink($label, $key, $currentKey, $currentDir, $path, $extraQuery);
    }
}
if (!function_exists('paginate')) {
    function paginate(int $total, int $page, int $perPage, string $path, array $extraQuery = []): string {
        return View::paginate($total, $page, $perPage, $path, $extraQuery);
    }
}

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

    // Use standard precedence: ETag (If-None-Match) takes priority; fall back to Last-Modified only if no ETag provided
    if ($ifNoneMatch !== '') {
        if (trim($ifNoneMatch) === $etag) {
            http_response_code(304);
            exit;
        }
    } elseif ($sinceTs !== false && $sinceTs >= $lastMod) {
        http_response_code(304);
        exit;
    }
}

/** @var Router $router */
$router = $container->get('router');

// First-run installer redirect
$installed = isset($_ENV['INSTALLED']) && ($_ENV['INSTALLED'] === '1');
$pathNow = current_path();
if (!$installed && $pathNow !== '/install') {
    redirect('/install');
}

// Load routes from dedicated file
$routesRegistrar = require dirname(__DIR__) . '/src/Http/routes.php';
if (is_callable($routesRegistrar)) {
    $routesRegistrar($container, $router);
}

// Register global middlewares for auth and CSRF guards
$router->use(function(string $method, string $path, callable $next) {
    // Non-POST auth guard: require login except for installer and login page
    if ($method !== 'POST') {
        if (!in_array($path, ['/install', '/login'], true) && !Auth::check()) {
            $return = $path;
            $query = $_SERVER['QUERY_STRING'] ?? '';
            if ($query !== '') {
                $return .= '?' . $query;
            }
            $q = http_build_query(['return' => $return]);
            redirect('/login?' . $q);
            return; // short-circuit
        }
    }
    // Fine-grained permission checks for non-POST too
    if ($method !== 'POST') {
        if (!Permission::enforce($method, $path)) {
            return;
        }
    }
    $next();
});

$router->use(function(string $method, string $path, callable $next) {
    // CSRF and auth guard for POST requests
    if ($method === 'POST') {
        $field = Csrf::fieldName();
        $incoming = $_POST[$field] ?? null;
        $token = is_string($incoming) ? $incoming : null;
        // Also accept token from X-CSRF-Token header (for AJAX uploads, etc.)
        if (!$token) {
            $hdr = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            $token = is_string($hdr) ? $hdr : null;
        }
        if (!Csrf::validate($token)) {
            http_response_code(403);
            render('errors/403');
            return;
        }
        if (!in_array($path, ['/install', '/login'], true) && !Auth::check()) {
            $return = isset($_POST['return']) ? (string)$_POST['return'] : $path;
            $q = http_build_query(['return' => $return]);
            redirect('/login?' . $q);
            return;
        }
        // Fine-grained permission checks for POST routes
        if (!Permission::enforce($method, $path)) {
            return;
        }
    }
    $next();
});

// Finally, dispatch the request
$router->dispatch();
