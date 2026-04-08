# Routes

Conventions for files in `routes/`.

## File responsibilities
- `web.php` — stateful, session/cookie-backed routes (browser UI).
- `api.php` — stateless JSON API routes, automatically prefixed with `/api`.
- `console.php` — Artisan/closure commands.

## Naming
- Use kebab-case URIs (`/password-reset`, not `/passwordReset`).
- Always assign route names with dot notation grouped by resource: `users.index`, `auth.login`.
- Generate URLs through `route('name')` — never hardcode paths.

## Middleware order
For protected API routes, apply middleware in this order:
1. Throttling (e.g. `throttle:api`)
2. Authentication (`auth:sanctum` — tokens are read from the `X-API-Key` header)
3. Authorization (`can:*`)
4. Request-shape validation (form requests in the controller)

Group routes that share middleware via `Route::middleware([...])->group(...)` rather than repeating per-route.

## API specifics
- All API routes return JSON. Never return views or redirects from `api.php`.
- Use API resources for response shaping; do not return raw Eloquent models.
- Version breaking changes by nesting under `Route::prefix('v2')->group(...)`.
