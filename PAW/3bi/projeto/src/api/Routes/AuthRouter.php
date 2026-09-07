<?php

declare(strict_types=1);

namespace Api\Routes;

use Api\Controllers\AuthController;
use Slim\App;

class AuthRouter
{
    public static function routes(App $app): void
    {
        $app->post('/auth/login', [AuthController::class, 'login']);
        $app->post('/auth/register', [AuthController::class, 'register']);
        $app->get('/auth/me', [AuthController::class, 'me']);
    }
}
