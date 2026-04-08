<?php

namespace App\Domain\Auth\Exceptions;

use Illuminate\Validation\ValidationException;

/**
 * Thrown when email + password do not match a known user.
 *
 * Extends ValidationException so Laravel's exception handler renders the
 * standard 422 JSON shape automatically — no try/catch in controllers.
 */
final class InvalidCredentialsException extends ValidationException
{
    public static function forEmail(): self
    {
        return self::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
}
