<?php

namespace App\Domain\Auth\DataTransferObjects;

/**
 * Immutable carrier for the inputs required to issue an API token.
 *
 * Lives in the domain layer so use-cases never depend on HTTP request shapes.
 */
final readonly class LoginCredentials
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName,
    ) {}
}
