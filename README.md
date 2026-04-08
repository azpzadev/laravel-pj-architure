# laravel-pj-architure

A Laravel 13 reference application demonstrating a **Domain-Driven, Hexagonal**
layout on top of the standard Laravel skeleton. Ships with a working
Sanctum-backed API authentication slice (`login`, `logout`, `user`) implemented
end-to-end through the architecture.

## Stack

- PHP 8.4
- Laravel 13
- Laravel Sanctum 4 (API token auth)
- Pest 4 (testing)
- Laravel Pint (formatting)
- Laravel Boost (MCP tooling for AI agents)

## Architecture

The `app/` tree is split into three layers. The Domain layer is
framework-free; Infrastructure adapts framework concerns to Domain ports; the
HTTP layer is a thin delivery mechanism that translates requests into Domain
DTOs.

```
app/
├── Domain/                   # business logic, framework-free
│   └── Auth/
│       ├── Actions/          # one verb per use case
│       ├── Contracts/        # ports (repos, issuers, revokers)
│       ├── DataTransferObjects/
│       └── Exceptions/
├── Infrastructure/           # concrete adapters bound to Domain contracts
│   └── Auth/
│       ├── EloquentUserCredentialsRepository.php
│       ├── SanctumTokenIssuer.php
│       └── SanctumCurrentTokenRevoker.php
├── Http/                     # delivery layer
│   ├── Controllers/Api/
│   ├── Requests/Api/
│   └── Resources/
├── Models/                   # Eloquent (persistence concern only)
└── Providers/
    └── DomainServiceProvider.php   # binds Domain contracts → Infrastructure
```

### Layer rules

- **Domain** never imports Eloquent, Sanctum, Guzzle, HTTP, or facades. The
  only `Illuminate\*` types it may use are framework *contracts*
  (`Illuminate\Contracts\Hashing\Hasher`, etc.).
- **Actions** are `final readonly`, expose a single `execute()` method, take
  DTOs in and return DTOs/`void`, and throw domain exceptions on failure.
- **Contracts** are ports the actions depend on. Implementations live in
  `app/Infrastructure/<Context>/` and are wired in
  `App\Providers\DomainServiceProvider`.
- **DTOs** are `final readonly` with constructor property promotion. They use
  `camelCase` properties; the boundary layer (FormRequest / Resource)
  translates wire formats.
- **Eloquent models** are persistence-only. Repositories translate them to
  Domain DTOs at the boundary.

See `app/Domain/CLAUDE.md` and `app/Infrastructure/CLAUDE.md` for the full
guidelines.

## Auth slice (worked example)

The `Auth` bounded context is a complete walk-through of the layout:

| Layer | Class |
|---|---|
| HTTP | `App\Http\Controllers\Api\AuthController` |
| HTTP | `App\Http\Requests\Api\LoginRequest` |
| HTTP | `App\Http\Resources\IssuedTokenResource` |
| Domain action | `App\Domain\Auth\Actions\IssueApiTokenAction` |
| Domain action | `App\Domain\Auth\Actions\RevokeCurrentTokenAction` |
| Domain port | `App\Domain\Auth\Contracts\UserCredentialsRepository` |
| Domain port | `App\Domain\Auth\Contracts\TokenIssuer` |
| Domain port | `App\Domain\Auth\Contracts\CurrentTokenRevoker` |
| Infrastructure | `App\Infrastructure\Auth\EloquentUserCredentialsRepository` |
| Infrastructure | `App\Infrastructure\Auth\SanctumTokenIssuer` |
| Infrastructure | `App\Infrastructure\Auth\SanctumCurrentTokenRevoker` |

### API endpoints

| Method | URI         | Name        | Middleware              |
|--------|-------------|-------------|-------------------------|
| POST   | `/login`    | `api.login` | `throttle:6,1`          |
| POST   | `/logout`   | `api.logout`| `auth:sanctum`          |
| GET    | `/user`     | `api.user`  | `auth:sanctum`          |

`POST /login` returns a plain-text Sanctum token via `IssuedTokenResource`.
Invalid credentials throw `InvalidCredentialsException` (extends
`ValidationException`, renders 422).

## Getting started

```bash
git clone git@github.com:azpzadev/laravel-pj-architure.git
cd laravel-pj-architure

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

php artisan serve
```

## Testing

```bash
php artisan test --compact
```

Create new tests with:

```bash
php artisan make:test --pest Api/LoginTest
```

## Code style

Format any modified PHP files before committing:

```bash
vendor/bin/pint --dirty --format agent
```

## Adding a new bounded context

1. Create `app/Domain/<Context>/{Actions,Contracts,DataTransferObjects,Exceptions}/`.
2. Define DTOs and contracts first; write the action against the contracts.
3. Add concrete adapters under `app/Infrastructure/<Context>/`.
4. Bind contracts to adapters in `App\Providers\DomainServiceProvider`.
5. Add the HTTP delivery layer (`FormRequest`, `Resource`, `Controller`) that
   translates requests into DTOs and calls the action.
6. Cover the slice with feature tests under `tests/Feature/`.

## License

MIT.
