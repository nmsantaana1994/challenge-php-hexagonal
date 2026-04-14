<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\DTOs\Gif\GifDataDto;
use App\Application\UseCases\Gif\GetGifByIdUseCase;
use App\Application\UseCases\Gif\SearchGifsUseCase;
use App\Infrastructure\External\Giphy\GiphyIntegrationException;
use App\Infrastructure\Http\Requests\GetGifByIdRequest;
use App\Infrastructure\Http\Requests\SearchGifsRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

class GifController
{
    public function search(SearchGifsRequest $request, SearchGifsUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute($request->toDto());
        } catch (GiphyIntegrationException) {
            return response()->json([
                'message' => 'Unable to retrieve GIFs from Giphy.',
            ], 500);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Unexpected error while searching GIFs.',
            ], 500);
        }

        return response()->json([
            'data' => array_map(
                fn (GifDataDto $gif): array => $this->mapGif($gif),
                $result->items,
            ),
            'meta' => [
                'total' => $result->total,
                'count' => $result->count,
                'offset' => $result->offset,
            ],
        ], 200);
    }

    public function show(GetGifByIdRequest $request, GetGifByIdUseCase $useCase): JsonResponse
    {
        try {
            $gif = $useCase->execute($request->toDto());
        } catch (GiphyIntegrationException) {
            return response()->json([
                'message' => 'Unable to retrieve GIF from Giphy.',
            ], 500);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Unexpected error while retrieving GIF.',
            ], 500);
        }

        if ($gif === null) {
            return response()->json([
                'message' => 'GIF not found.',
            ], 404);
        }

        return response()->json([
            'data' => $this->mapGif($gif),
        ], 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapGif(GifDataDto $gif): array
    {
        return [
            'id' => $gif->gifId,
            'title' => $gif->title,
            'url' => $gif->url,
            'raw_payload' => $gif->rawPayload,
        ];
    }
}
