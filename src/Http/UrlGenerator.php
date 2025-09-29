<?php

declare(strict_types=1);

namespace App\Http;

class UrlGenerator
{
    public function __construct(private Request $request)
    {
    }

    public function basePath(): string
    {
        $scriptName = $this->request->server()['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(str_replace('\\', '/', dirname((string)$scriptName)), '/');
        return ($basePath !== '/' ? $basePath : '');
    }

    public function url(string $path = '/', array $params = []): string
    {
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }
        $path = preg_replace('#/+#', '/', $path) ?? $path;
        $fullPath = $this->basePath() !== '' ? ($this->basePath() . $path) : $path;
        if (!empty($params)) {
            $qs = http_build_query($params);
            if ($qs !== '') {
                return $fullPath . '?' . $qs;
            }
        }
        return $fullPath;
    }

    public function canonical(?string $path = null, array $params = []): string
    {
        $scheme = $this->request->isSecure() ? 'https' : 'http';
        $host = $this->request->server()['HTTP_HOST'] ?? ($this->request->server()['SERVER_NAME'] ?? 'localhost');
        $p = $path ?? $this->request->path();
        return $scheme . '://' . $host . $this->url($p, $params);
    }
}
