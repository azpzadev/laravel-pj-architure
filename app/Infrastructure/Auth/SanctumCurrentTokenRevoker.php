<?php

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Contracts\CurrentTokenRevoker;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Laravel\Sanctum\PersonalAccessToken;

final readonly class SanctumCurrentTokenRevoker implements CurrentTokenRevoker
{
    public function __construct(private AuthFactory $auth) {}

    public function revokeCurrent(): void
    {
        $guard = $this->auth->guard('sanctum');

        /** @var User|null $user */
        $user = $guard->user();

        $token = $user?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        // Drop the cached user from the guard so any code that checks auth
        // state later in the same request lifecycle does not see a stale
        // "logged in" user. (Also makes the behaviour observable in tests,
        // where the singleton guard otherwise caches across HTTP calls.)
        if (method_exists($guard, 'forgetUser')) {
            $guard->forgetUser();
        }
    }
}
