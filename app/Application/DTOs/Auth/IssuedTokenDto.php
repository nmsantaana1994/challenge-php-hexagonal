<?php

namespace App\Application\DTOs\Auth;

final readonly class IssuedTokenDto
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn,
    ) {
    }
}
