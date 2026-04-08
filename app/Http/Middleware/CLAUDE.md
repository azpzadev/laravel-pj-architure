# Middleware

## API authentication: Sanctum via `X-API-Key`

This project does **not** use a custom auth middleware. API authentication is
handled by Laravel Sanctum's built-in `auth:sanctum` guard, with one project-
specific tweak: tokens are read from the **`X-API-Key`** header instead of
`Authorization: Bearer`.

The override lives in `App\Providers\AppServiceProvider::boot()`:

```php
Sanctum::getAccessTokenFromRequestUsing(
    fn (Request $request): ?string => $request->header('X-API-Key')
);
```

### How clients authenticate
1. `POST /api/login` with `{ email, password, device_name }` → returns `{ token }`.
2. Send `X-API-Key: <token>` on every subsequent request.
3. `POST /api/logout` revokes the token used for the current request.

### Protecting routes
Apply `auth:sanctum` to any route that requires a logged-in user:

```php
Route::middleware('auth:sanctum')->group(function () {
    // ...
});
```

### Security rules
- Never log the `X-API-Key` header — register any request-logging middleware
  *after* `auth:sanctum` and scrub `X-API-Key` (and `Authorization`) from logs.
- Never persist plain tokens; Sanctum hashes them with SHA-256 in
  `personal_access_tokens` automatically. Only return the plaintext once, at
  issue time, via `NewAccessToken::$plainTextToken`.
- Always pair the login route with `throttle:api` (or stricter) to mitigate
  credential stuffing.
- Do not weaken `getAccessTokenFromRequestUsing` to also accept query string
  tokens — headers only.
- Use `Sanctum::actingAs()` in tests instead of issuing real tokens.

## Writing new middleware
- Create with `php artisan make:middleware Name --no-interaction`.
- Register the alias in `bootstrap/app.php` under `->withMiddleware(...)` and
  reference it from routes by alias, never by FQCN.
- Type-hint dependencies in the constructor (resolved via the container).
- Return `Symfony\Component\HttpFoundation\Response`, not `mixed`.
- Keep middleware focused on a single concern; compose via groups.
