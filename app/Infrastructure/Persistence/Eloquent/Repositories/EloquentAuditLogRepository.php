<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Contracts\AuditLogRepositoryInterface;
use App\Application\DTOs\Audit\ApiLogDto;
use App\Infrastructure\Persistence\Eloquent\Models\ApiLog;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function save(ApiLogDto $apiLog): ApiLogDto
    {
        $model = $apiLog->id !== null
            ? ApiLog::query()->findOrFail($apiLog->id)
            : new ApiLog();

        $model->fill([
            'user_id' => $apiLog->userId,
            'service_name' => $apiLog->serviceName,
            'request_body' => $apiLog->requestBody,
            'response_code' => $apiLog->responseCode,
            'response_body' => $apiLog->responseBody,
            'ip_address' => $apiLog->ipAddress,
        ]);

        $model->save();

        return new ApiLogDto(
            id: (int) $model->getKey(),
            userId: $model->user_id !== null ? (int) $model->user_id : null,
            serviceName: (string) $model->service_name,
            requestBody: $model->request_body,
            responseCode: (int) $model->response_code,
            responseBody: $model->response_body,
            ipAddress: (string) $model->ip_address,
            createdAt: $model->created_at?->toIso8601String(),
            updatedAt: $model->updated_at?->toIso8601String(),
        );
    }
}
