<?php

namespace App\Application\UseCases\Auth;

use App\Application\Contracts\TokenIssuerInterface;
use App\Application\Contracts\UserRepositoryInterface;
use App\Application\DTOs\Auth\IssuedTokenDto;
use App\Application\DTOs\Auth\LoginUserInputDto;

final class LoginUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TokenIssuerInterface $tokenIssuer,
    ) {
    }

    public function execute(LoginUserInputDto $input): ?IssuedTokenDto
    {
        $user = $this->userRepository->findByEmail($input->email);

        if ($user === null) {
            return null;
        }

        if (! password_verify($input->password, $user->passwordHash)) {
            return null;
        }

        return $this->tokenIssuer->issueForUser($user);
    }
}
