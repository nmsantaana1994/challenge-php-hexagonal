<?php

namespace App\Application\Contracts;

use App\Application\DTOs\Audit\ApiLogDto;

interface AuditLogRepositoryInterface
{
    public function save(ApiLogDto $apiLog): ApiLogDto;
}
