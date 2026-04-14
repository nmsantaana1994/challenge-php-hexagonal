<?php

namespace App\Application\UseCases\Gif;

use App\Application\Contracts\GiphyClientInterface;
use App\Application\DTOs\Gif\GifSearchResultDto;
use App\Application\DTOs\Gif\SearchGifsInputDto;

final class SearchGifsUseCase
{
    public function __construct(
        private GiphyClientInterface $giphyClient,
    ) {
    }

    public function execute(SearchGifsInputDto $input): GifSearchResultDto
    {
        return $this->giphyClient->search(
            $input->query,
            $input->limit,
            $input->offset,
        );
    }
}
