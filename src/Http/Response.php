<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    private int $status;
    /** @var array<string, string> */
    private array $headers = [];
    private string $body;

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

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header($k . ': ' . $v);
        }
        echo $this->body;
    }
}
