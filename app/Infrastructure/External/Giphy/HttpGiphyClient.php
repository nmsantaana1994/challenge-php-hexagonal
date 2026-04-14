<?php

namespace App\Infrastructure\External\Giphy;

use App\Application\Contracts\GiphyClientInterface;
use App\Application\DTOs\Gif\GifDataDto;
use App\Application\DTOs\Gif\GifSearchResultDto;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class HttpGiphyClient implements GiphyClientInterface
{
    public function search(string $query, ?int $limit = null, ?int $offset = null): GifSearchResultDto
    {
        $payload = $this->request('v1/gifs/search', array_filter([
            'q' => $query,
            'limit' => $limit,
            'offset' => $offset,
        ], static fn (mixed $value): bool => $value !== null));

        $items = array_map(
            fn (array $gif): GifDataDto => $this->mapGifData($gif),
            $payload['data'] ?? [],
        );

        $pagination = $payload['pagination'] ?? [];

        return new GifSearchResultDto(
            items: $items,
            total: isset($pagination['total_count']) ? (int) $pagination['total_count'] : null,
            count: isset($pagination['count']) ? (int) $pagination['count'] : null,
            offset: isset($pagination['offset']) ? (int) $pagination['offset'] : null,
        );
    }

    public function findById(string $gifId): ?GifDataDto
    {
        try {
            $response = $this->httpClient()->get("v1/gifs/{$gifId}");
        } catch (ConnectionException $exception) {
            throw new GiphyIntegrationException('Unable to connect to Giphy.', 0, $exception);
        }

        if ($response->status() === 404) {
            return null;
        }

        $payload = $response->json();

        if ($this->isMissingGifPayload($response->status(), $payload)) {
            return null;
        }

        if ($response->failed()) {
            throw new GiphyIntegrationException(
                sprintf('Giphy request failed with status %d.', $response->status())
            );
        }

        $data = $payload['data'] ?? null;

        if (! is_array($data) || $data === [] || ! isset($data['id']) || $data['id'] === '') {
            return null;
        }

        return $this->mapGifData($data);
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    private function request(string $endpoint, array $query): array
    {
        try {
            $response = $this->httpClient()->get($endpoint, $query);
        } catch (ConnectionException $exception) {
            throw new GiphyIntegrationException('Unable to connect to Giphy.', 0, $exception);
        }

        if ($response->failed()) {
            throw new GiphyIntegrationException(
                sprintf('Giphy request failed with status %d.', $response->status())
            );
        }

        return $response->json();
    }

    private function httpClient()
    {
        $apiKey = (string) config('services.giphy.api_key', '');

        if ($apiKey === '') {
            throw new GiphyIntegrationException('Giphy API key is not configured.');
        }

        return Http::baseUrl((string) config('services.giphy.base_url', 'https://api.giphy.com/'))
            ->acceptJson()
            ->timeout((int) config('services.giphy.timeout', 10))
            ->withQueryParameters([
                'api_key' => $apiKey,
            ]);
    }

    /**
     * @param mixed $payload
     */
    private function isMissingGifPayload(int $statusCode, mixed $payload): bool
    {
        if (! is_array($payload)) {
            return false;
        }

        $meta = $payload['meta'] ?? null;
        $data = $payload['data'] ?? null;

        if (! is_array($meta)) {
            return is_array($data) && $data === [];
        }

        $metaStatus = isset($meta['status']) ? (int) $meta['status'] : null;
        $metaMessage = strtolower((string) ($meta['msg'] ?? ''));
        $metaErrorCode = strtolower((string) ($meta['error_code'] ?? ''));
        $hasEmptyData = is_array($data) && $data === [];

        if ($metaStatus === 404) {
            return true;
        }

        if ($statusCode !== 400 || ! $hasEmptyData) {
            return false;
        }

        $mentionsInvalidGifId = str_contains($metaMessage, 'validation error')
            && (str_contains($metaErrorCode, 'invalid gif id') || str_contains($metaErrorCode, 'gifid'));

        return $mentionsInvalidGifId;
    }

    /**
     * @param array<string, mixed> $gif
     */
    private function mapGifData(array $gif): GifDataDto
    {
        $images = is_array($gif['images'] ?? null) ? $gif['images'] : [];
        $originalImage = is_array($images['original'] ?? null) ? $images['original'] : [];

        return new GifDataDto(
            gifId: (string) ($gif['id'] ?? ''),
            title: isset($gif['title']) ? (string) $gif['title'] : null,
            url: isset($originalImage['url'])
                ? (string) $originalImage['url']
                : (isset($gif['url']) ? (string) $gif['url'] : null),
            rawPayload: $gif,
        );
    }
}
