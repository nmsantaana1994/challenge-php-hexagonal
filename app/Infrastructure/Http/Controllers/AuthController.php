<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\UseCases\Auth\LoginUserUseCase;
use App\Infrastructure\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

class AuthController
{
    public function login(LoginRequest $request, LoginUserUseCase $useCase): JsonResponse
    {
        try {
            $token = $useCase->execute($request->toDto());
        } catch (Throwable) {
            return response()->json([
                'message' => 'Unable to process login.',
            ], 500);
        }

        if ($token === null) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        return response()->json([
            'data' => [
                'access_token' => $token->accessToken,
                'token_type' => $token->tokenType,
                'expires_in' => $token->expiresIn,
            ],
        ], 200);
    }
}
