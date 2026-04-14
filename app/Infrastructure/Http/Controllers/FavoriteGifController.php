<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\UseCases\Favorite\SaveFavoriteGifUseCase;
use App\Infrastructure\Http\Requests\SaveFavoriteGifRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

class FavoriteGifController
{
    public function store(SaveFavoriteGifRequest $request, SaveFavoriteGifUseCase $useCase): JsonResponse
    {
        try {
            $favoriteGif = $useCase->execute($request->toDto());
        } catch (Throwable) {
            return response()->json([
                'message' => 'Unable to save favorite GIF.',
            ], 500);
        }

        if ($favoriteGif === null) {
            return response()->json([
                'message' => 'Favorite GIF could not be saved.',
            ], 422);
        }

        return response()->json([
            'data' => [
                'id' => $favoriteGif->id,
                'user_id' => $favoriteGif->userId,
                'gif_id' => $favoriteGif->gifId,
                'alias' => $favoriteGif->alias,
                'gif_title' => $favoriteGif->gifTitle,
                'gif_url' => $favoriteGif->gifUrl,
                'raw_payload' => $favoriteGif->rawPayload,
                'created_at' => $favoriteGif->createdAt,
                'updated_at' => $favoriteGif->updatedAt,
            ],
        ], 201);
    }
}
