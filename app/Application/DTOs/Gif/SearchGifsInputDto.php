<?php

namespace App\Application\DTOs\Gif;

final readonly class SearchGifsInputDto
{
    public function __construct(
        public string $query,
        public ?int $limit = null,
        public ?int $offset = null,
    ) {
    }
}
