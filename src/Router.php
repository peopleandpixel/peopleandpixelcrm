<?php

namespace App;

/**
 * Very small path-based router with method support, middleware, and 404/405 handling.
 */
class Router
{
    /** @var array<string, array<string, callable|array{0:class-string|object,1:string}>> */
    private array $routes = [];

    /** @var list<callable(string, string, callable):void> */
    private array $middleware = [];

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

    public function add(string $method, string $path, callable|array $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$path][$method] = $handler;
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
        $path = preg_replace('#/+#', '/', $path) ?? $path;
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        if (!isset($this->routes[$path])) {
            if ($this->notFoundHandler) {
                ($this->notFoundHandler)($path, $method);
                return;
            }
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        $handlers = $this->routes[$path];
        if (!isset($handlers[$method])) {
            // 405 Method Not Allowed
            $allowed = array_keys($handlers);
            header('Allow: ' . implode(', ', $allowed));
            if ($this->methodNotAllowedHandler) {
                ($this->methodNotAllowedHandler)($path, $allowed);
                return;
            }
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        // Build the handler pipeline with middleware
        $finalHandler = function() use ($handlers, $method): void {
            $callable = $this->resolveHandler($handlers[$method]);
            $callable();
        };
        // Wrap middlewares LIFO: last registered runs closest to handler
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            function (callable $next, callable $mw) use ($method, $path) {
                return function() use ($mw, $next, $method, $path): void {
                    $mw($method, $path, $next);
                };
            },
            $finalHandler
        );

        $pipeline();
    }
}
