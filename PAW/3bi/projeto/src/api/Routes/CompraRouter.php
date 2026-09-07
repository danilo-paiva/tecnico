<?php

declare(strict_types=1);

namespace Api\Routes;

use Api\Controllers\CompraController;
use Api\Middlewares\ValidateCompraBody;
use Api\Middlewares\ValidateCompraId;
use Slim\App;

class CompraRouter
{
    public static function routes(App $app): void
    {
        $app->get('/compras', [CompraController::class, 'findAll']);

        $app->get('/compras/count', [CompraController::class, 'count']);

        $app->get('/compras/participante/{id}', [CompraController::class, 'findByParticipante'])
            ->add(ValidateCompraId::class);

        $app->get('/compras/ingresso/{id}', [CompraController::class, 'findByIngresso'])
            ->add(ValidateCompraId::class);

        $app->get('/compras/{id}', [CompraController::class, 'findById'])
            ->add(ValidateCompraId::class);

        $app->post('/compras', [CompraController::class, 'create'])
            ->add(ValidateCompraBody::class);

        $app->put('/compras/{id}', [CompraController::class, 'update'])
            ->add(ValidateCompraBody::class)
            ->add(ValidateCompraId::class);

        $app->delete('/compras/{id}', [CompraController::class, 'delete'])
            ->add(ValidateCompraId::class);
    }
}
