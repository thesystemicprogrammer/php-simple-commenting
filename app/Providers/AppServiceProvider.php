<?php

namespace App\Providers;

use App\Services\Avatar\AvatarService;
use Illuminate\Support\ServiceProvider;
use App\Services\Hashing\PageHashingService;
use App\Services\Hashing\EmailHashingService;
use App\Services\Avatar\InitialAvatarService;
use App\Services\Avatar\IdenticonAvatarService;
use App\Exceptions\InvalidAvatarServiceException;
use App\Services\Approval\ApprovalService;
use App\Services\Approval\ApprovalTokenRandomBytesService;
use App\Services\Approval\ApprovalTokenService;
use App\Services\Commenting\CommentingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PageHashingService::class, function ($app) {
            return new PageHashingService();
        });

        $this->app->singleton(EmailHashingService::class, function ($app) {
            return new EmailHashingService();
        });

        $this->app->singleton(CommentingService::class, function ($app) {
            return new CommentingService(app(PageHashingService::class), app(EmailHashingService::class));
        });

        $this->app->singleton(ApprovalTokenService::class, function ($app) {
            return new ApprovalTokenRandomBytesService();
        });

        $this->app->singleton(ApprovalService::class, function ($app) {
            return new ApprovalService(app(ApprovalTokenService::class));
        });

        $this->app->singleton(AvatarService::class, function ($app) {
            if (env('AVATAR_SERVICE') == 'INITIAL') {
                return new InitialAvatarService();
            }

            if (env('AVATAR_SERVICE') == 'IDENTICON') {
                return new IdenticonAvatarService();
            }

            throw new InvalidAvatarServiceException('The provided Avatar Service in .env is invalid.');
        });
    }
}
