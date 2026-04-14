<?php

namespace App\Application\DTOs\Gif;

final readonly class GifDataDto
{
    /**
     * @param array<string, mixed>|null $rawPayload
     */
    public function __construct(
        public string $gifId,
        public ?string $title = null,
        public ?string $url = null,
        public ?array $rawPayload = null,
    ) {
    }
}
