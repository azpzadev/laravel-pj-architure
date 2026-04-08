<?php

namespace App\Domain\Auth\DataTransferObjects;

/**
 * Result of a successful token issuance.
 *
 * `plainTextToken` is only available at issue time — token issuers hash it
 * on persistence — so it must be returned to the client immediately and
 * never stored or logged.
 */
final readonly class IssuedToken
{
    public function __construct(
        public AuthenticatedUser $user,
        public string $plainTextToken,
        public string $deviceName,
    ) {}
}
