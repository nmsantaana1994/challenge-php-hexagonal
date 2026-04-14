<?php

namespace App\Infrastructure\Http\Requests;

use App\Application\DTOs\Auth\LoginUserInputDto;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function toDto(): LoginUserInputDto
    {
        return new LoginUserInputDto(
            email: (string) $this->string('email'),
            password: (string) $this->string('password'),
        );
    }
}
