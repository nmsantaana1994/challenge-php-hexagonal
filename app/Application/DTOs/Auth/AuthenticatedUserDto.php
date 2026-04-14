<?php

namespace App\Application\DTOs\Auth;

final readonly class AuthenticatedUserDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $passwordHash,
    ) {
    }
}
