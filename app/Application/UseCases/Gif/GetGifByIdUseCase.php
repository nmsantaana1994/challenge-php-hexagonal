<?php

namespace App\Application\UseCases\Gif;

use App\Application\Contracts\GiphyClientInterface;
use App\Application\DTOs\Gif\GetGifByIdInputDto;
use App\Application\DTOs\Gif\GifDataDto;

final class GetGifByIdUseCase
{
    public function __construct(
        private GiphyClientInterface $giphyClient,
    ) {
    }

    public function execute(GetGifByIdInputDto $input): ?GifDataDto
    {
        return $this->giphyClient->findById($input->gifId);
    }
}
