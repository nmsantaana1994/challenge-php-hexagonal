<?php

namespace App\Infrastructure\Providers;

use App\Application\Contracts\AuditLogRepositoryInterface;
use App\Application\Contracts\FavoriteGifRepositoryInterface;
use App\Application\Contracts\GiphyClientInterface;
use App\Application\Contracts\TokenIssuerInterface;
use App\Application\Contracts\UserRepositoryInterface;
use App\Infrastructure\Auth\PassportTokenIssuer;
use App\Infrastructure\External\Giphy\HttpGiphyClient;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentAuditLogRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentFavoriteGifRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class InfrastructureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(FavoriteGifRepositoryInterface::class, EloquentFavoriteGifRepository::class);
        $this->app->bind(AuditLogRepositoryInterface::class, EloquentAuditLogRepository::class);
        $this->app->bind(GiphyClientInterface::class, HttpGiphyClient::class);
        $this->app->bind(TokenIssuerInterface::class, PassportTokenIssuer::class);
    }

    public function boot(): void
    {
        Passport::tokensExpireIn(now()->addMinutes(30));
        Passport::personalAccessTokensExpireIn(now()->addMinutes(30));
    }
}
