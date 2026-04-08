<?php

namespace App\Domain\Auth\Contracts;

/**
 * Write-side port for revoking the access token used by the current request.
 *
 * The implementation reads the token from request-scoped state (e.g. the
 * authenticated guard) so callers do not have to know how the token was
 * presented to the application.
 */
interface CurrentTokenRevoker
{
    public function revokeCurrent(): void;
}
