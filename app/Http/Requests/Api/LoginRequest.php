<?php

namespace App\Http\Requests\Api;

use App\Domain\Auth\DataTransferObjects\LoginCredentials;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Map validated input to the domain DTO so the controller never touches
     * raw request data.
     */
    public function toCredentials(): LoginCredentials
    {
        /** @var array{email: string, password: string, device_name: string} $data */
        $data = $this->validated();

        return new LoginCredentials(
            email: $data['email'],
            password: $data['password'],
            deviceName: $data['device_name'],
        );
    }
}
