<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Contracts\FavoriteGifRepositoryInterface;
use App\Application\DTOs\Favorite\FavoriteGifDto;
use App\Infrastructure\Persistence\Eloquent\Models\FavoriteGif;

class EloquentFavoriteGifRepository implements FavoriteGifRepositoryInterface
{
    public function save(FavoriteGifDto $favoriteGif): FavoriteGifDto
    {
        $model = $favoriteGif->id !== null
            ? FavoriteGif::query()->findOrFail($favoriteGif->id)
            : new FavoriteGif();

        $model->fill([
            'user_id' => $favoriteGif->userId,
            'gif_id' => $favoriteGif->gifId,
            'alias' => $favoriteGif->alias,
            'gif_title' => $favoriteGif->gifTitle,
            'gif_url' => $favoriteGif->gifUrl,
            'raw_payload' => $favoriteGif->rawPayload,
        ]);

        $model->save();

        return new FavoriteGifDto(
            id: (int) $model->getKey(),
            userId: (int) $model->user_id,
            gifId: (string) $model->gif_id,
            alias: (string) $model->alias,
            gifTitle: $model->gif_title,
            gifUrl: $model->gif_url,
            rawPayload: $model->raw_payload,
            createdAt: $model->created_at?->toIso8601String(),
            updatedAt: $model->updated_at?->toIso8601String(),
        );
    }
}
