<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Contracts\TokenIssuer;
use App\Domain\Auth\Contracts\UserCredentialsRepository;
use App\Domain\Auth\DataTransferObjects\IssuedToken;
use App\Domain\Auth\DataTransferObjects\LoginCredentials;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use Illuminate\Contracts\Hashing\Hasher;

/**
 * Use case: verify a user's credentials and mint a new API access token.
 *
 * Depends only on Domain contracts and the framework `Hasher` interface — it
 * has no knowledge of Eloquent, Sanctum, HTTP, or any other delivery concern.
 */
final readonly class IssueApiTokenAction
{
    public function __construct(
        private UserCredentialsRepository $users,
        private TokenIssuer $tokens,
        private Hasher $hasher,
    ) {}

    public function execute(LoginCredentials $credentials): IssuedToken
    {
        $user = $this->users->findByEmail($credentials->email);

        if ($user === null || ! $this->hasher->check($credentials->password, $user->hashedPassword)) {
            throw InvalidCredentialsException::forEmail();
        }

        $plainTextToken = $this->tokens->issue($user->id, $credentials->deviceName);

        return new IssuedToken(
            user: $user,
            plainTextToken: $plainTextToken,
            deviceName: $credentials->deviceName,
        );
    }
}
