# Infrastructure Layer

`app/Infrastructure/` contains adapters that implement Domain contracts using
concrete frameworks, libraries, and external services. This is the **only**
place outside `app/Models/` and `app/Http/` where Eloquent, Sanctum, Guzzle,
queue drivers, mail drivers, etc. may be referenced directly.

## Per-context layout

Mirror the Domain layout:

```
Infrastructure/
  <Context>/                # same context names as Domain/<Context>/
    Eloquent*Repository.php
    *HttpClient.php
    *Mailer.php
    ...
```

For example, `Domain/Auth/Contracts/UserCredentialsRepository` is implemented
by `Infrastructure/Auth/EloquentUserCredentialsRepository`.

## Rules

### Adapters
- One class per Domain contract. Name describes the technology that backs it
  (`Eloquent…`, `Sanctum…`, `Redis…`, `Stripe…`).
- Mark `final` (and `readonly` where possible).
- Implement exactly one Domain contract per class. Do not "just add another
  method" — add a method to the contract first, then implement it.
- Translate persistence types (Eloquent models, API responses) to Domain DTOs
  before returning. Persistence types must not leak across the boundary.
- Throw Domain exceptions, not framework exceptions, when communicating
  expected failures back to the Domain.

### Wiring
- All Domain contracts must be bound to an Infrastructure implementation in
  `App\Providers\DomainServiceProvider`. The provider is the single source of
  truth for what is plugged in where.
- Use simple `$bindings` array entries when no constructor arguments need
  customising. Use a closure binding only when wiring is non-trivial.
- The provider implements `DeferrableProvider` and lists every binding in
  `provides()` so it is only booted when those types are resolved.

### What does NOT belong here
- Use cases / business logic (those live in `Domain/<Context>/Actions/`).
- HTTP, view, or console code.
- Domain DTOs and contracts (those live in `Domain/`).

## Example: Auth context

| Domain contract | Infrastructure implementation |
|---|---|
| `UserCredentialsRepository` | `EloquentUserCredentialsRepository` (queries `users` table, maps to `AuthenticatedUser` DTO) |
| `TokenIssuer` | `SanctumTokenIssuer` (calls `User::createToken()`, returns plaintext) |
| `CurrentTokenRevoker` | `SanctumCurrentTokenRevoker` (reads the Sanctum guard, deletes `PersonalAccessToken`, clears cached guard user) |

If we ever swap Sanctum for Passport / a custom JWT implementation, only this
folder and the provider need to change. The Domain actions, controllers, and
tests are untouched.
