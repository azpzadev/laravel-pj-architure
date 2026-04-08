<?php

namespace App\Domain\Auth\Contracts;

use App\Domain\Auth\DataTransferObjects\AuthenticatedUser;

/**
 * Read-side port for fetching the data needed to verify credentials.
 *
 * Implementations live in the Infrastructure layer and are bound in
 * `App\Providers\DomainServiceProvider`.
 */
interface UserCredentialsRepository
{
    public function findByEmail(string $email): ?AuthenticatedUser;
}
