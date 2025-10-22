# Routing

The application uses a very small, path-based router with HTTP method support.

- File: src/Router.php
- Registration: routes are registered in public/index.php using $router->get($path, $handler) and $router->post($path, $handler).
- Handlers: can be [ClassName, 'method'] callables or anonymous functions.
- 404/405: not-found and method-not-allowed handlers are supported and set in public/index.php.

Examples
- GET /contacts → list contacts
- GET /contacts/new → show form
- POST /contacts/new → create
- GET /contacts/edit?id=123 → edit form
- POST /contacts/edit → update
- POST /contacts/delete → delete

Other main route groups
- /times, /tasks, /projects, /employees, /candidates, /payments (also /payments/export.csv), /storage, /storage/history
- /calendar, /calendar/events
- /login, /logout

URL helpers (Twig)
- url(path, params?): builds a path with query string: {{ url('/contacts', { q: 'acme' }) }}
- canonical_url(path?, params?): absolute URL for canonical link tags
- active_class(path): returns 'active' when current path starts with the given path
- current_path(): returns the current request path

HTTP method notes
- Non-idempotent actions like create/update/delete use POST.
- For edit forms, entity id is typically passed as a query parameter (?id=).

Errors
- 404 is shown when no matching route exists.
- 405 is shown when path exists but HTTP method is not allowed; allowed methods are listed.
