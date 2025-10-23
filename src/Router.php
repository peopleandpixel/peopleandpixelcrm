<?php

declare(strict_types=1);

namespace App;

/**
 * Very small router with method support, middleware, param routes, groups, and 404/405/500 handling.
 */
class Router
{
    /** @var array<string, array<string, callable|array{0:class-string|object,1:string}>> Exact path routes */
    private array $staticRoutes = [];

    /**
     * @var list<array{
     *   pattern: string,
     *   regex: string,
     *   params: list<string>,
     *   handlers: array<string, callable|array{0:class-string|object,1:string}>,
     *   groupMiddleware: list<callable(string, string, callable):void>
     * }> Parameterized routes
     */
    private array $paramRoutes = [];

    /** @var list<callable(string, string, callable):void> */
    private array $middleware = [];

    /** @var list<string> */
    private array $groupPrefixStack = [];

    /** @var list<list<callable(string, string, callable):void>> */
    private array $groupMiddlewareStack = [];

    /** @var null|callable(string, string):void */
    private $notFoundHandler = null;

    /** @var null|callable(string, array<string>):void */
    private $methodNotAllowedHandler = null;

    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    public function patch(string $path, callable|array $handler): void
    {
        $this->add('PATCH', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    /**
     * Route grouping with optional per-group middleware.
     * Middleware signature: function(string $method, string $path, callable $next): void
     * Example:
     *   $router->group('/admin', function($r){ $r->get('/users', ...); }, [fn($m,$p,$n)=>...]);
     */
    public function group(string $prefix, callable $register, array $middleware = []): void
    {
        $prefix = $this->normalizePath($prefix);
        $this->groupPrefixStack[] = $prefix === '/' ? '' : $prefix;
        $this->groupMiddlewareStack[] = $middleware;
        try {
            // Allow callback to receive $this (Router) for convenience
            $register($this);
        } finally {
            array_pop($this->groupPrefixStack);
            array_pop($this->groupMiddlewareStack);
        }
    }

    public function add(string $method, string $path, callable|array $handler): void
    {
        $method = strtoupper($method);
        $fullPath = $this->applyGroupPrefix($path);
        // Determine if the path contains params like {id}
        if (str_contains($fullPath, '{')) {
            [$regex, $paramNames] = $this->compilePattern($fullPath);
            // Try to merge with an existing entry with same regex
            foreach ($this->paramRoutes as &$route) {
                if ($route['regex'] === $regex) {
                    $route['handlers'][$method] = $handler;
                    return;
                }
            }
            $this->paramRoutes[] = [
                'pattern' => $fullPath,
                'regex' => $regex,
                'params' => $paramNames,
                'handlers' => [$method => $handler],
                'groupMiddleware' => $this->currentGroupMiddleware(),
            ];
            return;
        }
        // static route
        $this->staticRoutes[$fullPath][$method] = $handler;
    }

    /**
     * Register a global middleware.
     * Middleware signature: function(string $method, string $path, callable $next): void
     */
    public function use(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Set a custom 404 handler.
     * @param callable(string $path, string $method):void $handler
     */
    public function setNotFoundHandler(callable $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    /**
     * Set a custom 405 handler.
     * @param callable(string $path, array<string> $allowed):void $handler
     */
    public function setMethodNotAllowedHandler(callable $handler): void
    {
        $this->methodNotAllowedHandler = $handler;
    }

    /**
     * Resolve a handler into an actual callable.
     * Supports:
     * - callable
     * - [class-string, method] for non-static methods (instantiated via new)
     * - [object, method]
     */
    private function resolveHandler(callable|array $handler): callable
    {
        if (is_callable($handler)) {
            return $handler;
        }
        // array handler
        if (is_array($handler) && count($handler) === 2) {
            [$target, $method] = $handler;
            if (is_object($target)) {
                return [$target, $method];
            }
            if (is_string($target) && class_exists($target)) {
                // If it's not a callable static, create an instance
                if (is_callable([$target, $method])) {
                    // Could be static; call as [class, method]
                    return [$target, $method];
                }
                $instance = new $target();
                return [$instance, $method];
            }
        }
        throw new \InvalidArgumentException('Invalid route handler provided');
    }

    /**
     * Dispatch the current request by method and REQUEST_URI path (without a query).
     */
    public function dispatch(?string $requestMethod = null, ?string $requestUri = null): void
    {
        $method = strtoupper($requestMethod ?: ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = $requestUri ?: ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        // Detect base path from SCRIPT_NAME (directory of front controller), e.g., /app/public
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        // Normalize multiple slashes and trailing slash (except root)
        $path = $this->normalizePath($path);

        try {
            // 1) Exact match first
            if (isset($this->staticRoutes[$path])) {
                $handlers = $this->staticRoutes[$path];
                if (!isset($handlers[$method])) {
                    $this->emit405($path, array_keys($handlers));
                    return;
                }
                $this->runPipeline($method, $path, $handlers[$method], []);
                return;
            }

            // 2) Parameterized routes
            foreach ($this->paramRoutes as $route) {
                if (preg_match($route['regex'], $path, $m)) {
                    $params = [];
                    foreach ($route['params'] as $i => $name) {
                        $params[$name] = $m[$name] ?? ($m[$i + 1] ?? null);
                    }
                    $handlers = $route['handlers'];
                    if (!isset($handlers[$method])) {
                        $this->emit405($path, array_keys($handlers));
                        return;
                    }
                    $this->runPipeline($method, $path, $handlers[$method], $route['groupMiddleware'], $params);
                    return;
                }
            }

            // 3) Not found
            $this->emit404($path, $method);
        } catch (\Throwable $e) {
            $this->emit500($e);
        }
    }

    private function runPipeline(string $method, string $path, callable|array $handler, array $groupMiddleware, array $params = []): void
    {
        $resolved = $this->resolveHandler($handler);
        $finalHandler = function() use ($resolved, $params): void {
            if (!empty($params)) {
                // Try to pass params positionally based on handler signature; fallback to associative by order
                $ref = \is_array($resolved) ? new \ReflectionMethod($resolved[0], $resolved[1]) : new \ReflectionFunction($resolved);
                $args = [];
                foreach ($ref->getParameters() as $p) {
                    $name = $p->getName();
                    if (array_key_exists($name, $params)) {
                        $args[] = $params[$name];
                    } else {
                        // Stop filling once first missing appears; allow optional params
                        break;
                    }
                }
                $resolved(...$args);
                return;
            }
            $resolved();
        };

        // group middlewares should run closest to handler, then globals
        $mwChain = array_merge($groupMiddleware, $this->middleware);
        $pipeline = array_reduce(
            array_reverse($mwChain),
            function (callable $next, callable $mw) use ($method, $path) {
                return function() use ($mw, $next, $method, $path): void {
                    $mw($method, $path, $next);
                };
            },
            $finalHandler
        );
        $pipeline();
    }

    private function normalizePath(string $path): string
    {
        $path = preg_replace('#/+#', '/', $path) ?? $path;
        if ($path === '') { $path = '/'; }
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }
        if ($path[0] !== '/') { $path = '/' . $path; }
        return $path;
    }

    private function applyGroupPrefix(string $path): string
    {
        $path = $this->normalizePath($path);
        $prefix = '';
        if (!empty($this->groupPrefixStack)) {
            $prefix = implode('', $this->groupPrefixStack);
        }
        if ($prefix === '' || $prefix === '/') {
            return $path;
        }
        if ($path === '/') { return $prefix; }
        return $this->normalizePath($prefix . ($path === '/' ? '' : $path));
    }

    /**
     * @return list<callable(string, string, callable):void>
     */
    private function currentGroupMiddleware(): array
    {
        $all = [];
        foreach ($this->groupMiddlewareStack as $group) {
            foreach ($group as $mw) { $all[] = $mw; }
        }
        return $all;
    }

    /**
     * Compile a path pattern like "/contacts/{id}" to a regex and collect param names.
     * Supported tokens: {name} (matches [^/]+). To restrict, allow {id:\d+} style basic regex.
     * @return array{0:string,1:list<string>}
     */
    private function compilePattern(string $pattern): array
    {
        $paramNames = [];
        $regex = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([^}]+))?\}#', function($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            $sub = isset($m[2]) ? $m[2] : '[^/]+';
            return '(?P<' . $m[1] . '>' . $sub . ')';
        }, $pattern);
        $regex = '#^' . $regex . '$#';
        return [$regex, $paramNames];
    }

    private function emit404(string $path, string $method): void
    {
        if ($this->notFoundHandler) {
            ($this->notFoundHandler)($path, $method);
            return;
        }
        http_response_code(404);
        if (function_exists('render')) {
            render('errors/404', ['path' => $path, 'method' => $method]);
        } else {
            echo '404 Not Found';
        }
    }

    private function emit405(string $path, array $allowed): void
    {
        header('Allow: ' . implode(', ', $allowed));
        if ($this->methodNotAllowedHandler) {
            ($this->methodNotAllowedHandler)($path, $allowed);
            return;
        }
        http_response_code(405);
        if (function_exists('render')) {
            render('errors/405', ['path' => $path, 'allowed' => $allowed]);
        } else {
            echo '405 Method Not Allowed';
        }
    }

    private function emit500(\Throwable $e): void
    {
        http_response_code(500);
        // Try to use existing ErrorHandler if wired; otherwise render template
        if (class_exists('App\\Util\\ErrorHandler')) {
            try {
                \App\Util\ErrorHandler::handleException($e);
                return;
            } catch (\Throwable) {
                // fall through to render()
            }
        }
        $details = null;
        $debug = in_array(strtolower($_ENV['APP_DEBUG'] ?? ''), ['1','true','yes'], true);
        if ($debug) {
            $details = [
                'class' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];
        }
        if (function_exists('render')) {
            render('errors/500', ['errorDetails' => $details]);
        } else {
            echo '500 Internal Server Error';
        }
    }
}
