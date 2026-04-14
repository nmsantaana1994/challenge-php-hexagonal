<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Contracts\UserRepositoryInterface;
use App\Application\DTOs\Auth\AuthenticatedUserDto;
use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?AuthenticatedUserDto
    {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if ($user === null) {
            return null;
        }

        return new AuthenticatedUserDto(
            id: (int) $user->getKey(),
            name: (string) $user->name,
            email: (string) $user->email,
            passwordHash: (string) $user->password,
        );
    }

    public function existsById(int $userId): bool
    {
        return User::query()->whereKey($userId)->exists();
    }
}
