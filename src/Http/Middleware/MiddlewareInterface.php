<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;

/**
 * Minimal middleware contract compatible with a simple pipeline.
 * Implementations should be stateless and immutable.
 */
interface MiddlewareInterface
{
    /**
     * Handle the request and either return a Response or delegate to $next.
     *
     * @param Request $request Current HTTP request (immutable)
     * @param callable $next Receives Request and returns Response
     */
    public function handle(Request $request, callable $next): Response;
}
