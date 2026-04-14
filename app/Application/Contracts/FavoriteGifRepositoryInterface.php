<?php

namespace App\Application\Contracts;

use App\Application\DTOs\Favorite\FavoriteGifDto;

interface FavoriteGifRepositoryInterface
{
    public function save(FavoriteGifDto $favoriteGif): FavoriteGifDto;
}
