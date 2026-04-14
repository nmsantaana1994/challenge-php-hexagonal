<?php

namespace App\Application\Contracts;

use App\Application\DTOs\Gif\GifDataDto;
use App\Application\DTOs\Gif\GifSearchResultDto;

interface GiphyClientInterface
{
    public function search(string $query, ?int $limit = null, ?int $offset = null): GifSearchResultDto;

    public function findById(string $gifId): ?GifDataDto;
}
