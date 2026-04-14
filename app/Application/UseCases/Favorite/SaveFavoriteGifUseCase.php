<?php

namespace App\Application\UseCases\Favorite;

use App\Application\Contracts\FavoriteGifRepositoryInterface;
use App\Application\Contracts\UserRepositoryInterface;
use App\Application\DTOs\Favorite\FavoriteGifDto;
use App\Application\DTOs\Favorite\SaveFavoriteGifInputDto;

final class SaveFavoriteGifUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private FavoriteGifRepositoryInterface $favoriteGifRepository,
    ) {
    }

    public function execute(SaveFavoriteGifInputDto $input): ?FavoriteGifDto
    {
        if (! $this->userRepository->existsById($input->userId)) {
            return null;
        }

        $favoriteGif = new FavoriteGifDto(
            id: null,
            userId: $input->userId,
            gifId: $input->gifId,
            alias: $input->alias,
            gifTitle: $input->gifTitle,
            gifUrl: $input->gifUrl,
            rawPayload: $input->rawPayload,
        );

        return $this->favoriteGifRepository->save($favoriteGif);
    }
}
