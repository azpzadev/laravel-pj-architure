<?php

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Contracts\UserCredentialsRepository;
use App\Domain\Auth\DataTransferObjects\AuthenticatedUser;
use App\Models\User;

final class EloquentUserCredentialsRepository implements UserCredentialsRepository
{
    public function findByEmail(string $email): ?AuthenticatedUser
    {
        $user = User::query()
            ->where('email', $email)
            ->first(['id', 'name', 'email', 'password']);

        if ($user === null) {
            return null;
        }

        return new AuthenticatedUser(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            hashedPassword: $user->password,
        );
    }
}
