<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    private int $status;
    /** @var array<string, string> */
    private array $headers = [];
    private string $body;
    /** @var array<int, array{name:string,value:string,options:array}> */
    private array $cookies = [];

    public function __construct(string $body = '', int $status = 200, array $headers = [])
    {
        $this->body = $body;
        $this->status = $status;
        foreach ($headers as $k => $v) {
            $this->headers[(string)$k] = (string)$v;
        }
    }

    public static function html(string $html, int $status = 200, array $headers = []): self
    {
        $headers = ['Content-Type' => 'text/html; charset=utf-8'] + $headers;
        return new self($html, $status, $headers);
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): self
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $headers = ['Content-Type' => 'application/json; charset=utf-8'] + $headers;
        return new self($json === false ? 'null' : $json, $status, $headers);
    }

    public static function redirect(string $url, int $status = 302, array $headers = []): self
    {
        $headers = ['Location' => $url] + $headers;
        return new self('', $status, $headers);
    }

    public function status(): int { return $this->status; }
    /** @return array<string, string> */
    public function headers(): array { return $this->headers; }
    public function body(): string { return $this->body; }

    public function withStatus(int $status): self
    {
        $clone = clone $this;
        $clone->status = $status;
        return $clone;
    }

    /** @param array<string,string> $headers */
    public function withHeaders(array $headers): self
    {
        $clone = clone $this;
        foreach ($headers as $k => $v) { $clone->headers[(string)$k] = (string)$v; }
        return $clone;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    /**
     * Set a cookie to be sent with this response.
     * Options: expires(int|DateTimeInterface), path, domain, secure, httponly, samesite(Lax|Strict|None)
     * @param array<string, mixed> $options
     */
    public function withCookie(string $name, string $value, array $options = []): self
    {
        $clone = clone $this;
        $clone->cookies[] = [
            'name' => $name,
            'value' => $value,
            'options' => $options,
        ];
        return $clone;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header($k . ': ' . $v, true);
        }
        // Emit cookies
        foreach ($this->cookies as $c) {
            $opts = $c['options'];
            // Normalize expires
            if (isset($opts['expires']) && $opts['expires'] instanceof \DateTimeInterface) {
                $opts['expires'] = $opts['expires']->getTimestamp();
            }
            // PHP 7.3+ supports array options
            setcookie($c['name'], $c['value'], $opts + [
                'path' => $opts['path'] ?? '/',
                'secure' => (bool)($opts['secure'] ?? false),
                'httponly' => (bool)($opts['httponly'] ?? true),
                'samesite' => (string)($opts['samesite'] ?? 'Lax'),
            ]);
        }
        echo $this->body;
    }
}
