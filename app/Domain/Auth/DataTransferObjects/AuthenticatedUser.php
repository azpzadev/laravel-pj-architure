<?php

namespace App\Domain\Auth\DataTransferObjects;

/**
 * Framework-free representation of a user as the Auth domain understands it.
 *
 * The Eloquent `App\Models\User` lives in the persistence layer; the domain
 * never imports it. Repositories are responsible for mapping between the two.
 */
final readonly class AuthenticatedUser
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $hashedPassword,
    ) {}
}
