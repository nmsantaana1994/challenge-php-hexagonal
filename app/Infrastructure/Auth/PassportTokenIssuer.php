<?php

namespace App\Infrastructure\Auth;

use App\Application\Contracts\TokenIssuerInterface;
use App\Application\DTOs\Auth\AuthenticatedUserDto;
use App\Application\DTOs\Auth\IssuedTokenDto;
use App\Models\User;
use Carbon\CarbonImmutable;
use Laravel\Passport\Passport;
use RuntimeException;

class PassportTokenIssuer implements TokenIssuerInterface
{
    public function issueForUser(AuthenticatedUserDto $user): IssuedTokenDto
    {
        /** @var User|null $eloquentUser */
        $eloquentUser = User::query()->find($user->id);

        if ($eloquentUser === null) {
            throw new RuntimeException('Unable to issue token for a non-existent user.');
        }

        $tokenResult = $eloquentUser->createToken('challenge-api-token');
        $expiresIn = CarbonImmutable::now()
            ->add(Passport::personalAccessTokensExpireIn())
            ->diffInSeconds(CarbonImmutable::now());

        return new IssuedTokenDto(
            accessToken: (string) $tokenResult->accessToken,
            tokenType: (string) ($tokenResult->tokenType ?? 'Bearer'),
            expiresIn: $expiresIn,
        );
    }
}
