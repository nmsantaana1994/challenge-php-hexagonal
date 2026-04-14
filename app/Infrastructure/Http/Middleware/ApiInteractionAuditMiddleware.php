<?php

namespace App\Infrastructure\Http\Middleware;

use App\Application\DTOs\Audit\LogApiInteractionInputDto;
use App\Application\UseCases\Audit\LogApiInteractionUseCase;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiInteractionAuditMiddleware
{
    private const REDACTED_VALUE = '[REDACTED]';

    /**
     * @var list<string>
     */
    private const SENSITIVE_KEYS = [
        'password',
        'access_token',
        'token',
        'refresh_token',
        'authorization',
        'client_secret',
    ];

    public function __construct(
        private LogApiInteractionUseCase $logApiInteractionUseCase,
    ) {
    }

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            $this->logApiInteractionUseCase->execute(new LogApiInteractionInputDto(
                userId: $request->user()?->getAuthIdentifier(),
                serviceName: sprintf('%s /%s', $request->method(), ltrim($request->path(), '/')),
                requestBody: $this->buildRequestPayload($request),
                responseCode: $response->getStatusCode(),
                responseBody: $this->extractResponseBody($response),
                ipAddress: (string) $request->ip(),
            ));
        } catch (Throwable) {
            // Audit failures must never alter the API response flow.
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildRequestPayload(Request $request): ?array
    {
        $input = $request->all();
        $routeParameters = $request->route()?->parameters() ?? [];
        $payload = array_merge($routeParameters, $input);

        if ($payload === []) {
            return null;
        }

        return $this->sanitizeArray($payload);
    }

    private function extractResponseBody(Response $response): ?string
    {
        if ($response->isEmpty()) {
            return null;
        }

        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return null;
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return $content;
        }

        return json_encode($this->sanitizeArray($decoded), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array<mixed> $payload
     * @return array<mixed>
     */
    private function sanitizeArray(array $payload): array
    {
        $sanitized = [];

        foreach ($payload as $key => $value) {
            if (is_string($key) && $this->isSensitiveKey($key)) {
                $sanitized[$key] = self::REDACTED_VALUE;

                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);

                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    private function isSensitiveKey(string $key): bool
    {
        return in_array(strtolower($key), self::SENSITIVE_KEYS, true);
    }
}
