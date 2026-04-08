# app/Http

HTTP layer: controllers, middleware, form requests, resources.

## Layout
- `Controllers/` — thin; delegate business logic to services or actions.
- `Middleware/` — request gating (auth, throttling, headers). See `Middleware/CLAUDE.md`.
- `Requests/` — form requests for input validation; do **not** validate inside controllers.
- `Resources/` — API response transformation.

## Controller rules
- One action per HTTP verb. Prefer single-action invokable controllers for non-CRUD endpoints.
- Type-hint form requests in the method signature for automatic validation.
- Constructor-inject domain Actions (`App\Domain\<Context>\Actions\*`). Controllers must not contain business logic — they translate HTTP ↔ Domain and nothing else.
- Pass DTOs into actions (via `FormRequest::toX()` mappers), never raw arrays or `Request` objects.
- Return API resources wrapping DTOs or models, never raw arrays.
- Do not catch exceptions for the purpose of converting them to responses — let the global handler render them. Domain exceptions extend `ValidationException` when a 422 is appropriate.

## Form requests
- Validate input *and* expose a `to<DTO>()` method that maps `validated()` data
  to the matching domain DTO. The controller calls only this mapper.
- Translate `snake_case` wire keys to `camelCase` DTO properties here — the
  domain layer never sees `snake_case`.

## Error format
All API errors must follow this JSON shape:

```json
{
  "message": "Human-readable message",
  "errors": { "field": ["why it failed"] }
}
```

`errors` is omitted for non-validation failures. HTTP status codes carry the semantic meaning — do not invent custom status fields in the body.

## Authentication
API authentication uses Laravel Sanctum's `auth:sanctum` guard, with tokens
delivered via the `X-API-Key` header (configured in `AppServiceProvider`). See
`Middleware/CLAUDE.md` for the full contract. Do not introduce a second auth
mechanism without approval.
