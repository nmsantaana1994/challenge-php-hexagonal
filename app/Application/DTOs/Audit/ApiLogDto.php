<?php

namespace App\Application\DTOs\Audit;

final readonly class ApiLogDto
{
    /**
     * @param array<string, mixed>|null $requestBody
     */
    public function __construct(
        public ?int $id,
        public ?int $userId,
        public string $serviceName,
        public ?array $requestBody,
        public int $responseCode,
        public ?string $responseBody,
        public string $ipAddress,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }
}
