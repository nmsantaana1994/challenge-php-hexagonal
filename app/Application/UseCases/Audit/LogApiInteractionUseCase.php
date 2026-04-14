<?php

namespace App\Application\UseCases\Audit;

use App\Application\Contracts\AuditLogRepositoryInterface;
use App\Application\DTOs\Audit\ApiLogDto;
use App\Application\DTOs\Audit\LogApiInteractionInputDto;

final class LogApiInteractionUseCase
{
    public function __construct(
        private AuditLogRepositoryInterface $auditLogRepository,
    ) {
    }

    public function execute(LogApiInteractionInputDto $input): ApiLogDto
    {
        $apiLog = new ApiLogDto(
            id: null,
            userId: $input->userId,
            serviceName: $input->serviceName,
            requestBody: $input->requestBody,
            responseCode: $input->responseCode,
            responseBody: $input->responseBody,
            ipAddress: $input->ipAddress,
        );

        return $this->auditLogRepository->save($apiLog);
    }
}
