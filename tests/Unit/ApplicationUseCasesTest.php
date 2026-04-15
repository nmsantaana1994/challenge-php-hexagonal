<?php

namespace Tests\Unit;

use App\Application\Contracts\AuditLogRepositoryInterface;
use App\Application\Contracts\FavoriteGifRepositoryInterface;
use App\Application\Contracts\TokenIssuerInterface;
use App\Application\Contracts\UserRepositoryInterface;
use App\Application\DTOs\Audit\ApiLogDto;
use App\Application\DTOs\Audit\LogApiInteractionInputDto;
use App\Application\DTOs\Auth\AuthenticatedUserDto;
use App\Application\DTOs\Auth\IssuedTokenDto;
use App\Application\DTOs\Auth\LoginUserInputDto;
use App\Application\DTOs\Favorite\FavoriteGifDto;
use App\Application\DTOs\Favorite\SaveFavoriteGifInputDto;
use App\Application\UseCases\Audit\LogApiInteractionUseCase;
use App\Application\UseCases\Auth\LoginUserUseCase;
use App\Application\UseCases\Favorite\SaveFavoriteGifUseCase;
use PHPUnit\Framework\TestCase;

class ApplicationUseCasesTest extends TestCase
{
    public function test_login_user_use_case_does_not_issue_token_when_password_is_invalid(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $tokenIssuer = $this->createMock(TokenIssuerInterface::class);

        $userRepository->expects($this->once())
            ->method('findByEmail')
            ->with('nico@example.com')
            ->willReturn(new AuthenticatedUserDto(
                id: 1,
                name: 'Nico',
                email: 'nico@example.com',
                passwordHash: password_hash('correct-password', PASSWORD_DEFAULT),
            ));

        $tokenIssuer->expects($this->never())
            ->method('issueForUser');

        $useCase = new LoginUserUseCase($userRepository, $tokenIssuer);

        $result = $useCase->execute(new LoginUserInputDto(
            email: 'nico@example.com',
            password: 'wrong-password',
        ));

        $this->assertNull($result);
    }

    public function test_save_favorite_gif_use_case_returns_null_when_user_does_not_exist(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $favoriteGifRepository = $this->createMock(FavoriteGifRepositoryInterface::class);

        $userRepository->expects($this->once())
            ->method('existsById')
            ->with(99)
            ->willReturn(false);

        $favoriteGifRepository->expects($this->never())
            ->method('save');

        $useCase = new SaveFavoriteGifUseCase($userRepository, $favoriteGifRepository);

        $result = $useCase->execute(new SaveFavoriteGifInputDto(
            userId: 99,
            gifId: 'gif_missing_user',
            alias: 'Invalid favorite',
        ));

        $this->assertNull($result);
    }

    public function test_log_api_interaction_use_case_maps_input_into_repository_dto(): void
    {
        $auditLogRepository = $this->createMock(AuditLogRepositoryInterface::class);

        $auditLogRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (ApiLogDto $apiLog): bool {
                return $apiLog->id === null
                    && $apiLog->userId === 5
                    && $apiLog->serviceName === 'GET /api/gifs/search'
                    && $apiLog->requestBody === ['query' => 'cats']
                    && $apiLog->responseCode === 200
                    && $apiLog->responseBody === '{"data":[]}'
                    && $apiLog->ipAddress === '127.0.0.1';
            }))
            ->willReturn(new ApiLogDto(
                id: 10,
                userId: 5,
                serviceName: 'GET /api/gifs/search',
                requestBody: ['query' => 'cats'],
                responseCode: 200,
                responseBody: '{"data":[]}',
                ipAddress: '127.0.0.1',
                createdAt: '2026-04-14T12:00:00+00:00',
                updatedAt: '2026-04-14T12:00:00+00:00',
            ));

        $useCase = new LogApiInteractionUseCase($auditLogRepository);

        $result = $useCase->execute(new LogApiInteractionInputDto(
            userId: 5,
            serviceName: 'GET /api/gifs/search',
            requestBody: ['query' => 'cats'],
            responseCode: 200,
            responseBody: '{"data":[]}',
            ipAddress: '127.0.0.1',
        ));

        $this->assertSame(10, $result->id);
        $this->assertSame('GET /api/gifs/search', $result->serviceName);
    }
}
