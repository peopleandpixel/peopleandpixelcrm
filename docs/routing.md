# Routing

The application uses a very small router with HTTP method support, route parameters, grouping, and standardized error handling.

- File: src/Router.php
- Registration: routes are registered in public/index.php or src/Http/routes.php using $router->get($path, $handler) and $router->post($path, $handler).
- Handlers: can be [ClassName, 'method'] callables, object-callables, or anonymous functions.
- 404/405: not-found and method-not-allowed handlers can be set, otherwise default error templates are rendered.
- 500: unhandled exceptions during dispatch render errors/500.twig (with details in debug mode).

Examples
- GET /contacts → list contacts
- GET /contacts/new → show form
- POST /contacts/new → create
- GET /contacts/{id} → view contact by id (route param)
- GET /contacts/edit?id=123 → edit form (legacy pattern still supported)
- POST /contacts/edit → update
- POST /contacts/delete → delete

Parameterized routes
- Use curly braces for params: /contacts/{id}
- Optionally constrain with regex: /files/{name:[a-zA-Z0-9_.-]+}
- Extracted params are passed to the handler by name if the handler declares parameters (e.g., function ($id) {...}).

Grouping and middleware
- Define route groups with a common prefix and per-group middleware:
  $router->group('/admin', function(Router $r) {
      $r->get('/users', [UsersController::class, 'list']);
      $r->post('/users/new', [UsersController::class, 'create']);
  }, [function(string $method, string $path, callable $next) { /* auth */ $next(); }]);
- Nested groups are supported; group middlewares run closest to the handler, then global middlewares.

URL helpers (Twig)
- url(path, params?): builds a path with query string: {{ url('/contacts', { q: 'acme' }) }}
- canonical_url(path?, params?): absolute URL for canonical link tags
- active_class(path): returns 'active' when current path starts with the given path
- current_path(): returns the current request path

HTTP method notes
- Non-idempotent actions like create/update/delete use POST.
- For edit forms, entity id is typically passed as a query parameter (?id=), but you may also use route params now.

Errors
- 404 is shown when no matching route exists (renders templates/errors/404.twig by default).
- 405 is shown when path exists but HTTP method is not allowed; allowed methods are listed (renders templates/errors/405.twig by default).
- 500 is rendered on unhandled exceptions via templates/errors/500.twig.

---

HTTP foundation
- Request class: App\\Http\\Request (immutable)
  - Access: request()->get('key'), request()->post('key'), request()->input('key'), request()->headers(), request()->cookies(), request()->files()
  - Helpers: request()->wantsJson(), request()->isAjax(), request()->json(), request()->ip(), request()->referer()
- Response class: App\\Http\\Response
  - Builders: Response::html(...), Response::json(...), Response::redirect(...)
  - Headers/cookies: ->withHeader(), ->withHeaders(), ->withCookie(...), ->withStatus(), ->send()

Middleware lifecycle (minimal)
- Contract: App\\Http\\Middleware\\MiddlewareInterface with handle(Request $request, callable $next): Response
- Flow: Each middleware receives the Request and must either return a Response early or call $next($request) to continue the pipeline and get a Response, which it may then modify before returning.
- Registration: A simple queue can be added around routing; for now, implement middlewares in controllers or front controller as needed using the interface to standardize signatures.

Migration note
- Prefer Request/Response over direct usage of $_GET/$_POST/$_FILES and header()/http_response_code().
- Example (JSON endpoint):
  $req = request();
  if ($req->wantsJson()) { return Response::json([...]); }
