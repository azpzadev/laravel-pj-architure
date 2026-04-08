# Domain Layer

The `app/Domain/` namespace holds the application's business logic, organised
by **bounded context** (one folder per context: `Auth/`, `Billing/`, …). It is
the core of the DDD layout — controllers, jobs, and console commands are
*delivery mechanisms* that orchestrate domain code; they must not contain
business rules themselves.

## Per-context layout

```
Domain/
  <Context>/
    Actions/                # single-purpose use-cases (one verb each)
    DataTransferObjects/    # immutable inputs/outputs across boundaries
    Contracts/              # ports the use-cases depend on (repos, gateways, …)
    Exceptions/             # domain-meaningful failures
    Models/                 # (optional) entities / value objects with behaviour
```

The Domain layer is **framework-free**: no Eloquent, no Sanctum, no Guzzle, no
HTTP, no facades. The only `Illuminate\*` types it may import are framework
contracts (`Illuminate\Contracts\Hashing\Hasher`, `Illuminate\Contracts\Cache\Repository`,
…) — interfaces, never concretes.

Concrete implementations of Domain `Contracts/` live in `app/Infrastructure/`
and are bound in `App\Providers\DomainServiceProvider`. See
`app/Infrastructure/CLAUDE.md`.

Eloquent models in `app/Models/` are persistence concerns; the Domain never
imports them. Repositories (Infrastructure) translate Eloquent models to
Domain DTOs at the boundary.

## Rules

### Actions
- One verb per class. Name in imperative: `IssueApiTokenAction`,
  `CancelSubscriptionAction`. Expose a single `execute()` method.
- Inputs are **DTOs**, never `Request` objects or raw arrays from outside.
- Outputs are DTOs, domain models, or `void`. Never `JsonResponse`.
- All dependencies are interfaces — either Domain `Contracts/` or framework
  contracts like `Hasher`, `Cache`, `Mailer`. Concrete classes (Eloquent
  models, Sanctum, Guzzle, facades) are forbidden.
- Mark actions `final readonly` unless they need mutable state.
- Throw domain exceptions on failure; do not return `null` to signal errors.

### Contracts
- One interface per port the use-case depends on. Read ports
  (`*Repository`) and write ports (`*Issuer`, `*Revoker`, `*Notifier`) live
  side by side in `Contracts/`.
- Method signatures use Domain DTOs and PHP scalars only. Never `Model`,
  `Builder`, `Request`, or other framework types.
- Implementations live in `app/Infrastructure/<Context>/` and are bound in
  `DomainServiceProvider`.

### DTOs
- `final readonly` PHP 8.4 classes with constructor property promotion.
- No behaviour beyond trivial named constructors / mappers.
- Use `camelCase` properties even when the wire format is `snake_case` —
  the boundary layer (FormRequest, Resource) is responsible for translation.
- Never reference `Illuminate\Http\Request` or other HTTP types from a DTO.

### Exceptions
- One class per failure mode. Name describes the *condition*, not the action
  (`InvalidCredentialsException`, not `LoginFailedException`).
- Extend `ValidationException` when the natural HTTP response is `422` so
  Laravel's handler renders the standard error envelope. Otherwise extend
  `RuntimeException` and map it in `bootstrap/app.php` if needed.

## What does NOT belong here
- HTTP concerns (requests, responses, resources, middleware).
- View rendering, Blade, Inertia.
- Console command definitions (the *body* of a command may call into Domain).
- Migrations, seeders, factories.
- Anything that imports from `App\Http\*`.

## Example: Auth context

`Domain/Auth/` issues and revokes API tokens, with zero coupling to Eloquent
or Sanctum.

- DTOs:
  - `LoginCredentials` `{email, password, deviceName}` — input.
  - `AuthenticatedUser` `{id, name, email, hashedPassword}` — domain entity.
  - `IssuedToken` `{user: AuthenticatedUser, plainTextToken, deviceName}` — output.
- Contracts:
  - `UserCredentialsRepository::findByEmail(string): ?AuthenticatedUser`
  - `TokenIssuer::issue(int $userId, string $deviceName): string`
  - `CurrentTokenRevoker::revokeCurrent(): void`
- Actions:
  - `IssueApiTokenAction` — depends on the three contracts above plus `Hasher`.
  - `RevokeCurrentTokenAction` — depends on `CurrentTokenRevoker`.
- Exceptions:
  - `InvalidCredentialsException` — extends `ValidationException` (renders 422).

Infrastructure adapters (`Infrastructure/Auth/Eloquent…`, `Sanctum…`) and the
HTTP layer (`AuthController`, `LoginRequest`, `IssuedTokenResource`) translate
between framework types and these Domain types — and do nothing else.
