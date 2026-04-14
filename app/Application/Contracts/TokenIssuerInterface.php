<?php

namespace App\Application\Contracts;

use App\Application\DTOs\Auth\AuthenticatedUserDto;
use App\Application\DTOs\Auth\IssuedTokenDto;

interface TokenIssuerInterface
{
    public function issueForUser(AuthenticatedUserDto $user): IssuedTokenDto;
}
