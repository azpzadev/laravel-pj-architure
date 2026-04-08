<?php

namespace App\Http\Controllers\Api;

use App\Domain\Auth\Actions\IssueApiTokenAction;
use App\Domain\Auth\Actions\RevokeCurrentTokenAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\IssuedTokenResource;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly IssueApiTokenAction $issueApiToken,
        private readonly RevokeCurrentTokenAction $revokeCurrentToken,
    ) {}

    /**
     * Exchange email + password for an API token.
     *
     * The returned `token` value must be sent in the `X-API-Key` header
     * on subsequent requests to routes protected by `auth:sanctum`.
     */
    public function login(LoginRequest $request): IssuedTokenResource
    {
        $issued = $this->issueApiToken->execute($request->toCredentials());

        return new IssuedTokenResource($issued);
    }

    /**
     * Revoke the API token used to authenticate the current request.
     */
    public function logout(): JsonResponse
    {
        $this->revokeCurrentToken->execute();

        return response()->json(['message' => 'Logged out.']);
    }
}
