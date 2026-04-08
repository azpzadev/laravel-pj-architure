<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Contracts\CurrentTokenRevoker;

/**
 * Use case: revoke the API access token used by the current request.
 */
final readonly class RevokeCurrentTokenAction
{
    public function __construct(private CurrentTokenRevoker $revoker) {}

    public function execute(): void
    {
        $this->revoker->revokeCurrent();
    }
}
