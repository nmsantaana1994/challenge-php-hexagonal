<?php

namespace App\Application\DTOs\Auth;

final readonly class LoginUserInputDto
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }
}
