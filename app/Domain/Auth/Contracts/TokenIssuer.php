<?php

namespace App\Domain\Auth\Contracts;

/**
 * Write-side port for minting an opaque API access token for a user.
 *
 * Returns the **plain-text** token. The implementation is responsible for
 * persisting whatever derived form (hash, JWT, etc.) it needs.
 */
interface TokenIssuer
{
    public function issue(int $userId, string $deviceName): string;
}
