<?php

namespace App\Application\DTOs\Favorite;

final readonly class FavoriteGifDto
{
    /**
     * @param array<string, mixed>|null $rawPayload
     */
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $gifId,
        public string $alias,
        public ?string $gifTitle = null,
        public ?string $gifUrl = null,
        public ?array $rawPayload = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }
}
