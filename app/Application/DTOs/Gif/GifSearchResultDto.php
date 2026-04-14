<?php

namespace App\Application\DTOs\Gif;

final readonly class GifSearchResultDto
{
    /**
     * @param list<GifDataDto> $items
     */
    public function __construct(
        public array $items,
        public ?int $total = null,
        public ?int $count = null,
        public ?int $offset = null,
    ) {
    }
}
