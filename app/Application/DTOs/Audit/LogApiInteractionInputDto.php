<?php

namespace App\Application\DTOs\Audit;

final readonly class LogApiInteractionInputDto
{
    /**
     * @param array<string, mixed>|null $requestBody
     */
    public function __construct(
        public ?int $userId,
        public string $serviceName,
        public ?array $requestBody,
        public int $responseCode,
        public ?string $responseBody,
        public string $ipAddress,
    ) {
    }
}
