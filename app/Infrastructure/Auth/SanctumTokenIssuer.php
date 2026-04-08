<?php

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Contracts\TokenIssuer;
use App\Models\User;

final class SanctumTokenIssuer implements TokenIssuer
{
    public function issue(int $userId, string $deviceName): string
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);

        return $user->createToken($deviceName)->plainTextToken;
    }
}
