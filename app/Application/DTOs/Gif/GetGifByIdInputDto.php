<?php

namespace App\Application\DTOs\Gif;

final readonly class GetGifByIdInputDto
{
    public function __construct(
        public string $gifId,
    ) {
    }
}
