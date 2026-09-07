<?php

declare(strict_types=1);

namespace Api\Routes;

use Api\Controllers\LocalController;
use Api\Middlewares\ValidateLocalBody;
use Api\Middlewares\ValidateLocalId;
use Slim\App;

class LocalRouter
{
    public static function routes(App $app): void
    {
        $app->get('/locais', [LocalController::class, 'findAll']);

        $app->get('/locais/count', [LocalController::class, 'count']);

        $app->get('/locais/nome/{nome}', [LocalController::class, 'findByNome']);

        $app->get('/locais/busca', [LocalController::class, 'findByNome']);

        $app->get('/locais/{id}', [LocalController::class, 'findById'])
            ->add(ValidateLocalId::class);

        $app->post('/locais', [LocalController::class, 'create'])
            ->add(ValidateLocalBody::class);

        $app->put('/locais/{id}', [LocalController::class, 'update'])
            ->add(ValidateLocalBody::class)
            ->add(ValidateLocalId::class);

        $app->delete('/locais/{id}', [LocalController::class, 'delete'])
            ->add(ValidateLocalId::class);
    }
}
