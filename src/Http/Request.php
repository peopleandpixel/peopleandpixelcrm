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
    /** @var array<string, string> */
    private array $files;
    /** @var array<string, string> */
    private array $server;
    /** @var array<string, string> */
    private array $headers;

    /** @param array<string, mixed> $query
     *  @param array<string, mixed> $body
     *  @param array<string, string> $cookies
     *  @param array<string, string> $files
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

    public function isAjax(): bool
    {
        $xreq = $this->header('x-requested-with');
        return $xreq !== null && strtolower($xreq) === 'xmlhttprequest';
    }

    public function isSecure(): bool
    {
        return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] === 'on')
            || (($this->server['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    }
}
