<?php

namespace App\Providers;

use App\Domain\Auth\Contracts\CurrentTokenRevoker;
use App\Domain\Auth\Contracts\TokenIssuer;
use App\Domain\Auth\Contracts\UserCredentialsRepository;
use App\Infrastructure\Auth\EloquentUserCredentialsRepository;
use App\Infrastructure\Auth\SanctumCurrentTokenRevoker;
use App\Infrastructure\Auth\SanctumTokenIssuer;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Wires Domain contracts to their Infrastructure implementations.
 *
 * Adding a new bounded context? Bind its ports here. Keeping all bindings in
 * one place makes it trivial to swap implementations (e.g. for testing or for
 * a future migration off Sanctum).
 */
class DomainServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        UserCredentialsRepository::class => EloquentUserCredentialsRepository::class,
        TokenIssuer::class => SanctumTokenIssuer::class,
        CurrentTokenRevoker::class => SanctumCurrentTokenRevoker::class,
    ];

    /**
     * @return array<int, class-string>
     */
    public function provides(): array
    {
        return [
            UserCredentialsRepository::class,
            TokenIssuer::class,
            CurrentTokenRevoker::class,
        ];
    }
}
