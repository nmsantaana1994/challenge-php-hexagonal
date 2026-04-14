<?php

namespace App\Application\Contracts;

use App\Application\DTOs\Auth\AuthenticatedUserDto;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?AuthenticatedUserDto;

    public function existsById(int $userId): bool;
}
