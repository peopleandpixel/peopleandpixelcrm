<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Lightweight immutable Request wrapper around PHP superglobals.
 */
class Request
{
    private string $method;
    private string $uri;
    private string $path;
    /** @var array<string, mixed> */
    private array $query;
    /** @var array<string, mixed> */
    private array $body;
    /** @var array<string, string> */
    private array $cookies;
    /** @var array<string, mixed> */
    private array $files;
    /** @var array<string, string> */
    private array $server;
    /** @var array<string, string> */
    private array $headers;
    /** @var mixed|null Parsed JSON cache */
    private mixed $parsedJson = null;
    private bool $jsonParsed = false;

    /** @param array<string, mixed> $query
     *  @param array<string, mixed> $body
     *  @param array<string, string> $cookies
     *  @param array<string, mixed> $files
     *  @param array<string, string> $server
     *  @param array<string, string> $headers
     */
    public function __construct(string $method, string $uri, string $path, array $query, array $body, array $cookies, array $files, array $server, array $headers)
    {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->path = $path;
        $this->query = $query;
        $this->body = $body;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;
        $this->headers = $headers;
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        // Strip base path like Router/current_path()
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }
        $path = preg_replace('#/+#', '/', $path) ?? $path;
        if ($path !== '/' && str_ends_with($path, '/')) { $path = rtrim($path, '/'); }
        $headers = function_exists('getallheaders') ? (array) getallheaders() : [];
        // normalize header keys
        $norm = [];
        foreach ($headers as $k => $v) {
            $norm[strtolower((string)$k)] = (string)$v;
        }
        return new self($method, $uri, $path, $_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, $norm);
    }

    public function method(): string { return $this->method; }
    public function uri(): string { return $this->uri; }
    public function path(): string { return $this->path; }

    /** @return array<string, mixed> */
    public function query(): array { return $this->query; }
    /** @return array<string, mixed> */
    public function body(): array { return $this->body; }
    /** @return array<string, string> */
    public function headers(): array { return $this->headers; }
    /** @return array<string, string> */
    public function cookies(): array { return $this->cookies; }
    /** @return array<string, string> */
    public function server(): array { return $this->server; }
    /** @return array<string, mixed> */
    public function files(): array { return $this->files; }

    public function header(string $name, ?string $default = null): ?string
    {
        $key = strtolower($name);
        return $this->headers[$key] ?? $default;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $this->body[$key] ?? $default;
    }

    public function cookie(string $key, ?string $default = null): ?string
    {
        $val = $this->cookies[$key] ?? null;
        return $val === null ? $default : (string)$val;
    }

    /** @return array|string|mixed|null */
    public function file(string $key, mixed $default = null): mixed
    {
        return $this->files[$key] ?? $default;
    }

    public function isAjax(): bool
    {
        $xreq = $this->header('x-requested-with');
        return $xreq !== null && strtolower($xreq) === 'xmlhttprequest';
    }

    public function wantsJson(): bool
    {
        $accept = strtolower($this->header('accept', '') ?? '');
        return str_contains($accept, 'application/json') || $this->isAjax();
    }

    public function isSecure(): bool
    {
        return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] === 'on')
            || (($this->server['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    }

    /**
     * Parse and return JSON request body if Content-Type is application/json.
     * @return array<string,mixed>|list<mixed>|object|null
     */
    public function json(): array|object|null
    {
        if (!$this->jsonParsed) {
            $this->jsonParsed = true;
            $ct = strtolower($this->header('content-type', '') ?? '');
            if (str_starts_with($ct, 'application/json')) {
                $raw = file_get_contents('php://input');
                $decoded = json_decode($raw ?: '', true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->parsedJson = $decoded;
                } else {
                    $this->parsedJson = null;
                }
            } else {
                $this->parsedJson = null;
            }
        }
        return $this->parsedJson;
    }

    public function referer(): ?string
    {
        return $this->header('referer');
    }

    public function ip(): string
    {
        $h = $this->server['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($h !== '') { return trim(explode(',', $h)[0]); }
        return (string)($this->server['REMOTE_ADDR'] ?? '');
    }
}
